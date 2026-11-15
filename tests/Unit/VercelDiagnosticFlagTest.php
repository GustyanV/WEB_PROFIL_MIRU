<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;

class VercelDiagnosticFlagTest extends TestCase
{
    /** @dataProvider flags */
    public function test_real_environment_config_and_handler_activation(string $flag,bool $active):void
    {
        $root=dirname(__DIR__,2);$dir=$root.'/storage/app/testing/diagnostic-flag';
        if(!is_dir($dir))mkdir($dir,0777,true);
        $log=$dir.'/'.bin2hex(random_bytes(8)).'.log';$pipes=[];
        $command=[PHP_BINARY,'-d','sys_temp_dir='.$dir,$root.'/tests/Support/vercel-diagnostic-flag.php',$flag,$log];
        $process=proc_open($command,[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root);
        $output=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
        $this->assertSame(0,proc_close($process),$error);
        $data=json_decode($output,true,512,JSON_THROW_ON_ERROR);
        $this->assertSame($active,$data['config_active']);
        $this->assertSame($active,$data['diagnostic_active']);
        $this->assertSame($active?0:1,$data['normal_logger_calls']);
    }
    public static function flags():array
    {
        return [['true',true],['1',true],['false',false],['0',false],['',false],['__missing__',false],['__runtime_prepare__',true]];
    }
}