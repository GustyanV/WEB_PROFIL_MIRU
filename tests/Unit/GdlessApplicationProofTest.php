<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
class GdlessApplicationProofTest extends TestCase
{
 public function test_real_preview_applicant_and_admin_pdf_without_gd():void {
  $root=dirname(__DIR__,2);$dir=$root.'/storage/app/testing/gdless-proof';if(!is_dir($dir))mkdir($dir,0777,true);
  $original=file_get_contents($root.'/public/images/logo-miru.png');file_put_contents($dir.'/original.png',$original);
  $key='school/pdf-logos/'.hash('sha256',$original).'.jpg';if(!is_dir(dirname($dir.'/'.$key)))mkdir(dirname($dir.'/'.$key),0777,true);
  copy($root.'/public/images/logo-miru-pdf.jpg',$dir.'/'.$key);
  $command=[PHP_BINARY,'-n','-d','display_errors=stderr','-d','sys_temp_dir='.$dir];
  foreach(['pdo','mbstring','fileinfo','dom','xml','xmlwriter','simplexml','tokenizer','ctype','iconv','openssl','pdo_sqlite'] as $extension){$path=ini_get('extension_dir').DIRECTORY_SEPARATOR.(PHP_OS_FAMILY==='Windows'?'php_'.$extension.'.dll':$extension.'.so');if(is_file($path)){array_push($command,'-d','extension='.$path);}}
  array_push($command,$root.'/tests/Support/gdless-application-proof.php',$dir);
  $pipes=[];$process=proc_open($command,[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root);
  $out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($process),$err.$out);$data=json_decode($out,true,512,JSON_THROW_ON_ERROR);
  $this->assertFalse($data['gd']);$this->assertEqualsWithDelta(82,$data['logo_width'],0.01);$this->assertEqualsWithDelta(4252/2835,$data['logo_width']/$data['logo_height'],0.001);foreach(['jpeg','qr_svg','applicant_pdf','admin_pdf'] as $field)$this->assertTrue($data[$field],$field);
  $this->assertStringEndsWith('/verifikasi-spmb/'.$data['token'],$data['url']);
 }
}