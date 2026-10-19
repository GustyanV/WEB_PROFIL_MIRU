<?php
namespace Tests\Feature;

use App\Models\{User,SchoolProfile,Program,News,Teacher,Extracurricular,HeroBanner,GalleryAlbum,Activity,AdmissionPeriod,AdmissionWave};
use App\Support\SchoolContent;
use Database\Seeders\{DatabaseSeeder,ProductionSeeder,DevelopmentSeeder,PublicDemoSeeder};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Hash,Storage};
use Tests\TestCase;

class SchoolDataFinalizationTest extends TestCase
{
    private $credentials;
    protected function setUp(): void {
        parent::setUp(); config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        $this->artisan('migrate',['--force'=>true])->assertExitCode(0); Storage::fake('public');
        $this->credentials=[];
        foreach(['INITIAL_SUPER_ADMIN_NAME','INITIAL_SUPER_ADMIN_EMAIL','INITIAL_SUPER_ADMIN_PASSWORD','SCHOOL_NAME'] as $key) $this->credentials[$key]=[$_ENV[$key]??null,$_SERVER[$key]??null];
    }
    protected function tearDown(): void {
        foreach($this->credentials as $key=>[$env,$server]) {
            if($env===null)unset($_ENV[$key]);else $_ENV[$key]=$env;
            if($server===null)unset($_SERVER[$key]);else $_SERVER[$key]=$server;
        }
        parent::tearDown();
    }
    private function admin($role='admin') {
        return User::create(['name'=>'Admin Pengujian','email'=>$role.'@sekolah.test','password'=>Hash::make('password123'),'role'=>$role]);
    }
    public function test_school_profile_fields_and_photo_are_editable_and_public() {
        $this->actingAs($this->admin());
        $this->get('/admin/profil')->assertOk()->assertSee('Tentang Madrasah')->assertSee('Komitmen Pendidikan');
        $data=['name'=>'Madrasah Data Resmi','nsm'=>'111111111111','npsn'=>'12345678','address'=>'Alamat Resmi','phone'=>'0335123456',
            'whatsapp'=>'081298765432','email'=>'info@madrasah.sch.id','website'=>'https://madrasah.sch.id',
            'about'=>'Tentang yang disahkan','history'=>'Sejarah yang disahkan','vision'=>'Visi yang disahkan','mission'=>'Misi yang disahkan',
            'goals'=>'Tujuan yang disahkan','education_commitment'=>'Komitmen yang disahkan','headmaster_name'=>'Kepala Resmi',
            'headmaster_message'=>'Sambutan yang disahkan','headmaster_photo'=>UploadedFile::fake()->image('kepala.jpg')];
        $this->put('/admin/profil',$data)->assertRedirect()->assertSessionHasNoErrors();
        $profile=SchoolProfile::firstOrFail(); Storage::disk('public')->assertExists($profile->headmaster_photo);
        $this->get('/profil')->assertOk()->assertSee('Tentang yang disahkan')->assertSee('111111111111')->assertSee('12345678');
        $this->get('/profil/sejarah')->assertSee('Sejarah yang disahkan');
        $this->get('/profil/visi-misi')->assertSee('Visi yang disahkan')->assertSee('Misi yang disahkan')->assertSee('Tujuan yang disahkan');
        $this->get('/profil/sambutan')->assertSee('Kepala Resmi')->assertSee('Sambutan yang disahkan')->assertSee('storage/'.$profile->headmaster_photo);
    }
    public function test_known_demo_contacts_are_hidden_without_mutating_database() {
        $profile=SchoolProfile::create(['name'=>'Madrasah','address'=>'Alamat belum diverifikasi','phone'=>'0335-000000','whatsapp'=>'081200000000','email'=>'info@mi-raudlatululum.test','website'=>'https://mi-raudlatululum.test','history'=>'Konten berikut merupakan data demo.']);
        foreach(['/','/kontak','/profil','/login'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('0335-000000')->assertDontSee('081200000000')->assertDontSee('info@mi-raudlatululum.test')->assertDontSee('https://mi-raudlatululum.test');
        }
        $this->get('/profil/sejarah')->assertDontSee('Konten berikut merupakan data demo.')->assertSee('Sejarah madrasah belum diisi.');
        $this->assertSame('0335-000000',$profile->fresh()->phone);
        $this->assertSame('info@mi-raudlatululum.test',$profile->fresh()->email);
        $profile->update(['phone'=>'0335123456','whatsapp'=>'081298765432','email'=>'info@madrasah.sch.id']);
        $this->get('/kontak')->assertSee('0335123456')->assertSee('info@madrasah.sch.id')->assertSee('https://wa.me/6281298765432');
    }
    public function test_explicit_dummy_content_is_not_public_and_can_be_replaced_by_admin() {
        $news=News::create(['title'=>'Dummy berita','slug'=>'dummy-berita','content'=>'Ini adalah konten dummy berita.','status'=>'published','published_at'=>now()]);
        $this->get('/berita')->assertDontSee('Dummy berita');
        $this->get('/berita/dummy-berita')->assertNotFound();
        $this->assertSame(1,News::count());
        $this->actingAs($this->admin());
        $this->get('/admin/berita')->assertSee('Dummy berita');
        $this->put('/admin/berita/'.$news->id,['title'=>'Berita Resmi Sekolah','slug'=>$news->slug,'excerpt'=>'Ringkasan resmi','content'=>'Isi kegiatan yang sudah diverifikasi.','status'=>'published','published_at'=>now()->format('Y-m-d H:i:s')])->assertRedirect()->assertSessionHasNoErrors();
        $this->get('/berita/'.$news->slug)->assertOk()->assertSee('Berita Resmi Sekolah');
        $this->assertSame(1,News::count());
    }
    public function test_extracurricular_coach_schedule_and_teacher_bio_are_dynamic() {
        $this->actingAs($this->admin());
        $extra=Extracurricular::create(['name'=>'Pramuka Resmi','description'=>'Latihan','is_active'=>true]);
        $this->put('/admin/ekstrakurikuler/'.$extra->id,['name'=>'Pramuka Resmi','coach_name'=>'Pembina Resmi','schedule'=>'Jumat 14.00','description'=>'Latihan resmi','is_active'=>1])->assertRedirect()->assertSessionHasNoErrors();
        $this->get('/ekstrakurikuler')->assertOk()->assertSee('Pembina Resmi')->assertSee('Jumat 14.00');
        Teacher::create(['name'=>'Guru Resmi','position'=>'Guru Kelas','subject'=>'Matematika','nip'=>'0012345','nuptk'=>'0098765','bio'=>'Bio guru resmi','is_active'=>true]);
        $this->get('/guru')->assertOk()->assertSee('Bio guru resmi')->assertSee('0012345')->assertSee('0098765');
    }
    public function test_missing_photos_use_neutral_fallback_and_uploaded_photos_replace_them() {
        foreach(['school','teacher','headmaster','news','activity','gallery'] as $kind) {
            $url=SchoolContent::image('missing.jpg',$kind);
            $this->assertStringContainsString('fallback-',$url);
            $this->assertFileExists(public_path(parse_url($url,PHP_URL_PATH)));
        }
        $album=GalleryAlbum::create(['title'=>'Album','slug'=>'album']);
        $album->images()->create(['image'=>'images/gallery-placeholder.jpg']);
        $this->get('/galeri/album')->assertOk()->assertSee('fallback-gallery.svg')->assertDontSee('images/gallery-placeholder.jpg');
        $teacher=Teacher::create(['name'=>'Guru','is_active'=>true,'photo'=>'missing.jpg']);
        $this->get('/guru')->assertSee('fallback-teacher.svg')->assertDontSee('missing.jpg');
        Storage::disk('public')->put('teachers/resmi.png','fixture');
        $teacher->update(['photo'=>'teachers/resmi.png']);
        $this->get('/guru')->assertSee('storage/teachers/resmi.png');
    }
    public function test_homepage_uses_database_photos_and_admission_year_without_false_open_claim() {
        $period=AdmissionPeriod::create(['name'=>'Periode Final','academic_year'=>'2030/2031','start_date'=>today()->subDay(),'end_date'=>today()->addDay(),'is_active'=>true]);
        Storage::disk('public')->put('news/resmi.png','fixture');
        News::create(['title'=>'Berita Foto Resmi','slug'=>'foto-resmi','content'=>'Isi resmi','status'=>'published','thumbnail'=>'news/resmi.png']);
        $this->get('/')->assertOk()->assertSee('2030/2031')->assertSee('storage/news/resmi.png')->assertDontSee('Pendaftaran Murid Baru Telah Dibuka');
        AdmissionWave::create(['admission_period_id'=>$period->id,'name'=>'Gelombang Aktif','start_date'=>today()->subDay(),'end_date'=>today()->addDay(),'is_active'=>true]);
        $this->get('/')->assertSee('Pendaftaran Murid Baru Telah Dibuka');
    }
    public function test_development_seeders_do_not_overwrite_real_data_or_duplicate_profile() {
        $profile=SchoolProfile::create(['name'=>'Nama Resmi Berbeda','history'=>'Sejarah resmi','phone'=>'0335123456']);
        Program::create(['title'=>'Tahsin dan Tilawah','description'=>'Deskripsi resmi','is_active'=>false]);
        $this->seed(DevelopmentSeeder::class);
        $this->seed(PublicDemoSeeder::class);
        $this->assertSame(1,SchoolProfile::count());
        $this->assertSame('Sejarah resmi',$profile->fresh()->history);
        $this->assertSame('Nama Resmi Berbeda',$profile->fresh()->name);
        $program=Program::where('title','Tahsin dan Tilawah')->firstOrFail();
        $this->assertSame('Deskripsi resmi',$program->description); $this->assertFalse($program->is_active);
        $super=User::where('role','super_admin')->first(); $super->update(['password'=>Hash::make('changedpassword123')]);
        $this->seed(DevelopmentSeeder::class);
        $this->assertTrue(Hash::check('changedpassword123',$super->fresh()->password));
        $this->assertSame(1,SchoolProfile::count());
    }
    public function test_default_seed_is_minimal_and_never_resets_existing_super_admin() {
        $super=$this->admin('super_admin'); $password=$super->password;
        SchoolProfile::create(['name'=>'Sekolah Resmi']);
        $this->seed(DatabaseSeeder::class);
        $this->assertSame($password,$super->fresh()->password);
        $this->assertSame('Sekolah Resmi',SchoolProfile::first()->name);
        $this->assertSame(0,Teacher::count()); $this->assertSame(0,News::count()); $this->assertSame(0,Activity::count());
    }
    public function test_production_bootstrap_requires_explicit_credentials_and_no_demo_defaults() {
        foreach(['INITIAL_SUPER_ADMIN_NAME','INITIAL_SUPER_ADMIN_EMAIL','INITIAL_SUPER_ADMIN_PASSWORD'] as $key) { $_ENV[$key]=''; $_SERVER[$key]=''; }
        try { $this->seed(ProductionSeeder::class); $this->fail('Missing credentials must fail'); }
        catch(\RuntimeException $e) { $this->assertStringContainsString('INITIAL_SUPER_ADMIN',$e->getMessage()); }
        $this->assertSame(0,User::count()); $this->assertSame(0,SchoolProfile::count());
        foreach(['INITIAL_SUPER_ADMIN_NAME'=>'Pengelola Resmi','INITIAL_SUPER_ADMIN_EMAIL'=>'pengelola@madrasah.sch.id','INITIAL_SUPER_ADMIN_PASSWORD'=>'secretproduction123','SCHOOL_NAME'=>'Madrasah Resmi'] as $key=>$value) { $_ENV[$key]=$value; $_SERVER[$key]=$value; }
        $this->seed(ProductionSeeder::class);
        $this->assertSame('Madrasah Resmi',SchoolProfile::first()->name);
        $this->assertTrue(Hash::check('secretproduction123',User::first()->password));
        $this->assertSame(0,News::count()); $this->assertSame(0,HeroBanner::count());
    }
    public function test_development_seeders_are_noops_in_production() {
        $this->app['env']='production';
        app(DevelopmentSeeder::class)->run(); app(PublicDemoSeeder::class)->run();
        app(\Database\Seeders\ActivitySeeder::class)->run(); app(\Database\Seeders\HeroBannerSeeder::class)->run();
        $this->assertSame(0,SchoolProfile::count()); $this->assertSame(0,User::count()); $this->assertSame(0,News::count()); $this->assertSame(0,Activity::count());
        $this->app['env']='testing';
    }
}
