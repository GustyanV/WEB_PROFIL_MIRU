<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemManager;

final class S3PathEndpoint
{
    public static function disk($app, array $config)
    {
        $disk=(new FilesystemManager($app))->createS3Driver($config);
        $base=rtrim((string) parse_url($config['endpoint'] ?? '', PHP_URL_PATH), '/');
        if ($base !== '') {
            // AWS SDK 3.337.3 RestSerializer repeats custom endpoint paths.
            // Correct only the duplicated base prefix, before request signing.
            // Leave query parameters, encoded keys, payload and headers intact.
            $disk->getClient()->getHandlerList()->appendBuild(function ($next) use ($base) {
                return function ($command, $request) use ($next, $base) {
                    $uri=$request->getUri();
                    $duplicate=$base.$base.'/';
                    if (str_starts_with($uri->getPath(), $duplicate)) {
                        $request=$request->withUri($uri->withPath(substr($uri->getPath(), strlen($base))));
                    }
                    return $next($command, $request);
                };
            }, 'app.s3_base_path');
        }
        return $disk;
    }
}