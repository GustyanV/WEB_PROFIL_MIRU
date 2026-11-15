<?php
namespace Tests\Feature;
use App\Models\{SchoolProfile,User};
use App\Support\PdfLogo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class PdfLogoUploadTest extends TestCase
{
 protected function setUp():void {parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);$this->artisan('migrate',['--force'=>true]);Storage::fake('public');}
 public function test_original_png_and_pdf_jpeg_are_kept_separately():void {
  $this->actingAs(User::factory()->create(['role'=>'admin']));
  $original=UploadedFile::fake()->image('logo.png',200,100);
  $jpeg=UploadedFile::fake()->image('pdf.jpg',200,100);
  $bytes=file_get_contents($original->getRealPath());
  $this->put('/admin/profil',['name'=>'Madrasah','logo'=>$original,'pdf_logo'=>$jpeg,'pdf_logo_hash'=>hash('sha256',$bytes),'pdf_logo_approved'=>'1'])->assertSessionHasNoErrors()->assertRedirect();
  $profile=SchoolProfile::firstOrFail();
  $this->assertSame($bytes,Storage::disk('public')->get($profile->logo));
  $this->assertStringStartsWith('data:image/jpeg;base64,',app(PdfLogo::class)->forProfile($profile));
  $this->assertStringContainsString($profile->logo,$profile->site_logo_url);
 }
 public function test_pdf_derivative_requires_matching_source_and_dimensions():void {
  $this->actingAs(User::factory()->create(['role'=>'admin']));
  $this->put('/admin/profil',['name'=>'Madrasah','logo'=>UploadedFile::fake()->image('logo.png',200,100),'pdf_logo'=>UploadedFile::fake()->image('pdf.jpg',100,100),'pdf_logo_hash'=>'wrong','pdf_logo_approved'=>'1'])->assertSessionHasErrors('pdf_logo');
  $this->assertSame(0,SchoolProfile::count());
 }
 public function test_migration_of_existing_logo_retains_original_and_requires_review():void {
  $this->actingAs(User::factory()->create(['role'=>'admin']));
  $png=UploadedFile::fake()->image('old.png',200,100);$bytes=file_get_contents($png->getRealPath());Storage::disk('public')->put('school/old.png',$bytes);
  $profile=SchoolProfile::create(['name'=>'Madrasah','logo'=>'school/old.png']);
  $this->get('/admin/profil/logo-source')->assertOk()->assertContent($bytes);
  $this->put('/admin/profil',['name'=>'Madrasah','pdf_logo'=>UploadedFile::fake()->image('pdf.jpg',200,100),'pdf_logo_hash'=>hash('sha256',$bytes)])->assertSessionHasErrors('pdf_logo_approved');
  $this->assertSame('school/old.png',$profile->fresh()->logo);
 }
 public function test_existing_png_is_migrated_without_replacing_or_deleting_original():void {
  $this->actingAs(User::factory()->create(['role'=>'admin']));
  $png=UploadedFile::fake()->image('old.png',200,100);$bytes=file_get_contents($png->getRealPath());Storage::disk('public')->put('school/old.png',$bytes);
  $profile=SchoolProfile::create(['name'=>'Madrasah','logo'=>'school/old.png']);
  $this->put('/admin/profil',['name'=>'Madrasah','pdf_logo'=>UploadedFile::fake()->image('pdf.jpg',200,100),'pdf_logo_hash'=>hash('sha256',$bytes),'pdf_logo_approved'=>'1'])->assertSessionHasNoErrors();
  $this->assertSame('school/old.png',$profile->fresh()->logo);$this->assertSame($bytes,Storage::disk('public')->get('school/old.png'));
  $this->assertStringStartsWith('data:image/jpeg;base64,',app(PdfLogo::class)->forProfile($profile));
 }
 public function test_derivative_is_not_deleted_when_database_save_fails():void {
  $this->actingAs(User::factory()->create(['role'=>'admin']));
  $png=UploadedFile::fake()->image('old.png',200,100);$bytes=file_get_contents($png->getRealPath());Storage::disk('public')->put('school/old.png',$bytes);
  SchoolProfile::create(['name'=>'Madrasah','logo'=>'school/old.png']);
  SchoolProfile::saving(function(){throw new \RuntimeException('Simulated save failure');});
  try {$this->put('/admin/profil',['name'=>'Madrasah','pdf_logo'=>UploadedFile::fake()->image('pdf.jpg',200,100),'pdf_logo_hash'=>hash('sha256',$bytes),'pdf_logo_approved'=>'1'])->assertStatus(500);}
  finally {SchoolProfile::flushEventListeners();}
  Storage::disk('public')->assertExists(app(PdfLogo::class)->key($bytes));Storage::disk('public')->assertExists('school/old.png');
 }
 public function test_original_source_is_admin_only_and_png_requires_prepared_pdf():void {
  $this->get('/admin/profil/logo-source')->assertRedirect('/login');
  $this->actingAs(User::factory()->create(['role'=>'pendaftar']))->get('/admin/profil/logo-source')->assertForbidden();
  $this->actingAs(User::factory()->create(['role'=>'admin']));
  $this->put('/admin/profil',['name'=>'Madrasah','logo'=>UploadedFile::fake()->image('logo.png')])->assertSessionHasErrors('pdf_logo');
 }

}