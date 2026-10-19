<?php

namespace Tests\Feature;

use App\Models\HeroBanner;
use App\Models\User;
use Database\Seeders\HeroBannerSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroBannerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Storage::fake('public');
    }

    private function admin($role = 'admin')
    {
        return User::create(['name' => 'Hero Tester', 'email' => $role.'@hero.test', 'password' => bcrypt('test-password'), 'role' => $role]);
    }

    public function test_admin_can_edit_upload_and_see_changes_on_homepage()
    {
        $this->seed(HeroBannerSeeder::class);
        $this->seed(HeroBannerSeeder::class);
        $this->assertSame(1, HeroBanner::count());
        $this->actingAs($this->admin())->get('/admin/hero')->assertOk()->assertSee('SIMPAN HERO');
        $this->put('/admin/hero', [
            'title' => 'Judul Hero Terbaru', 'academic_year' => '2030/2031',
            'subtitle' => 'Subtitle baru', 'small_text' => 'Pendaftaran',
            'banner_desktop' => UploadedFile::fake()->image('siswa.png'),
            'banner_mobile' => UploadedFile::fake()->image('latar.jpg'),
            'primary_button_label' => 'Daftar', 'primary_button_url' => '/register',
            'secondary_button_label' => 'Informasi', 'secondary_button_url' => '#informasi-spmb',
            'is_active' => 1,
        ])->assertRedirect(route('admin.hero.edit'))->assertSessionHasNoErrors();
        $hero = HeroBanner::first();
        Storage::disk('public')->assertExists([$hero->banner_desktop, $hero->banner_mobile]);
        $this->assertStringStartsWith('hero/', $hero->banner_desktop);
        $this->get('/')->assertOk()->assertSee('Judul Hero Terbaru')->assertSee('2030/2031')
            ->assertSee('storage/'.$hero->banner_desktop)->assertSee('storage/'.$hero->banner_mobile);
        $oldImage = $hero->banner_desktop;
        $this->patch('/admin/hero', ['title' => 'Hero dinonaktifkan', 'academic_year' => '2031/2032', 'is_active' => 0])
            ->assertSessionHasNoErrors();
        $this->assertSame($oldImage, $hero->fresh()->banner_desktop);
        $this->get('/')->assertOk()->assertDontSee('Hero dinonaktifkan');
    }

    public function test_validation_rejects_invalid_images_and_unsafe_urls()
    {
        $this->actingAs($this->admin());
        $this->put('/admin/hero', [
            'title' => '', 'is_active' => 'invalid',
            'banner_desktop' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'),
            'banner_mobile' => UploadedFile::fake()->image('large.png')->size(2049),
            'primary_button_url' => 'javascript:alert(1)',
            'secondary_button_url' => '//example.com',
        ])->assertSessionHasErrors(['title', 'is_active', 'banner_desktop', 'banner_mobile', 'primary_button_url', 'secondary_button_url']);
        $this->assertSame(0, HeroBanner::count());
    }

    public function test_access_is_limited_to_admin_and_super_admin()
    {
        $this->get('/admin/hero')->assertRedirect('/login');
        $this->actingAs($this->admin('pendaftar'))->get('/admin/hero')->assertForbidden();
        $this->put('/admin/hero', ['title' => 'Unauthorized', 'is_active' => 1])->assertForbidden();
        $this->actingAs($this->admin('super_admin'))->get('/admin/hero')->assertOk();
    }

    public function test_homepage_has_fallback_when_hero_is_empty()
    {
        $this->get('/')->assertOk()->assertSee('Sistem Penerimaan Murid Baru');
    }
    public function test_mobile_fallback_replacement_and_legacy_files_are_preserved()
    {
        $this->actingAs($this->admin());
        Storage::disk('public')->put('hero/legacy.png','legacy');
        $hero=HeroBanner::create(['title'=>'Banner gabungan','is_active'=>true,'image'=>'hero/legacy.png','background_image'=>'hero/legacy.png']);
        $this->put('/admin/hero',['title'=>$hero->title,'is_active'=>1,'banner_desktop'=>UploadedFile::fake()->image('desktop.jpg')])->assertRedirect()->assertSessionHasNoErrors();
        $hero->refresh(); $desktop=$hero->banner_desktop;
        $this->get('/')->assertOk()->assertSee('storage/'.$desktop)->assertDontSee('storage/hero/legacy.png')->assertDontSee('hero-pattern.png');
        $this->assertNull($hero->banner_mobile);
        $this->put('/admin/hero',['title'=>$hero->title,'is_active'=>1,'banner_mobile'=>UploadedFile::fake()->image('mobile.jpg')])->assertRedirect()->assertSessionHasNoErrors();
        $hero->refresh(); $mobile=$hero->banner_mobile;
        $this->get('/')->assertSee('media="(max-width: 767px)"',false)->assertSee('storage/'.$mobile);
        $this->put('/admin/hero',['title'=>$hero->title,'is_active'=>1,'banner_desktop'=>UploadedFile::fake()->image('new.jpg'),'remove_mobile'=>1])->assertRedirect()->assertSessionHasNoErrors();
        $hero->refresh();
        Storage::disk('public')->assertMissing([$desktop,$mobile]);
        Storage::disk('public')->assertExists('hero/legacy.png');
        $this->assertSame('hero/legacy.png',$hero->image);
        $this->assertNull($hero->banner_mobile);
    }

    public function test_missing_banner_has_plain_fallback_and_cms_has_design_guidance()
    {
        HeroBanner::create(['title'=>'Banner tanpa file','banner_desktop'=>'hero/missing.jpg','is_active'=>true]);
        $this->get('/')->assertOk()->assertSee('Banner tanpa file')->assertDontSee('hero/missing.jpg')->assertDontSee('fallback-school.svg')->assertDontSee('hero-pattern.png');
        $this->actingAs($this->admin())->get('/admin/hero')->assertOk()->assertSee('1920 × 720')->assertSee('768 × 960')->assertSee('Banner Desktop')->assertSee('Banner Mobile')->assertDontSee('name="background_image"',false)->assertDontSee('name="image"',false);
    }
}
