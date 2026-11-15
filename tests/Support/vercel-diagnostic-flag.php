<?php
// Isolated flag audit: no Laravel kernel, DB connection, env-file writes, or credentials output.
$root=dirname(__DIR__,2);
require $root.'/vendor/autoload.php';
$value=$argv[1];$log=$argv[2];
if($value==='__missing__'||$value==='__runtime_prepare__') {putenv('VERCEL');unset($_ENV['VERCEL'],$_SERVER['VERCEL']);}
else {putenv('VERCEL='.$value);$_ENV['VERCEL']=$value;$_SERVER['VERCEL']=$value;}
if($value==='__runtime_prepare__') App\Support\VercelRuntime::prepare();
$config=require $root.'/config/app.php';
$app=new Illuminate\Foundation\Application($root);
$app->instance('config',new Illuminate\Config\Repository(['app'=>$config]));
$app->instance('request',Illuminate\Http\Request::create('/login'));
$logger=new class extends Psr\Log\AbstractLogger {
    public int $calls=0;
    public function log($level,$message,array $context=[]):void {$this->calls++;}
};
$app->instance(Psr\Log\LoggerInterface::class,$logger);
ini_set('error_log',$log);
(new App\Exceptions\Handler($app))->report(new RuntimeException('Undefined variable $hero'));
$written=is_file($log) ? file_get_contents($log) : '';
echo json_encode(['config_active'=>(bool)$config['vercel'],'diagnostic_active'=>str_contains($written,'[laravel.exception] '),'normal_logger_calls'=>$logger->calls]),PHP_EOL;