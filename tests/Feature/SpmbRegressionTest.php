<?php
namespace Tests\Feature;
use App\Models\{User,AdmissionPeriod,AdmissionWave,StudentApplication};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Hash,Storage};
use Tests\TestCase;

class SpmbRegressionTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp(); config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        $this->artisan('migrate',['--force'=>true])->assertExitCode(0); Storage::fake('public'); Storage::fake('spmb');
    }
    private function user($role,$email) { return User::create(['name'=>'Test '.$role,'email'=>$email,'phone'=>'08123456789','password'=>Hash::make('password123'),'role'=>$role]); }
    public function test_application_submission_revision_acceptance_pdf_qr_and_export() {
        $admin=$this->user('admin','admin@regression.test'); $applicant=$this->user('pendaftar','wali@regression.test');
        $period=AdmissionPeriod::create(['name'=>'Periode Aktif','academic_year'=>'2026/2027','start_date'=>today()->subDays(5),'end_date'=>today()->addDays(5),'is_active'=>true]);
        $wave=AdmissionWave::create(['admission_period_id'=>$period->id,'name'=>'Gelombang Aktif','start_date'=>today()->subDay(),'end_date'=>today()->addDay(),'is_active'=>true]);
        $this->actingAs($applicant)->get('/dashboard')->assertOk();
        $this->post('/dashboard/biodata',['full_name'=>'Siswa Pengujian','gender'=>'L','address'=>'Alamat siswa'])->assertSessionHasNoErrors()->assertRedirect();
        $this->post('/dashboard/orang-tua',['father_name'=>'Ayah','mother_name'=>'Ibu'])->assertSessionHasNoErrors()->assertRedirect();
        foreach (['student_photo','family_card','birth_certificate'] as $type) $this->post('/dashboard/dokumen/'.$type,['file'=>UploadedFile::fake()->image($type.'.png')])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/dashboard/review')->assertOk();
        $this->post('/dashboard/submit')->assertRedirect(route('applicant.status'));
        $a=StudentApplication::firstOrFail();
        $this->assertSame('submitted',$a->status); $this->assertSame($period->id,(int)$a->admission_period_id); $this->assertSame($wave->id,(int)$a->admission_wave_id);
        $this->get('/dashboard/status')->assertOk()->assertSee($a->registration_number);
        $this->actingAs($admin)->get('/admin/pendaftar/'.$a->id)->assertOk();
        $this->post('/admin/pendaftar/'.$a->id.'/status',['to'=>'under_review'])->assertRedirect();
        $this->post('/admin/pendaftar/'.$a->id.'/status',['to'=>'revision','note'=>'Perbaiki alamat'])->assertRedirect();
        $this->assertSame('revision',$a->fresh()->status);
        $this->actingAs($applicant)->get('/dashboard/status')->assertSee('Perbaiki alamat');
        $this->post('/dashboard/biodata',['full_name'=>'Siswa Pengujian','gender'=>'L','address'=>'Alamat diperbaiki'])->assertRedirect();
        $this->post('/dashboard/submit')->assertRedirect();
        $this->actingAs($admin);
        foreach (['under_review','verified','accepted'] as $status) $this->post('/admin/pendaftar/'.$a->id.'/status',['to'=>$status])->assertRedirect();
        $this->assertSame('accepted',$a->fresh()->status);
        $this->get('/admin/pendaftar/'.$a->id.'/pdf')->assertOk()->assertHeader('content-type','application/pdf');
        $this->get('/admin/spmb/laporan?period='.$period->id)->assertOk()->assertSee('Siswa Pengujian');
        $export=$this->get('/admin/spmb/laporan/export?period='.$period->id.'&status=accepted')->assertOk();
        $this->assertStringContainsString('Siswa Pengujian',$export->streamedContent());
        $this->actingAs($applicant);
        $this->get('/dashboard/cetak-bukti/'.$a->id)->assertOk();
        $this->get('/dashboard/cetak-bukti/'.$a->id.'/pdf')->assertOk()->assertHeader('content-type','application/pdf');
        $this->assertNotNull($a->fresh()->public_token);
        $this->get('/verifikasi-spmb/'.$a->fresh()->public_token)->assertOk()->assertSee('Siswa Pengujian');
        $this->get('/verifikasi-spmb/invalid-token')->assertNotFound();
        $this->actingAs($this->user('pendaftar','other@regression.test'))->get('/dashboard/cetak-bukti/'.$a->id)->assertForbidden();
    }
    public function test_rejected_status_requires_note_and_export_filter_is_safe() {
        $admin=$this->user('super_admin','super@regression.test'); $user=$this->user('pendaftar','wali@regression.test');
        $a=StudentApplication::create(['user_id'=>$user->id,'registration_number'=>'SPMB-2026-9999','status'=>'verified']);
        $this->actingAs($admin);
        $this->post('/admin/pendaftar/'.$a->id.'/status',['to'=>'rejected'])->assertStatus(422);
        $this->post('/admin/pendaftar/'.$a->id.'/status',['to'=>'rejected','note'=>'Kuota penuh'])->assertRedirect();
        $this->assertSame('rejected',$a->fresh()->status);
        $this->post('/admin/pendaftar/'.$a->id.'/status',['to'=>'accepted'])->assertStatus(422);
        $user->update(['name'=>'=HYPERLINK("evil")']);
        $csv=$this->get('/admin/spmb/laporan/export?status=rejected')->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK",$csv);
        $csv=$this->get('/admin/spmb/laporan/export?status=accepted')->assertOk()->streamedContent();
        $this->assertStringNotContainsString('SPMB-2026-9999',$csv);
    }
    public function test_search_preserves_status_filter_and_closed_wave_blocks_submission() {
        $admin=$this->user('admin','adminfilter@regression.test');
        $u1=$this->user('pendaftar','wali1@regression.test'); $u2=$this->user('pendaftar','wali2@regression.test');
        $a1=StudentApplication::create(['user_id'=>$u1->id,'status'=>'submitted','registration_number'=>'SPMB-filter-1']);
        $a2=StudentApplication::create(['user_id'=>$u2->id,'status'=>'accepted','registration_number'=>'SPMB-filter-2']);
        $this->actingAs($admin)->get('/admin/pendaftar?search=Test&status=submitted')->assertOk()->assertSee('SPMB-filter-1')->assertDontSee('SPMB-filter-2');
        $this->actingAs($u1);
        $a1->update(['status'=>'draft','registration_number'=>null]);
        $a1->studentProfile()->create(['full_name'=>'Siswa','gender'=>'L','address'=>'Alamat']);
        $a1->parentProfile()->create(['father_name'=>'Ayah','mother_name'=>'Ibu']);
        foreach (['student_photo','family_card','birth_certificate'] as $type) $a1->documents()->create(['document_type'=>$type,'file_path'=>'spmb/test.png','original_name'=>'test.png','mime_type'=>'image/png','file_size'=>100]);
        $period=AdmissionPeriod::create(['name'=>'Periode Tertutup','academic_year'=>'2026/2027','start_date'=>today()->subDay(),'end_date'=>today()->addDay(),'is_active'=>false]);
        AdmissionWave::create(['admission_period_id'=>$period->id,'name'=>'Gelombang','start_date'=>today()->subDay(),'end_date'=>today()->addDay(),'is_active'=>true]);
        $this->post('/dashboard/submit')->assertRedirect(route('applicant.review'))->assertSessionHasErrors('submit');
        $this->assertSame('draft',$a1->fresh()->status);
    }
    public function test_pdf_long_data_preserves_a4_logo_qr_and_layout()
    {
        $user=$this->user('pendaftar','longpdf@regression.test');
        $a=StudentApplication::create(['user_id'=>$user->id,'registration_number'=>'SPMB-PDF-0001','status'=>'accepted']);
        $a->studentProfile()->create([
            'full_name'=>str_repeat('Nama Siswa Panjang ',10),
            'gender'=>'L','address'=>str_repeat('Alamat lengkap dusun desa kecamatan kabupaten. ',60),
        ]);
        $a->parentProfile()->create(['father_name'=>str_repeat('Nama Ayah ',10),'mother_name'=>str_repeat('Nama Ibu ',10)]);
        $this->actingAs($user);
        $preview=$this->get('/dashboard/cetak-bukti/'.$a->id)->assertOk();
        $data=$preview->original->getData();
        $this->assertStringStartsWith('data:image/jpeg;base64,',$data['logo']);
        $this->assertStringContainsString('<svg',base64_decode($data['qr'],true));
        $this->assertStringContainsString('/verifikasi-spmb/'.$a->fresh()->public_token,$data['url']);
        $pdf=\Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.proof',$data)->setPaper('a4','portrait');
        $binary=$pdf->output();
        $this->assertStringStartsWith('%PDF-',$binary);
        $this->assertStringContainsString('/Subtype /Image',$binary);
        $canvas=$pdf->getDomPDF()->getCanvas();
        $this->assertEqualsWithDelta(595.28,$canvas->get_width(),1);
        $this->assertEqualsWithDelta(841.89,$canvas->get_height(),1);
        $this->assertGreaterThanOrEqual(1,$canvas->get_page_count());
        $this->assertStringContainsString('SPMB-PDF-0001',view('pdf.proof',$data)->render());
    }
}
