<?php
namespace App\Support;

class VercelRuntime
{
    public static function prepare(): string
    {
        $storage = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).'/miru-vercel';
        foreach (['app/private','app/public','framework/views','framework/sessions','framework/cache/data','fonts','logs','bootstrap/cache'] as $directory) {
            $path=$storage.'/'.$directory;
            if (!is_dir($path) && !mkdir($path,0775,true) && !is_dir($path)) {
                throw new \RuntimeException('Direktori sementara serverless tidak dapat dibuat.');
            }
        }
        $defaults = [
            'APP_ENV'=>'production','APP_DEBUG'=>'false',
            'SESSION_DRIVER'=>'database','CACHE_DRIVER'=>'database','LOG_CHANNEL'=>'stderr',
            'QUEUE_CONNECTION'=>'sync','FILESYSTEM_DISK'=>'s3',
            'PUBLIC_FILESYSTEM_DRIVER'=>'s3','SPMB_FILESYSTEM_DRIVER'=>'s3',
            'SESSION_SECURE_COOKIE'=>'true',
        ];
        foreach($defaults as $key=>$value) if(getenv($key)===false && !isset($_ENV[$key]) && !isset($_SERVER[$key])) self::environment($key,$value);
        // Debug output is never enabled on this public entrypoint.
        self::environment('APP_DEBUG','false');
        self::environment('VERCEL','1');
        foreach(['CONFIG'=>'config.php','EVENTS'=>'events.php','PACKAGES'=>'packages.php','ROUTES'=>'routes.php','SERVICES'=>'services.php'] as $type=>$file) {
            self::environment('APP_'.$type.'_CACHE',$storage.'/bootstrap/cache/'.$file);
        }
        self::environment('VIEW_COMPILED_PATH',$storage.'/framework/views');
        return $storage;
    }

    public static function configureApplication($app, string $storage): void
    {
        // Laravel 9 recognizes slash-prefixed cache paths; support Windows CLI smoke tests too.
        if (preg_match('/^[A-Za-z]:/', $storage)) $app->addAbsoluteCachePathPrefix(substr($storage,0,3));
        $app->useStoragePath($storage);
    }

    private static function environment(string $key,string $value): void {
        $_ENV[$key]=$value; $_SERVER[$key]=$value; putenv($key.'='.$value);
    }
}
