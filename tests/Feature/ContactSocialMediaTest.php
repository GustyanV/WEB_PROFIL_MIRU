<?php
namespace Tests\Feature;

use App\Models\SchoolProfile;
use App\Models\SocialMedia;
use App\Models\User;
use Database\Seeders\SocialMediaSeeder;
use Tests\TestCase;

class ContactSocialMediaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>':memory:']);
        $this->artisan('migrate', ['--force'=>true])->assertExitCode(0);
    }
    private function user($role = 'admin')
    {
        return User::create(['name'=>'Contact Tester', 'email'=>$role.'@contact.test', 'password'=>bcrypt('test-password'), 'role'=>$role]);
    }
    private function contactData(): array
    {
        return ['address'=>'Alamat baru madrasah', 'phone'=>'0335123456', 'whatsapp'=>'+62 812 3456 7890', 'email'=>'kontak@madrasah.sch.id', 'service_hours'=>'Senin Jumat 08.00 - 14.00', 'google_maps_embed_url'=>'https://www.google.com/maps/embed?pb=example'];
    }
    public function test_admin_updates_single_contact_and_public_components_immediately()
    {
        $this->actingAs($this->user())->get('/admin/kontak')->assertOk()->assertSee('SIMPAN KONTAK');
        $this->get('/')->assertOk();
        $this->put('/admin/kontak', $this->contactData())->assertRedirect(route('admin.contact.edit'))->assertSessionHasNoErrors();
        $this->assertSame(1, SchoolProfile::count());
        $this->patch('/admin/kontak', $this->contactData())->assertSessionHasNoErrors();
        $this->assertSame(1, SchoolProfile::count());
        foreach (['/', '/kontak'] as $url) {
            $response = $this->get($url)->assertOk();
            foreach (['Alamat baru madrasah','0335123456','kontak@madrasah.sch.id','Senin Jumat 08.00 - 14.00','https://wa.me/6281234567890'] as $text) $response->assertSee($text);
            $this->assertStringContainsString('Alamat baru madrasah', explode('</header>', $response->getContent())[0]);
            $this->assertStringContainsString('Alamat baru madrasah', explode('<footer', $response->getContent())[1]);
        }
        $this->get('/kontak')->assertSee('https://www.google.com/maps/embed?pb=example', false);
        $this->put('/admin/kontak', array_fill_keys(array_keys($this->contactData()), null))->assertSessionHasNoErrors();
        $this->get('/kontak')->assertOk()->assertSee('Peta lokasi belum tersedia')->assertDontSee('Alamat baru madrasah')->assertDontSee('https://wa.me/');
    }
    public function test_contact_validation_and_map_xss_protection()
    {
        $this->actingAs($this->user());
        foreach (['<iframe src="https://evil.test"></iframe>', 'https://evil.test/maps/embed', 'https://www.google.com.evil.test/maps/embed', 'javascript:alert(1)'] as $url) {
            $this->put('/admin/kontak', ['email'=>'invalid', 'google_maps_embed_url'=>$url])->assertSessionHasErrors(['email','google_maps_embed_url']);
        }
        SchoolProfile::create(['name'=>'Madrasah','google_maps_embed_url'=>'https://evil.test/maps/embed']);
        $this->get('/kontak')->assertOk()->assertDontSee('<iframe', false)->assertDontSee('https://evil.test');
    }
    public function test_social_crud_visibility_sorting_and_seeder_idempotence()
    {
        $this->seed(SocialMediaSeeder::class);
        $this->seed(SocialMediaSeeder::class);
        $this->assertSame(4, SocialMedia::count());
        $this->actingAs($this->user());
        $this->get('/admin/media-sosial')->assertOk();
        $this->get('/admin/media-sosial/create')->assertOk();
        $data = ['platform'=>'instagram','url'=>'https://instagram.com/miru_test','is_active'=>1,'sort_order'=>10];
        $this->post('/admin/media-sosial', $data)->assertRedirect(route('admin.social-media.index'))->assertSessionHasNoErrors();
        $item = SocialMedia::where('url',$data['url'])->firstOrFail();
        $this->get('/admin/media-sosial/'.$item->id.'/edit')->assertOk();
        SocialMedia::create(['platform'=>'youtube','url'=>'https://youtube.com/@miru_first','is_active'=>true,'sort_order'=>1]);
        foreach (['/', '/kontak'] as $url) {
            $response = $this->get($url)->assertOk()->assertSee($data['url']);
            $this->assertLessThan(strpos($response->getContent(), $data['url']), strpos($response->getContent(), 'https://youtube.com/@miru_first'));
        }
        $data['url'] = 'https://instagram.com/miru_updated';
        $this->put('/admin/media-sosial/'.$item->id, $data)->assertSessionHasNoErrors();
        $this->get('/')->assertSee($data['url'])->assertDontSee('https://instagram.com/miru_test');
        $data['is_active'] = 0;
        $this->patch('/admin/media-sosial/'.$item->id, $data)->assertSessionHasNoErrors();
        foreach (['/', '/kontak'] as $url) $this->get($url)->assertDontSee($data['url']);
        $this->delete('/admin/media-sosial/'.$item->id)->assertRedirect(route('admin.social-media.index'));
        $this->assertNull($item->fresh());
    }
    public function test_social_validation_and_empty_or_unsafe_urls_are_hidden()
    {
        $this->actingAs($this->user());
        $this->post('/admin/media-sosial', ['platform'=>'unknown','url'=>'javascript:alert(1)','is_active'=>'invalid','sort_order'=>-1])
            ->assertSessionHasErrors(['platform','url','is_active','sort_order']);
        SocialMedia::create(['platform'=>'facebook','url'=>null,'is_active'=>true,'sort_order'=>0]);
        SocialMedia::create(['platform'=>'instagram','url'=>'javascript:alert(1)','is_active'=>true,'sort_order'=>0]);
        $this->get('/')->assertOk()->assertDontSee('aria-label="Facebook"', false)->assertDontSee('javascript:alert(1)', false);
    }
    public function test_only_admin_and_super_admin_have_access()
    {
        $this->get('/admin/kontak')->assertRedirect('/login');
        $this->get('/admin/media-sosial')->assertRedirect('/login');
        $item = SocialMedia::create(['platform'=>'tiktok','url'=>null,'is_active'=>true,'sort_order'=>0]);
        $this->actingAs($this->user('pendaftar'));
        foreach (['/admin/kontak','/admin/media-sosial','/admin/media-sosial/create','/admin/media-sosial/'.$item->id.'/edit'] as $url) $this->get($url)->assertForbidden();
        $this->put('/admin/kontak', $this->contactData())->assertForbidden();
        $this->post('/admin/media-sosial', [])->assertForbidden();
        $this->patch('/admin/media-sosial/'.$item->id, [])->assertForbidden();
        $this->delete('/admin/media-sosial/'.$item->id)->assertForbidden();
        $this->actingAs($this->user('super_admin'))->get('/admin/kontak')->assertOk()->assertSee('aria-current="page"',false);
        $this->get('/admin/media-sosial')->assertOk()->assertSee('aria-current="page"',false);
    }
}