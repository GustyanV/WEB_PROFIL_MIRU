<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PdfLogoCompatibilityTest extends TestCase
{
    /** @dataProvider formats */
    public function test_original_logos_in_actual_proof_template_without_gd(string $format, bool $expected): void
    {
        if (!extension_loaded('gd')) $this->markTestSkipped('GD is used only to generate raster test fixtures in the parent process.');
        $root=dirname(__DIR__,2);
        $dir=$root.'/storage/app/testing/pdf-logo-audit';
        if (!is_dir($dir)) mkdir($dir,0777,true);
        $file=$dir.'/logo.'.$format;
        if ($format==='svg') {
            file_put_contents($file,'<svg xmlns="http://www.w3.org/2000/svg" width="200" height="100"><rect width="200" height="100" fill="#0d9488"/><circle cx="100" cy="50" r="30" fill="#e87500"/></svg>');
        } else {
            $im=imagecreatetruecolor(200,100);
            imagealphablending($im,false);imagesavealpha($im,true);
            imagefill($im,0,0,imagecolorallocatealpha($im,13,148,136,$format==='png-alpha'?70:0));
            if (str_starts_with($format,'png')) imagepng($im,$file);
            elseif ($format==='jpeg') imagejpeg($im,$file,95);
            elseif ($format==='webp') {
                if (!function_exists('imagewebp')) $this->markTestSkipped('Fixture creation requires GD WebP support.');
                imagewebp($im,$file);
            }
            imagedestroy($im);
        }
        $mime=str_starts_with($format,'png')?'image/png':('image/'.($format==='svg'?'svg+xml':$format));
        $pipes=[];
        $command=[PHP_BINARY,'-n','-d','display_errors=stderr'];
        foreach (['mbstring','dom','xml','xmlwriter','simplexml','tokenizer','ctype','iconv'] as $extension) {
            $module=ini_get('extension_dir').DIRECTORY_SEPARATOR.(PHP_OS_FAMILY==='Windows'?'php_'.$extension.'.dll':$extension.'.so');
            if (is_file($module)) { $command[]='-d'; $command[]='extension='.$module; }
        }
        array_push($command,$root.'/tests/Support/pdf-logo-probe.php',$file,$mime);
        $process=proc_open($command,[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root);
        $output=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);
        fclose($pipes[1]);fclose($pipes[2]);
        $this->assertSame(0,proc_close($process),$error.$output);
        $data=json_decode($output,true,512,JSON_THROW_ON_ERROR);
        $this->assertFalse($data['gd']);
        $this->assertTrue($data['logo_in_html']);$this->assertTrue($data['qr_in_html']);
        $this->assertSame($expected,$data['rendered'],json_encode($data));
        if ($expected) {
            $this->assertSame(1,$data['pages']);
            if ($format==='jpeg') $this->assertTrue($data['jpeg_embedded']);
        } else {
            $this->assertStringContainsString($format==='webp'?'imagecreatefromwebp':'GD extension',$data['error']);
        }
        file_put_contents($file.'.json',json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

    public static function formats(): array
    {
        return [['png',false],['png-alpha',false],['jpeg',true],['svg',true],['webp',false]];
    }
}