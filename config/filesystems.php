<?php

// Disk names stay stable: existing CMS code uses "public", SPMB uses "spmb".
// Production switches their drivers without changing stored object keys.
$publicCloud = env('PUBLIC_FILESYSTEM_DRIVER', 'local') === 's3';
$privateCloud = env('SPMB_FILESYSTEM_DRIVER', 'local') === 's3';
$s3 = [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
    'throw' => true,
];

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => ['driver'=>'local','root'=>storage_path('app'),'throw'=>false],
        'public' => $publicCloud
            ? array_merge($s3, ['bucket'=>env('AWS_BUCKET'),'url'=>env('AWS_URL')])
            : ['driver'=>'local','root'=>storage_path('app/public'),'url'=>env('APP_URL').'/storage','visibility'=>'public','throw'=>false],
        'spmb' => $privateCloud
            ? array_merge($s3, ['bucket'=>env('AWS_PRIVATE_BUCKET')])
            : ['driver'=>'local','root'=>storage_path('app/private'),'visibility'=>'private','throw'=>true],
        's3' => array_merge($s3, ['bucket'=>env('AWS_BUCKET'),'url'=>env('AWS_URL')]),
    ],
    'links' => [public_path('storage')=>storage_path('app/public')],
];
