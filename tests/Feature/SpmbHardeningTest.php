<?php
namespace Tests\Feature;

use App\Models\{User,StudentApplication,AdmissionPeriod,AdmissionWave};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Storage,Password,Hash,Notification};
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class SpmbHardeningTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        $this->artisan('migrate',['--force'=>true])->assertExitCode(0);
        Storage::fake('public'); Storage::fake('spmb');
    }
    public function test_documents_are_private_and_only_owner_or_active_admin_can_open_them() {
        $owner=User::factory()->create(['role'=>'pendaftar']);
        $other=User::factory()->create(['role'=>'pendaftar']);
        $admin=User::factory()->create(['role'=>'admin','is_active'=>true]);
        $this->actingAs($owner)->post('/dashboard/dokumen/family_card',['file'=>UploadedFile::fake()->image('kk.png')])->assertRedirect()->assertSessionHasNoErrors();
        $doc=StudentApplication::where('user_id',$owner->id)->firstOrFail()->documents()->firstOrFail();
        Storage::disk('spmb')->assertExists($doc->file_path);
        Storage::disk('public')->assertMissing($doc->file_path);
        $this->get(route('applicant.documents.show',$doc))->assertOk()->assertHeader('X-Content-Type-Options','nosniff');
        $this->actingAs($other)->get(route('applicant.documents.show',$doc))->assertForbidden();
        $this->get(route('admin.documents.show',$doc))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.documents.show',$doc))->assertOk();
        $admin->update(['is_active'=>false]);
        $this->get(route('admin.documents.show',$doc))->assertForbidden();
        auth()->logout();
        $this->get(route('applicant.documents.show',$doc))->assertRedirect(route('login'));
    }
    public function test_invalid_document_type_is_rejected_and_locked_application_cannot_upload() {
        $user=User::factory()->create(['role'=>'pendaftar']);
        $this->actingAs($user)->post('/dashboard/dokumen/unknown',['file'=>UploadedFile::fake()->image('x.png')])->assertNotFound();
        StudentApplication::create(['user_id'=>$user->id,'status'=>'submitted']);
        $this->post('/dashboard/dokumen/family_card',['file'=>UploadedFile::fake()->image('x.png')])->assertForbidden();
        $this->assertCount(0,Storage::disk('spmb')->allFiles());
    }
    public function test_legacy_files_are_copied_verified_and_removed_from_public_storage_idempotently() {
        Storage::disk('public')->put('spmb/1/kk.pdf','private content');
        $this->artisan('spmb:privatize-documents')->assertExitCode(0);
        $this->assertSame('private content',Storage::disk('spmb')->get('spmb/1/kk.pdf'));
        Storage::disk('public')->assertMissing('spmb/1/kk.pdf');
        $this->artisan('spmb:privatize-documents')->assertExitCode(0);
        Storage::disk('public')->put('spmb/1/kk.pdf','different content');
        $this->artisan('spmb:privatize-documents')->assertExitCode(1);
        Storage::disk('public')->assertExists('spmb/1/kk.pdf');
        $this->assertSame('private content',Storage::disk('spmb')->get('spmb/1/kk.pdf'));
    }
    public function test_revision_preserves_identity_even_when_original_period_is_closed() {
        $user=User::factory()->create(['role'=>'pendaftar']);
        $period=AdmissionPeriod::create(['name'=>'Lama','academic_year'=>'2026/2027','start_date'=>today()->subDays(20),'end_date'=>today()->subDays(10),'is_active'=>false]);
        $wave=AdmissionWave::create(['admission_period_id'=>$period->id,'name'=>'Lama','start_date'=>$period->start_date,'end_date'=>$period->end_date,'is_active'=>false]);
        $a=StudentApplication::create(['user_id'=>$user->id,'registration_number'=>'SPMB-2026-0007','admission_period_id'=>$period->id,'admission_wave_id'=>$wave->id,'status'=>'revision','submitted_at'=>now()->subDays(12)]);
        $a->studentProfile()->create(['full_name'=>'Siswa']);
        $a->parentProfile()->create(['father_name'=>'Ayah','mother_name'=>'Ibu']);
        foreach(['student_photo','family_card','birth_certificate'] as $type) $a->documents()->create(['document_type'=>$type,'file_path'=>'spmb/1/'.$type.'.png']);
        $before=$a->only(['registration_number','admission_period_id','admission_wave_id','submitted_at']);
        $this->actingAs($user)->post('/dashboard/submit')->assertRedirect(route('applicant.status'));
        $this->assertEquals($before,$a->fresh()->only(array_keys($before)));
        $this->assertSame('submitted',$a->fresh()->status);
        $this->post('/dashboard/submit')->assertForbidden();
        $this->assertSame(1,$a->statusHistories()->count());
    }
    public function test_password_reset_email_token_confirmation_and_single_use() {
        Notification::fake();
        $user=User::factory()->create(['role'=>'pendaftar']);
        $this->post('/forgot-password',['email'=>$user->email])->assertSessionHasNoErrors();
        Notification::assertSentTo($user,ResetPassword::class);
        $token=Password::createToken($user);
        $this->get(route('password.reset',['token'=>$token,'email'=>$user->email]))->assertOk();
        $data=['email'=>$user->email,'token'=>$token,'password'=>'new-password123','password_confirmation'=>'wrong-password'];
        $this->post('/reset-password',$data)->assertSessionHasErrors('password');
        $data['password_confirmation']=$data['password'];
        $this->post('/reset-password',$data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('new-password123',$user->fresh()->password));
        $this->post('/reset-password',$data)->assertSessionHasErrors('email');
        $this->post('/reset-password',array_merge($data,['token'=>'invalid']))->assertSessionHasErrors('email');
        $this->post('/login',['email'=>$user->email,'password'=>'new-password123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
