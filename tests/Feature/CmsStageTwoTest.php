<?php
namespace Tests\Feature;

use App\Models\{User,SchoolProfile,AdmissionPeriod,AdmissionWave,StudentApplication,News,Activity,Teacher,GalleryAlbum};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Hash,Storage};
use Tests\TestCase;

class CmsStageTwoTest extends TestCase
{
    private $sequence=0;
    protected function setUp(): void {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        $this->artisan('migrate',['--force'=>true])->assertExitCode(0); Storage::fake('public');
    }
    private function user($role='admin',$active=true) {
        return User::create(['name'=>'CMS Tester '.++$this->sequence,'email'=>'cms'.$this->sequence.'@test.test','password'=>Hash::make('password123'),'role'=>$role,'is_active'=>$active]);
    }
    private function period(array $data=[]) {
        return AdmissionPeriod::create(array_merge(['name'=>'Periode Utama','academic_year'=>'2027/2028','start_date'=>'2027-01-01','end_date'=>'2027-12-31','quota'=>100,'is_active'=>true],$data));
    }
    private function adminData(array $data=[]) { return array_merge(['name'=>'Admin Baru','email'=>'newadmin@test.test','phone'=>'08123456789','password'=>'password123','password_confirmation'=>'password123','is_active'=>1],$data); }
    public function test_super_admin_manages_admin_accounts_without_role_escalation() {
        $super=$this->user('super_admin'); $this->actingAs($super);
        $this->get('/admin/pengguna')->assertOk()->assertSee('Tambah Admin');
        $this->get('/admin/pengguna/create')->assertOk();
        $this->post('/admin/pengguna',$this->adminData())->assertRedirect(route('admin.users.index'))->assertSessionHasNoErrors();
        $admin=User::where('email','newadmin@test.test')->firstOrFail();
        $this->assertSame('admin',$admin->role); $this->assertTrue(Hash::check('password123',$admin->password));
        $this->get('/admin/pengguna/'.$admin->id.'/edit')->assertOk()->assertSee('Reset Password');
        $this->get('/admin/pengguna?search=Admin+Baru')->assertSee('Admin Baru');
        $this->put('/admin/pengguna/'.$admin->id,['name'=>'Admin Diedit','email'=>$admin->email,'phone'=>'0812','is_active'=>0])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertFalse($admin->fresh()->is_active);
        $this->post('/admin/pengguna/'.$admin->id.'/reset-password',['password'=>'newpassword123','password_confirmation'=>'newpassword123'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue(Hash::check('newpassword123',$admin->fresh()->password));
        $this->post('/admin/pengguna',$this->adminData(['email'=>'injection@test.test','role'=>'super_admin']))->assertSessionHasErrors('role');
        $this->put('/admin/pengguna/'.$admin->id,['name'=>'Admin','email'=>$admin->email,'is_active'=>1,'role'=>'super_admin'])->assertSessionHasErrors('role');
        $this->assertSame('admin',$admin->fresh()->role);
    }
    public function test_authorization_and_super_admin_self_protection() {
        $super=$this->user('super_admin'); $admin=$this->user();
        $this->actingAs($admin);
        foreach (['/admin/pengguna','/admin/pengguna/create','/admin/pengguna/'.$admin->id.'/edit'] as $url) $this->get($url)->assertForbidden();
        $this->post('/admin/pengguna',$this->adminData())->assertForbidden();
        $this->put('/admin/pengguna/'.$admin->id,['name'=>'No','email'=>$admin->email,'is_active'=>0])->assertForbidden();
        $this->post('/admin/pengguna/'.$admin->id.'/reset-password',['password'=>'password456','password_confirmation'=>'password456'])->assertForbidden();
        $this->actingAs($super)->put('/admin/pengguna/'.$super->id,['name'=>$super->name,'email'=>$super->email,'is_active'=>0])->assertForbidden();
        $this->get('/admin/pengguna/'.$super->id.'/edit')->assertForbidden();
        $this->assertTrue($super->fresh()->is_active); $this->assertSame('super_admin',$super->fresh()->role);
        $this->delete('/admin/pengguna/'.$super->id)->assertNotFound();
        $applicant=$this->user('pendaftar'); $this->actingAs($applicant);
        foreach (['/admin','/admin/pengguna','/admin/spmb/periode','/admin/spmb/gelombang','/admin/pengaturan-website','/admin/spmb/laporan','/admin/spmb/laporan/export'] as $url) $this->get($url)->assertForbidden();
    }
    public function test_inactive_admin_cannot_login_or_use_existing_session_but_applicant_is_unchanged() {
        $admin=$this->user('admin',false);
        $this->post('/login',['email'=>$admin->email,'password'=>'password123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($admin)->get('/admin')->assertForbidden();
        auth()->logout();
        $applicant=$this->user('pendaftar',false);
        $this->post('/login',['email'=>$applicant->email,'password'=>'password123'])->assertRedirect(route('dashboard'));
        $this->get('/dashboard')->assertOk();
    }
    public function test_active_admin_login_and_password_validation() {
        $admin=$this->user();
        $this->post('/login',['email'=>$admin->email,'password'=>'password123'])->assertRedirect(route('admin.dashboard'));
        $this->get('/admin')->assertOk()->assertDontSee('Pengguna Admin');
        $this->actingAs($this->user('super_admin'));
        $this->post('/admin/pengguna',$this->adminData(['password'=>'short','password_confirmation'=>'wrong']))->assertSessionHasErrors('password');
    }
    public function test_period_create_edit_activation_dates_and_delete_protection() {
        $this->actingAs($this->user()); $old=$this->period();
        $data=['name'=>'Periode Baru','academic_year'=>'2028/2029','start_date'=>'2028-01-01','end_date'=>'2028-12-31','quota'=>50,'is_active'=>1];
        $this->get('/admin/spmb/periode/create')->assertOk();
        $this->post('/admin/spmb/periode',$data)->assertRedirect()->assertSessionHasNoErrors();
        $period=AdmissionPeriod::latest('id')->first();
        $this->assertFalse($old->fresh()->is_active); $this->assertSame(1,AdmissionPeriod::where('is_active',true)->count());
        $this->get('/admin/spmb/periode/'.$period->id.'/edit')->assertOk();
        $this->put('/admin/spmb/periode/'.$period->id,array_merge($data,['name'=>'Periode Diedit']))->assertSessionHasNoErrors();
        $this->assertTrue($period->fresh()->is_active);
        $this->patch('/admin/spmb/periode/'.$old->id,['name'=>$old->name,'academic_year'=>$old->academic_year,'start_date'=>'2027-01-01','end_date'=>'2027-12-31','quota'=>100,'is_active'=>1])->assertSessionHasNoErrors();
        $this->assertFalse($period->fresh()->is_active);
        $this->post('/admin/spmb/periode',array_merge($data,['start_date'=>'2029-01-01','quota'=>0]))->assertSessionHasErrors(['end_date','quota']);
        StudentApplication::create(['user_id'=>$this->user('pendaftar')->id,'admission_period_id'=>$old->id,'status'=>'submitted']);
        $this->delete('/admin/spmb/periode/'.$old->id)->assertSessionHasErrors('delete');
        $this->assertNotNull($old->fresh());
        $this->delete('/admin/spmb/periode/'.$period->id)->assertRedirect();
        $this->assertNull($period->fresh());
    }
    public function test_wave_create_edit_filter_and_date_and_application_protection() {
        $this->actingAs($this->user()); $period=$this->period(); $other=$this->period(['name'=>'Periode Lain','is_active'=>false]);
        $data=['admission_period_id'=>$period->id,'name'=>'Gelombang Pertama','start_date'=>'2027-01-01','end_date'=>'2027-03-31','quota'=>25,'is_active'=>1];
        $this->get('/admin/spmb/gelombang/create')->assertOk();
        $this->post('/admin/spmb/gelombang',$data)->assertRedirect()->assertSessionHasNoErrors();
        $wave=AdmissionWave::firstOrFail();
        $this->get('/admin/spmb/gelombang/'.$wave->id.'/edit')->assertOk();
        $this->put('/admin/spmb/gelombang/'.$wave->id,array_merge($data,['name'=>'Gelombang Diedit']))->assertSessionHasNoErrors();
        $this->get('/admin/spmb/gelombang?period='.$period->id)->assertSee('Gelombang Diedit');
        $this->get('/admin/spmb/gelombang?period='.$other->id)->assertDontSee('Gelombang Diedit');
        $this->post('/admin/spmb/gelombang',array_merge($data,['start_date'=>'2026-12-01']))->assertSessionHasErrors('start_date');
        $this->post('/admin/spmb/gelombang',array_merge($data,['end_date'=>'2026-01-01','quota'=>-1]))->assertSessionHasErrors(['end_date','quota']);
        StudentApplication::create(['user_id'=>$this->user('pendaftar')->id,'admission_period_id'=>$period->id,'admission_wave_id'=>$wave->id,'status'=>'submitted']);
        $this->delete('/admin/spmb/gelombang/'.$wave->id)->assertSessionHasErrors('delete');
        $this->put('/admin/spmb/gelombang/'.$wave->id,array_merge($data,['admission_period_id'=>$other->id]))->assertSessionHasErrors('admission_period_id');
        $this->put('/admin/spmb/periode/'.$period->id,['name'=>$period->name,'academic_year'=>$period->academic_year,'start_date'=>'2027-02-01','end_date'=>'2027-12-31','quota'=>10,'is_active'=>1])->assertSessionHasErrors('start_date');
        $this->assertNotNull($wave->fresh());
        $unused=AdmissionWave::create(array_merge($data,['name'=>'Belum digunakan']));
        $this->delete('/admin/spmb/gelombang/'.$unused->id)->assertRedirect();
        $this->assertNull($unused->fresh());
    }
    public function test_settings_upload_branding_and_footer_use_database() {
        $this->actingAs($this->user());
        $this->get('/admin/pengaturan-website')->assertOk();
        $data=['name'=>'Website Madrasah Baru','tagline'=>'Tagline baru madrasah','footer_text'=>'Teks footer dari CMS','copyright_text'=>'Hak cipta madrasah','primary_color'=>'#123456','accent_color'=>'#FEDCBA','logo'=>UploadedFile::fake()->image('logo.png'),'favicon'=>UploadedFile::fake()->image('favicon.png')];
        $data['pdf_logo']=UploadedFile::fake()->image('logo-pdf.jpg');
        $data['pdf_logo_hash']=hash_file('sha256',$data['logo']->getRealPath());
        $data['pdf_logo_approved']='1';
        $this->put('/admin/pengaturan-website',$data)->assertRedirect()->assertSessionHasNoErrors();
        $profile=SchoolProfile::firstOrFail();
        Storage::disk('public')->assertExists([$profile->logo,$profile->favicon]);
        $this->assertStringStartsWith('site/',$profile->logo);
        $oldLogo=$profile->logo;
        foreach (['/','/kontak','/kegiatan','/admin'] as $url) {
            $r=$this->get($url)->assertOk()->assertSee('Website Madrasah Baru')->assertSee('storage/'.$profile->favicon)->assertSee('--site-primary: 18 52 86;',false);
            if(in_array($url,['/','/kontak','/kegiatan'])) $r->assertSee('Teks footer dari CMS')->assertSee('Tagline baru madrasah')->assertSee('Hak cipta madrasah')->assertSee('storage/'.$profile->logo);
        }
        $this->put('/admin/pengaturan-website',['name'=>'Website Madrasah Baru','logo'=>UploadedFile::fake()->image('new.jpg')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists($oldLogo);
        $this->assertSame(1,SchoolProfile::count());
        auth()->logout();
        $this->get('/login')->assertOk()->assertSee('Website Madrasah Baru');
    }
    public function test_setting_validation_rejects_unsafe_uploads_and_colors() {
        $this->actingAs($this->user());
        $this->put('/admin/pengaturan-website',[
            'name'=>'Website','primary_color'=>'red; color: red','accent_color'=>'#abc',
            'logo'=>UploadedFile::fake()->createWithContent('logo.svg','<svg onload="alert(1)"></svg>'),
            'favicon'=>UploadedFile::fake()->createWithContent('favicon.ico','not an icon')
        ])->assertSessionHasErrors(['primary_color','accent_color','logo','favicon']);
        $this->put('/admin/pengaturan-website',['name'=>'Website','favicon'=>UploadedFile::fake()->image('large.png')->size(1025)])->assertSessionHasErrors('favicon');
    }
    public function test_dashboard_counts_recent_lists_and_sidebar_routes() {
        $this->actingAs($this->user('super_admin'));
        StudentApplication::create(['user_id'=>$this->user('pendaftar')->id,'status'=>'submitted']);
        StudentApplication::create(['user_id'=>$this->user('pendaftar')->id,'status'=>'accepted']);
        News::create(['title'=>'Berita Terbaru Database','slug'=>'berita-dashboard','content'=>'Isi','status'=>'published']);
        Activity::create(['title'=>'Kegiatan Terbaru Database','slug'=>'kegiatan-dashboard','category'=>'Sosial','activity_date'=>today(),'content'=>'Isi','status'=>'draft']);
        Teacher::create(['name'=>'Guru Database','is_active'=>true]);
        GalleryAlbum::create(['title'=>'Galeri Database','slug'=>'galeri-dashboard']);
        $this->get('/admin')->assertOk()->assertViewHas('stats',fn($s)=>$s['Total Pendaftar']===2 && $s['Menunggu Verifikasi']===1 && $s['Diterima']===1 && $s['Total Berita']===1 && $s['Total Kegiatan']===1 && $s['Total Guru']===1 && $s['Total Galeri']===1)
            ->assertSee('Berita Terbaru Database')->assertSee('Kegiatan Terbaru Database')->assertSee('Pengguna Admin');
        foreach (['/admin/hero','/admin/profil','/admin/guru','/admin/program','/admin/prestasi','/admin/ekstrakurikuler','/admin/kegiatan','/admin/berita','/admin/pengumuman','/admin/galeri','/admin/pendaftar','/admin/spmb/periode','/admin/spmb/gelombang','/admin/spmb/laporan','/admin/kontak','/admin/media-sosial','/admin/pengaturan-website','/admin/pengguna'] as $url) {
            $this->get($url)->assertOk()->assertSee('aria-current="page"',false);
        }
    }
    public function test_valid_ico_favicon_is_supported() {
        $this->actingAs($this->user());
        $pngFile=UploadedFile::fake()->image('icon.png',16,16);
        $png=file_get_contents($pngFile->getRealPath());
        $ico=pack('vvv',0,1,1).pack('CCCCvvVV',16,16,0,0,1,32,strlen($png),22).$png;
        $this->put('/admin/pengaturan-website',['name'=>'Website ICO','favicon'=>UploadedFile::fake()->createWithContent('site.ico',$ico)])->assertRedirect()->assertSessionHasNoErrors();
        $profile=SchoolProfile::firstOrFail();
        $this->assertStringEndsWith('.ico',$profile->favicon);
        Storage::disk('public')->assertExists($profile->favicon);
        $this->get('/')->assertOk()->assertSee('storage/'.$profile->favicon);
    }
    public function test_existing_cms_create_and_edit_forms_render_in_final_layout() {
        $this->actingAs($this->user());
        foreach (['guru','program','prestasi','ekstrakurikuler','berita','pengumuman','galeri'] as $type) $this->get('/admin/'.$type.'/create')->assertOk()->assertSee('SIMPAN');
        $news=News::create(['title'=>'Berita Uji Form','slug'=>'uji-form','content'=>'Isi','status'=>'published','published_at'=>now()]);
        $this->get('/admin/berita/'.$news->id.'/edit')->assertOk()->assertSee('Berita Uji Form');
    }}
