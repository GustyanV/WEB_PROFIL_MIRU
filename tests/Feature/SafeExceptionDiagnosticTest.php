<?php
namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SafeExceptionDiagnosticTest extends TestCase
{
    private string $log;
    private $previousLog;
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.vercel'=>true,'app.debug'=>false]);
        $dir=base_path('storage/app/testing/exception-diagnostics');
        if(!is_dir($dir))mkdir($dir,0777,true);
        $this->log=$dir.'/'.bin2hex(random_bytes(8)).'.log';
        $this->previousLog=ini_set('error_log',$this->log);
    }
    protected function tearDown(): void
    {
        ini_set('error_log',(string)$this->previousLog);
        parent::tearDown();
    }
    private function record(): array
    {
        $lines=file($this->log,FILE_IGNORE_NEW_LINES);
        $diagnostics=array_values(array_filter($lines,fn($line)=>str_contains($line,'[laravel.exception] ')));
        $this->assertCount(1,$diagnostics,implode("\n",$lines));
        return json_decode(explode('[laravel.exception] ',$diagnostics[0],2)[1],true,512,JSON_THROW_ON_ERROR);
    }
    public function test_http_500_is_logged_once_without_body_query_token_or_personal_data(): void
    {
        Route::get('/diagnostic-test/{token}',function(){throw new \RuntimeException('Undefined variable $hero');});
        $response=$this->getJson('/diagnostic-test/PRIVATE_TOKEN_SENTINEL?email=person@example.test', ['Authorization'=>'Bearer AUTH_SENTINEL']);
        $response->assertStatus(500);
        $record=$this->record();
        $this->assertSame(\RuntimeException::class,$record['exception_class']);
        $this->assertStringContainsString('Undefined variable',$record['message']);
        $this->assertSame('/diagnostic-test/{token}',$record['request_path']);
        $this->assertSame('tests/Feature/SafeExceptionDiagnosticTest.php',$record['file']);
        $this->assertIsInt($record['line']);
        $all=file_get_contents($this->log).$response->getContent();
        foreach(['PRIVATE_TOKEN_SENTINEL','person@example.test','AUTH_SENTINEL'] as $value)$this->assertStringNotContainsString($value,$all);
    }
    public function test_database_wrapped_exception_preserves_primary_error_without_sql_or_secrets(): void
    {
        $secret='DB_PASSWORD_SENTINEL';config(['database.connections.pgsql.password'=>$secret]);
        Route::get('/diagnostic-db',function()use($secret){
            $previous=new \PDOException('SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "hero_banners" does not exist '.$secret);
            throw new QueryException('select * from student_applications where email = ?',['person@example.test'],$previous);
        });
        $this->getJson('/diagnostic-db')->assertStatus(500);
        $record=$this->record();
        $this->assertSame(\PDOException::class,$record['exception_class']);
        $this->assertStringContainsString('42P01',$record['message']);
        $all=file_get_contents($this->log);
        foreach([$secret,'person@example.test','select *','student_applications','hero_banners'] as $value)$this->assertStringNotContainsString($value,$all);
    }
    public function test_unstructured_message_never_prints_secrets_or_pii(): void
    {
        Route::get('/diagnostic-unsafe',function(){throw new \RuntimeException("APP_KEY=base64:APP_KEY_SENTINEL DB_PASSWORD=PASSWORD_SENTINEL AWS_SECRET_ACCESS_KEY=AWS_SENTINEL token=TOKEN_SENTINEL Jane Sensitive 081234567890\nforged-log-line");});
        $this->getJson('/diagnostic-unsafe')->assertStatus(500);
        $record=$this->record();$this->assertTrue($record['message_redacted']);
        $all=file_get_contents($this->log);
        foreach(['APP_KEY_SENTINEL','PASSWORD_SENTINEL','AWS_SENTINEL','TOKEN_SENTINEL','Jane Sensitive','081234567890','forged-log-line'] as $value)$this->assertStringNotContainsString($value,$all);
    }
    public function test_validation_exceptions_remain_unreported(): void
    {
        $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->report(new \Illuminate\Validation\ValidationException(validator([],[])));
        $this->assertFileDoesNotExist($this->log);
    }
    public function test_success_responses_are_not_changed_or_logged(): void
    {
        Route::get('/diagnostic-ok',fn()=>response('<html>OK</html>'));
        $this->get('/diagnostic-ok')->assertOk()->assertSee('OK');
        $this->assertFileDoesNotExist($this->log);
    }
    public function test_unmatched_private_path_and_environment_values_are_not_logged(): void
    {
        $request=\Illuminate\Http\Request::create('/users/PRIVATE_PERSON_SENTINEL?token=QUERY_SENTINEL');
        \App\Support\SafeExceptionDiagnostic::write(new \RuntimeException('PRIVATE_PERSON_SENTINEL'),$request);
        $record=$this->record();$this->assertSame('[unmatched route]',$record['request_path']);
        $this->assertStringNotContainsString('PRIVATE_PERSON_SENTINEL',file_get_contents($this->log));
        $this->assertStringNotContainsString('QUERY_SENTINEL',file_get_contents($this->log));
    }
    public function test_explicit_http_500_is_reported_without_changing_its_status(): void
    {
        Route::get('/diagnostic-abort',function(){abort(500);});
        $this->getJson('/diagnostic-abort')->assertStatus(500);
        $this->assertSame('Symfony\\Component\\HttpKernel\\Exception\\HttpException',$this->record()['exception_class']);
    }

    public function test_config_secret_cannot_reappear_in_a_technical_message(): void
    {
        config(['database.connections.pgsql.password'=>'driver']);
        \App\Support\SafeExceptionDiagnostic::write(new \RuntimeException('could not find driver'),\Illuminate\Http\Request::create('/'));
        $record=$this->record();$this->assertTrue($record['message_redacted']);
        $this->assertStringNotContainsString('driver',file_get_contents($this->log));
    }
    public function test_vercel_does_not_forward_raw_exception_to_default_logger(): void
    {
        $logger=\Mockery::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldNotReceive('error');$logger->shouldNotReceive('log');
        $this->app->instance(\Psr\Log\LoggerInterface::class,$logger);
        $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->report(new \RuntimeException('private unstructured message'));
        $this->assertSame('Unstructured exception message withheld for privacy',$this->record()['message']);
    }
    public function test_non_vercel_reporting_keeps_laravel_default_behavior(): void
    {
        config(['app.vercel'=>false]);
        $logger=\Mockery::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('error')->once()->with('nonsecret-testmessage',\Mockery::type('array'));
        $this->app->instance(\Psr\Log\LoggerInterface::class,$logger);
        $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->report(new \RuntimeException('nonsecret-testmessage'));
        $this->assertFileDoesNotExist($this->log);
    }

}