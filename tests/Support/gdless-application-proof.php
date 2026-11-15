<?php
// Isolated in-memory application proof: no Supabase connection or credentials used.
require dirname(__DIR__,2).'/vendor/autoload.php';
$app=require dirname(__DIR__,2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','cache.default'=>'array','filesystems.disks.public'=>['driver'=>'local','root'=>$argv[1],'throw'=>true]]);
Illuminate\Support\Facades\DB::purge();
Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
$user=App\Models\User::create(['name'=>'Test','email'=>'gdless@example.test','password'=>'unused','role'=>'pendaftar']);
$profile=App\Models\SchoolProfile::create(['name'=>'Madrasah GD-less','logo'=>'original.png']);
$a=App\Models\StudentApplication::create(['user_id'=>$user->id,'registration_number'=>'SPMB-GDLESS-0001','status'=>'submitted']);
auth()->login($user);
$controller=app(App\Http\Controllers\ApplicationPrintController::class);
$preview=$controller->preview($a)->getData();
$pdf=$controller->pdf($a)->getContent();
$admin=$controller->adminPdf($a)->getContent();
file_put_contents($argv[1].'/proof.pdf',$pdf);
echo json_encode(['gd'=>extension_loaded('gd'),'logo_width'=>$preview['logo_width'],'logo_height'=>$preview['logo_height'],'jpeg'=>str_starts_with($preview['logo'],'data:image/jpeg;base64,'),'qr_svg'=>str_contains(base64_decode($preview['qr']),'<svg'),'url'=>$preview['url'],'token'=>$a->fresh()->public_token,'applicant_pdf'=>str_starts_with($pdf,'%PDF-')&&str_contains($pdf,'/Subtype /Image'),'admin_pdf'=>str_starts_with($admin,'%PDF-')&&str_contains($admin,'/Subtype /Image')]),PHP_EOL;