<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgramImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Storage::fake('public');
    }

    public function test_uploaded_program_image_appears_on_homepage_and_program_listing(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $program = Program::create(['title' => 'Program Foto Uji', 'description' => 'Deskripsi program uji', 'is_active' => true]);

        $this->actingAs($admin)->put('/admin/program/'.$program->id, [
            'title' => $program->title,
            'description' => $program->description,
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('program.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $image = $program->fresh()->image;
        $this->assertNotEmpty($image);
        Storage::disk('public')->assertExists($image);
        $this->get('/')->assertOk()->assertSee('src="'.asset('storage/'.$image).'"', false);
        $this->get('/program-unggulan')->assertOk()->assertSee('src="'.asset('storage/'.$image).'"', false);
    }

    public function test_missing_program_image_uses_fallback_and_inactive_program_is_hidden(): void
    {
        Program::create(['title' => 'Program Tanpa Foto', 'description' => 'Deskripsi uji', 'image' => 'programs/missing.jpg', 'is_active' => true]);
        Program::create(['title' => 'Program Nonaktif Uji', 'description' => 'Deskripsi uji', 'is_active' => false]);

        foreach (['/', '/program-unggulan'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Program Tanpa Foto')
                ->assertSee('src="'.asset('images/fallback-school.svg').'"', false)
                ->assertDontSee('storage/programs/missing.jpg')
                ->assertDontSee('Program Nonaktif Uji');
        }
    }
}