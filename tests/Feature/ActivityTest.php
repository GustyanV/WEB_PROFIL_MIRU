<?php
namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityImage;
use App\Models\User;
use Database\Seeders\ActivitySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        $this->artisan('migrate',['--force'=>true])->assertExitCode(0);
        Storage::fake('public');
    }
    private function login($role='admin') {
        $user=User::create(['name'=>'Activity Tester','email'=>$role.'@activity.test','password'=>bcrypt('test-password'),'role'=>$role]);
        $this->actingAs($user); return $user;
    }
    private function data(array $overrides=[]): array {
        return array_merge(['title'=>'Kegiatan Pengujian Siswa','slug'=>'','category'=>'Sosial','activity_date'=>'2026-09-01',
            'excerpt'=>'Ringkasan kegiatan siswa','content'=>"Isi kegiatan\nBaris kedua",'status'=>'draft','published_at'=>null],$overrides);
    }
    private function activity(array $overrides=[]): Activity {
        return Activity::create($this->data(array_merge(['slug'=>'kegiatan-pengujian-siswa'],$overrides)));
    }
    public function test_admin_creates_draft_publishes_edits_and_deletes_activity() {
        $this->login();
        $this->get('/admin/kegiatan')->assertOk()->assertSee('Tambah Kegiatan');
        $this->get('/admin/kegiatan/create')->assertOk();
        $this->post('/admin/kegiatan',$this->data())->assertSessionHasNoErrors()->assertRedirect();
        $activity=Activity::firstOrFail();
        $this->assertSame('kegiatan-pengujian-siswa',$activity->slug);
        $this->get('/admin/kegiatan/'.$activity->id.'/edit')->assertOk()->assertSee('GALERI KEGIATAN');
        $this->get('/kegiatan')->assertDontSee($activity->title);
        $this->get('/')->assertDontSee($activity->title);
        $this->get('/kegiatan/'.$activity->slug)->assertNotFound();
        $published=$this->data(['status'=>'published','slug'=>$activity->slug]);
        $this->put('/admin/kegiatan/'.$activity->id,$published)->assertSessionHasNoErrors();
        $this->get('/kegiatan')->assertOk()->assertSee($activity->title);
        $this->get('/')->assertOk()->assertSee($activity->title);
        $this->get('/kegiatan/'.$activity->slug)->assertOk()->assertSee('Isi kegiatan');
        $this->patch('/admin/kegiatan/'.$activity->id,array_merge($published,['title'=>'Judul setelah diedit']))->assertSessionHasNoErrors();
        $this->get('/kegiatan/'.$activity->slug)->assertSee('Judul setelah diedit');
        $this->delete('/admin/kegiatan/'.$activity->id)->assertRedirect(route('admin.activities.index'));
        $this->assertNull($activity->fresh());
    }
    public function test_scheduled_publication_and_unpublish_are_enforced() {
        $activity=$this->activity(['status'=>'published','published_at'=>now()->addDay()]);
        foreach (['/','/kegiatan'] as $url) $this->get($url)->assertOk()->assertDontSee($activity->title);
        $this->get('/kegiatan/'.$activity->slug)->assertNotFound();
        $this->travel(2)->days();
        $this->get('/kegiatan/'.$activity->slug)->assertOk();
        $this->login();
        $this->put('/admin/kegiatan/'.$activity->id,$this->data(['status'=>'draft']))->assertSessionHasNoErrors();
        $this->get('/kegiatan/'.$activity->slug)->assertNotFound();
    }
    public function test_gallery_upload_caption_cover_order_and_file_cleanup() {
        $this->login(); $activity=$this->activity(['status'=>'published']);
        $this->post('/admin/kegiatan/'.$activity->id.'/images',['images'=>[
            UploadedFile::fake()->image('foto.png'),UploadedFile::fake()->image('foto.png')
        ]])->assertSessionHasNoErrors();
        $images=$activity->images()->get();
        $this->assertCount(2,$images);
        $this->assertNotSame($images[0]->image,$images[1]->image);
        foreach ($images as $image) {
            Storage::disk('public')->assertExists($image->image);
            $this->assertStringStartsWith('activities/gallery/',$image->image);
            $this->patch('/admin/kegiatan/'.$activity->id.'/images/'.$image->id,[
                'caption'=>'Caption foto '.$image->id,'sort_order'=>5,'is_cover'=>1
            ])->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->assertSame(1,$activity->images()->where('is_cover',true)->count());
        $this->assertTrue($images[1]->fresh()->is_cover);
        $this->assertSame(asset('storage/'.$images[1]->image),$activity->fresh()->image_url);
        $this->get('/kegiatan/'.$activity->slug)->assertOk()->assertSee('Caption foto '.$images[1]->id)->assertSee('x-data',false);
        $this->delete('/admin/kegiatan/'.$activity->id.'/images/'.$images[0]->id)->assertRedirect();
        Storage::disk('public')->assertMissing($images[0]->image);
        $this->assertNull($images[0]->fresh());
        $this->delete('/admin/kegiatan/'.$activity->id);
        $this->assertSame(0,ActivityImage::count());
        Storage::disk('public')->assertMissing($images[1]->image);
    }
    public function test_thumbnail_replacement_and_shared_file_safety() {
        $this->login();
        $this->post('/admin/kegiatan',$this->data(['thumbnail'=>UploadedFile::fake()->image('thumb.jpg')]))->assertSessionHasNoErrors();
        $activity=Activity::firstOrFail(); $old=$activity->thumbnail;
        Storage::disk('public')->assertExists($old);
        $this->put('/admin/kegiatan/'.$activity->id,$this->data(['thumbnail'=>UploadedFile::fake()->image('new.webp')]))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($old);
        $new=$activity->fresh()->thumbnail;
        $shared=$this->activity(['title'=>'Pemakai thumbnail bersama','slug'=>'shared','thumbnail'=>$new]);
        $this->delete('/admin/kegiatan/'.$activity->id);
        Storage::disk('public')->assertExists($new);
        $this->delete('/admin/kegiatan/'.$shared->id);
        Storage::disk('public')->assertMissing($new);
    }
    public function test_upload_slug_and_required_field_validation() {
        $this->login(); $activity=$this->activity();
        $this->post('/admin/kegiatan',$this->data())->assertSessionHasErrors('slug');
        $this->post('/admin/kegiatan',[])->assertSessionHasErrors(['title','category','activity_date','content','status']);
        $this->post('/admin/kegiatan',$this->data(['slug'=>'different','category'=>'Invalid','status'=>'invalid','thumbnail'=>UploadedFile::fake()->create('shell.php',5,'text/plain')]))->assertSessionHasErrors(['category','status','thumbnail']);
        $this->post('/admin/kegiatan',$this->data(['slug'=>'oversized','thumbnail'=>UploadedFile::fake()->image('large.png')->size(2049)]))->assertSessionHasErrors('thumbnail');
        $this->post('/admin/kegiatan/'.$activity->id.'/images',['images'=>[UploadedFile::fake()->create('shell.php',1,'text/plain')]])->assertSessionHasErrors('images.0');
        $this->post('/admin/kegiatan/'.$activity->id.'/images',['images'=>[UploadedFile::fake()->image('large.jpg')->size(2049)]])->assertSessionHasErrors('images.0');
        $this->assertSame(0,ActivityImage::count());
    }
    public function test_authorization_and_gallery_parent_binding() {
        $activity=$this->activity(); $other=$this->activity(['slug'=>'other']);
        $image=$other->images()->create(['image'=>'activities/gallery/example.png','sort_order'=>0]);
        $this->get('/admin/kegiatan')->assertRedirect('/login');
        $this->login('pendaftar');
        foreach (['/admin/kegiatan','/admin/kegiatan/create','/admin/kegiatan/'.$activity->id.'/edit'] as $url) $this->get($url)->assertForbidden();
        $this->post('/admin/kegiatan',$this->data())->assertForbidden();
        $this->put('/admin/kegiatan/'.$activity->id,$this->data())->assertForbidden();
        $this->delete('/admin/kegiatan/'.$activity->id)->assertForbidden();
        $this->post('/admin/kegiatan/'.$activity->id.'/images',[])->assertForbidden();
        $this->patch('/admin/kegiatan/'.$activity->id.'/images/'.$image->id,[])->assertForbidden();
        $this->delete('/admin/kegiatan/'.$activity->id.'/images/'.$image->id)->assertForbidden();
        $this->login('super_admin');
        $this->get('/admin/kegiatan')->assertOk();
        $this->patch('/admin/kegiatan/'.$activity->id.'/images/'.$image->id,['caption'=>'wrong parent','sort_order'=>0,'is_cover'=>1])->assertNotFound();
        $this->delete('/admin/kegiatan/'.$activity->id.'/images/'.$image->id)->assertNotFound();
        $this->assertNotNull($image->fresh());
    }
    public function test_search_filters_home_limit_and_public_sorting() {
        $this->login();
        for ($i=1;$i<=12;$i++) $this->activity(['slug'=>'item-'.$i,'title'=>'Activity item '.$i,'activity_date'=>'2026-09-'.str_pad($i,2,'0',STR_PAD_LEFT),'status'=>'published','category'=>$i===1?'Pramuka':'Sosial']);
        $this->get('/admin/kegiatan?search=Activity+item+12')->assertOk()->assertSee('Activity item 12')->assertDontSee('Activity item 11');
        $this->get('/admin/kegiatan?category=Pramuka&status=published')->assertSee('Activity item 1')->assertDontSee('Activity item 12');
        $this->get('/admin/kegiatan?status=draft')->assertSee('Belum ada kegiatan yang sesuai.');
        $this->get('/')->assertSee('Activity item 12')->assertSee('Activity item 10')->assertDontSee('Activity item 9');
        $this->get('/kegiatan')->assertSee('Activity item 12')->assertDontSee('Activity item 3')->assertSee('page=2',false);
        $this->get('/kegiatan-siswa')->assertRedirect('/kegiatan');
    }
    public function test_content_is_escaped_and_default_image_exists() {
        $activity=$this->activity(['status'=>'published','content'=>'<script>alert("xss")</script>']);
        $this->get('/kegiatan/'.$activity->slug)->assertOk()->assertDontSee('<script>alert("xss")</script>',false)->assertSee('&lt;script&gt;',false)->assertSee('images/fallback-activity.svg');
        $this->assertFileExists(public_path('images/fallback-activity.svg'));
    }
    public function test_demo_seeder_does_not_duplicate_or_overwrite_admin_changes() {
        $this->seed(ActivitySeeder::class);
        $this->assertSame(5,Activity::count());
        $activity=Activity::first(); $activity->update(['title'=>'Judul diubah admin']);
        $this->seed(ActivitySeeder::class);
        $this->assertSame(5,Activity::count());
        $this->assertSame('Judul diubah admin',$activity->fresh()->title);
    }
}