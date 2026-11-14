<?php
namespace Tests\Integration;

use GuzzleHttp\Client;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\TestCase;

/** Explicit opt-in: no database queries or migrations; only unique test objects. */
class SupabaseStorageTest extends TestCase
{
    public function test_media_upload_exists_public_read_and_delete(): void
    {
        $this->checkDisk('public','media',true);
    }

    public function test_documents_upload_exists_public_denial_and_delete(): void
    {
        $this->checkDisk('spmb','documents',false);
    }

    private function checkDisk(string $diskName, string $bucket, bool $public): void
    {
        if (getenv('RUN_SUPABASE_STORAGE_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_SUPABASE_STORAGE_TESTS=1 for live Supabase storage checks.');
        }
        $app=require dirname(__DIR__,2).'/bootstrap/app.php';
        $app->loadEnvironmentFrom('.env.supabase');
        $app->make(Kernel::class)->bootstrap();
        $config=config("filesystems.disks.$diskName");
        $this->assertSame('s3',$config['driver']);
        $this->assertSame($bucket,$config['bucket']);
        $endpoint=$config['endpoint'];
        $this->assertSame('/storage/v1/s3',rtrim(parse_url($endpoint,PHP_URL_PATH),'/'));
        $this->assertSame('https',parse_url($endpoint,PHP_URL_SCHEME));
        $key='_integration-checks/'.bin2hex(random_bytes(16)).'.txt';
        $body='MIRU storage check '.bin2hex(random_bytes(32));
        $disk=Storage::disk($diskName);
        $attempted=false;
        try {
            $attempted=true;
            $this->assertTrue($disk->put($key,$body,['ContentType'=>'text/plain']));
            $this->assertTrue($disk->exists($key));
            $this->assertSame($body,$disk->get($key));
            // Anonymous REST request: never send an API key or signed URL.
            $origin='https://'.parse_url($endpoint,PHP_URL_HOST);
            $url=$origin.'/storage/v1/object/public/'.$bucket.'/'.$key;
            $response=(new Client(['timeout'=>25,'connect_timeout'=>10,'http_errors'=>false,'allow_redirects'=>false]))->get($url);
            $content=(string)$response->getBody();
            if ($public) {
                $this->assertSame(200,$response->getStatusCode());
                $this->assertSame($body,$content);
                $served=(new Client(['timeout'=>25,'http_errors'=>false,'allow_redirects'=>false]))->get($disk->url($key));
                $this->assertSame(200,$served->getStatusCode());
                $this->assertSame($body,(string)$served->getBody());
            } else {
                $this->assertContains($response->getStatusCode(),[400,401,403,404]);
                $this->assertStringNotContainsString($body,$content);
            }
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->fail('Live '.$bucket.' operation failed ('.get_class($e).'); details intentionally redacted.');
        } finally {
            if ($attempted) {
                try {
                    $this->assertTrue($disk->delete($key));
                    $this->assertFalse($disk->exists($key));
                } catch (\Throwable $e) {
                    $this->fail('Cleanup failed for generated test object '.$key.'; details redacted.');
                }
            }
        }
    }
}