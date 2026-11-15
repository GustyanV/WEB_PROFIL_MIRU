<?php
// Standalone process probe. Never boots Laravel, opens a DB or accesses S3.
require dirname(__DIR__,2).'/vendor/autoload.php';
$root=dirname(__DIR__,2);
$file=$argv[1]; $mime=$argv[2]; $temp=dirname($file);
$application=(object)[
 'registration_number'=>'SPMB-AUDIT-0001','status'=>'submitted',
 'studentProfile'=>(object)['full_name'=>'Siswa Audit','nik'=>'123','nisn'=>'456','birth_place'=>'Jabung','birth_date'=>'2020-01-01','gender'=>'L','previous_school'=>'RA Audit'],
 'parentProfile'=>(object)['father_name'=>'Wali Audit','mother_name'=>'Ibu Audit'],
 'user'=>(object)['phone'=>'08123456789'],
 'period'=>(object)['academic_year'=>'2026/2027'],'wave'=>(object)['name'=>'Gelombang 1'],
];
$school=(object)['name'=>'Madrasah Audit','nsm'=>'123','npsn'=>'456','address'=>'Jabung Wetan'];
$url='https://madrasah.example/verifikasi-spmb/123e4567-e89b-12d3-a456-426614174000';
$qr=base64_encode((new BaconQrCode\Writer(new BaconQrCode\Renderer\ImageRenderer(new BaconQrCode\Renderer\RendererStyle\RendererStyle(150,0),new BaconQrCode\Renderer\Image\SvgImageBackEnd())))->writeString($url));
$logo='data:'.$mime.';base64,'.base64_encode(file_get_contents($file));
$compiler=new Illuminate\View\Compilers\BladeCompiler(new Illuminate\Filesystem\Filesystem(),$temp);
$compiled=$compiler->compileString(file_get_contents($root.'/resources/views/pdf/proof.blade.php'));
ob_start(); eval('?>'.$compiled); $html=ob_get_clean();
$result=['gd'=>extension_loaded('gd'),'mime'=>$mime,'logo_in_html'=>strpos($html,$logo)!==false,'qr_in_html'=>strpos($html,$qr)!==false];
try {
 $dompdf=new Dompdf\Dompdf(['tempDir'=>$temp,'fontCache'=>$temp,'isRemoteEnabled'=>false]);
 $dompdf->loadHtml($html);$dompdf->setPaper('a4','portrait');$dompdf->render();
 $pdf=$dompdf->output(['compress'=>0]);
 file_put_contents($file.'.pdf',$pdf);
 $result+=['rendered'=>str_starts_with($pdf,'%PDF-'),'pages'=>$dompdf->getCanvas()->get_page_count(),'jpeg_embedded'=>strpos($pdf,'/DCTDecode')!==false,'pdf_path'=>$file.'.pdf'];
} catch (Throwable $e) {
 $result+=['rendered'=>false,'error'=>$e->getMessage()];
}
echo json_encode($result,JSON_UNESCAPED_SLASHES),PHP_EOL;