<?php
namespace Tests\Feature;

use App\Models\{HeroBanner, SchoolProfile};
use App\Support\SchoolContent;
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Tests\TestCase;

class DeploymentCompatibilityTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        $this->artisan('migrate',['--force'=>true])->assertExitCode(0);
    }
    public function test_cms_assets_use_disk_urls_instead_of_local_storage_paths(): void {
        config(['filesystems.disks.public.url'=>'https://media.example.invalid/storage/v1/object/public/site-media']);
        Storage::fake('public', ['url'=>config('filesystems.disks.public.url')]);
        Storage::disk('public')->put('hero/banner.png','image');
        Storage::disk('public')->put('site/logo.png','image');
        $this->assertSame('https://media.example.invalid/storage/v1/object/public/site-media/hero/banner.png', (new HeroBanner(['banner_desktop'=>'hero/banner.png']))->desktop_banner_url);
        $this->assertSame('https://media.example.invalid/storage/v1/object/public/site-media/site/logo.png', (new SchoolProfile(['logo'=>'site/logo.png']))->site_logo_url);
        $this->assertSame('https://media.example.invalid/storage/v1/object/public/site-media/site/logo.png', SchoolContent::image('site/logo.png'));
        $this->assertStringContainsString('/images/fallback-school.svg',SchoolContent::image('missing.png'));
    }
    public function test_database_session_cache_and_lock_tables_are_available(): void {
        foreach(['sessions','cache','cache_locks'] as $table) $this->assertTrue(Schema::hasTable($table),$table);
        config(['cache.default'=>'database','session.driver'=>'database']);
        app('cache')->store('database')->put('deployment-test','persistent',60);
        $this->assertSame('persistent',app('cache')->store('database')->get('deployment-test'));
        $lock=app('cache')->store('database')->lock('deployment-lock',10);
        $this->assertTrue($lock->get());
        $lock->release();
        $this->get('/login')->assertOk();
        $this->assertGreaterThan(0, DB::table('sessions')->count());
    }
    public function test_all_migrations_compile_with_postgres_grammar_without_connecting(): void {
        config(['database.default'=>'pgsql']);
        DB::purge('pgsql');
        $queries=DB::connection('pgsql')->pretend(function() {
            foreach(glob(database_path('migrations/*.php')) as $file) (require $file)->up();
        });
        $sql=implode("\n",array_column($queries,'query'));
        $this->assertStringContainsString('create table "student_applications"',$sql);
        $this->assertStringContainsString('"public_token" uuid',$sql);
        $this->assertStringNotContainsString('ENGINE=',$sql);
        $this->assertStringNotContainsString(chr(96),$sql);
    }
    public function test_invalid_qr_token_is_rejected_without_uuid_database_query(): void {
        DB::flushQueryLog(); DB::enableQueryLog();
        $this->get('/verifikasi-spmb/not-a-uuid')->assertNotFound();
        $queries=collect(DB::getQueryLog())->pluck('query')->implode("\n");
        $this->assertStringNotContainsString('from "student_applications"',$queries);
        DB::disableQueryLog();
    }

    public function test_pdf_reads_uploaded_logo_through_storage_and_preserves_qr_route(): void {
        Storage::fake('public');
        $file=\Illuminate\Http\UploadedFile::fake()->image('logo.png',23,31);
        Storage::disk('public')->put('site/logo.png',file_get_contents($file->getRealPath()));
        SchoolProfile::create(['name'=>'Madrasah Pengujian','logo'=>'site/logo.png']);
        $user=\App\Models\User::factory()->create(['role'=>'pendaftar']);
        $application=\App\Models\StudentApplication::create(['user_id'=>$user->id,'status'=>'submitted','registration_number'=>'SPMB-CLOUD-0001']);
        $response=$this->actingAs($user)->get('/dashboard/cetak-bukti/'.$application->id)->assertOk();
        $data=$response->original->getData();
        $size=getimagesizefromstring(base64_decode(substr($data['logo'],strlen('data:image/png;base64,'))));
        $this->assertSame(23,$size[0]); $this->assertSame(31,$size[1]);
        $this->assertSame(route('spmb.verify',['token'=>$application->fresh()->public_token]),$data['url']);
        $this->get('/dashboard/cetak-bukti/'.$application->id.'/pdf')->assertOk()->assertHeader('Content-Type','application/pdf');
    }
    public function test_postgres_search_is_case_insensitive_without_changing_mysql_operator(): void {
        $this->assertSame('like',\App\Support\DatabaseSearch::operator());
        config(['database.default'=>'pgsql']); DB::purge('pgsql');
        $this->assertSame('ilike',\App\Support\DatabaseSearch::operator());
        $this->assertStringContainsString('ilike',DB::table('users')->where('name',\App\Support\DatabaseSearch::operator(),'%nama%')->toSql());
    }

    public function test_vercel_proxy_headers_are_trusted_only_for_runtime_loopback(): void {
        config(['app.vercel'=>true]);
        $request=\Illuminate\Http\Request::create('http://internal/login','GET',[],[],[],['REMOTE_ADDR'=>'127.0.0.1','HTTP_X_FORWARDED_PROTO'=>'https','HTTP_X_FORWARDED_FOR'=>'203.0.113.42']);
        (new \App\Http\Middleware\TrustProxies)->handle($request,fn()=>response('ok'));
        $this->assertTrue($request->secure()); $this->assertSame('203.0.113.42',$request->ip());
        config(['app.vercel'=>false]);
        $request=\Illuminate\Http\Request::create('http://local/login','GET',[],[],[],['REMOTE_ADDR'=>'127.0.0.1','HTTP_X_FORWARDED_PROTO'=>'https']);
        (new \App\Http\Middleware\TrustProxies)->handle($request,fn()=>response('ok'));
        $this->assertFalse($request->secure());
        \Illuminate\Http\Request::setTrustedProxies([],0);
    }

    public function test_vercel_config_protects_private_paths_and_keeps_static_assets(): void {
        $config=json_decode(file_get_contents(base_path('vercel.json')),true,512,JSON_THROW_ON_ERROR);
        $this->assertSame('public',$config['outputDirectory']);
        $this->assertSame('npm run build',$config['buildCommand']);
        $this->assertSame('vercel-php@0.6.2',$config['functions']['api/index.php']['runtime']);
        $this->assertFileExists(base_path('api/index.php'));
        $this->assertSame(404,$config['routes'][0]['status']);
        $this->assertMatchesRegularExpression('~^'.$config['routes'][0]['src'].'$~','/storage/spmb/private.pdf');
        $this->assertMatchesRegularExpression('~^'.$config['routes'][1]['src'].'$~','/.env');
        $this->assertSame('filesystem',$config['routes'][3]['handle']);
        $this->assertSame('/api/index.php',$config['routes'][4]['dest']);
        $this->assertFileExists(public_path('images/logo-miru.png'));
    }
}
