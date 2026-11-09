<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$storage = \App\Support\VercelRuntime::prepare();
$app = require __DIR__.'/../bootstrap/app.php';
\App\Support\VercelRuntime::configureApplication($app,$storage);
// Never reference a developer Vite hot-server URL from the serverless entrypoint.
$app->booted(function () use ($app, $storage) {
    $app->make(\Illuminate\Foundation\Vite::class)->useHotFile($storage.'/unused-vite-hot');
});
$kernel = $app->make(Kernel::class);
$response = $kernel->handle($request = Request::capture());
$response->send();
$kernel->terminate($request,$response);
