<?php
namespace Tests\Feature;

use Aws\Result;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class S3EndpointTest extends TestCase
{
    public function test_supabase_operations_preserve_endpoint_path_exactly_once(): void
    {
        foreach (['public'=>'media', 'spmb'=>'documents'] as $disk=>$bucket) {
            $paths=[];
            $config=array_merge(config("filesystems.disks.$disk"), [
                'driver'=>'s3', 'endpoint'=>'https://project.supabase.co/storage/v1/s3',
                'bucket'=>$bucket, 'key'=>'test-key', 'secret'=>'test-secret',
                'region'=>'ap-southeast-1', 'use_path_style_endpoint'=>true,
                'suppress_php_deprecation_warning'=>true,
                'handler'=>function ($command, $request) use (&$paths) {
                    $paths[$command->getName()]=$request->getUri()->getPath();
                    return Create::promiseFor(new Result([]));
                },
            ]);
            $storage=Storage::build($config);
            $storage->put('checks/test.txt','test');
            $this->assertTrue($storage->exists('checks/test.txt'));
            $storage->delete('checks/test.txt');
            foreach (['PutObject','HeadObject','DeleteObject'] as $operation) {
                $this->assertSame("/storage/v1/s3/$bucket/checks/test.txt",$paths[$operation],$operation);
            }
            $signed=$storage->temporaryUrl('checks/test.txt',now()->addMinutes(5));
            $this->assertSame("/storage/v1/s3/$bucket/checks/test.txt",parse_url($signed,PHP_URL_PATH));
        }
    }
}
