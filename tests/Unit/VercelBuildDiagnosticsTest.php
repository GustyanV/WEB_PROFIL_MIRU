<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class VercelBuildDiagnosticsTest extends TestCase
{
    public function test_diagnostics_report_platform_without_printing_secrets(): void
    {
        $root=dirname(__DIR__,2);
        $pipes=[];
        $process=proc_open([PHP_BINARY,$root.'/scripts/vercel-check.php','--diagnostics'],[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root,array_merge(getenv(),['APP_KEY'=>'DO_NOT_PRINT_THIS_SECRET']));
        $output=stream_get_contents($pipes[1]);
        $error=stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $status=proc_close($process);
        $this->assertSame(0,$status,$error);
        $this->assertStringContainsString('PHP_VERSION='.PHP_VERSION,$output);
        foreach(['pdo_pgsql','gd','mbstring','fileinfo','dom','openssl'] as $ext) {
            $this->assertStringContainsString('ext-'.$ext.'='.(extension_loaded($ext)?'loaded':'MISSING'),$output);
        }
        $this->assertStringNotContainsString('DO_NOT_PRINT_THIS_SECRET',$output.$error);
    }

    public function test_guard_still_fails_when_required_extensions_are_missing(): void
    {
        $pipes=[];
        $process=proc_open([PHP_BINARY,'-n',dirname(__DIR__,2).'/scripts/vercel-check.php'],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
        $output=stream_get_contents($pipes[1]);
        $error=stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $this->assertSame(1,proc_close($process));
        $this->assertStringContainsString('Required Vercel PHP extension unavailable: pdo_pgsql',$error);
        $this->assertStringNotContainsString('verified',$output);
    }
    public function test_composer_preserves_real_platform_check_after_diagnostics(): void
    {
        $composer=json_decode(file_get_contents(dirname(__DIR__,2).'/composer.json'),true);
        $this->assertSame([
            '@php scripts/vercel-check.php --diagnostics',
            '@composer check-platform-reqs --no-dev',
            '@php scripts/vercel-check.php',
        ],$composer['scripts']['vercel']);
    }
}