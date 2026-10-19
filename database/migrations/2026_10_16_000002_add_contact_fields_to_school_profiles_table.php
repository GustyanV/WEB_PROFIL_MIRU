<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['service_hours', 'google_maps_embed_url'] as $field) {
            if (!Schema::hasColumn('school_profiles', $field)) {
                Schema::table('school_profiles', function (Blueprint $table) use ($field) {
                    $table->text($field)->nullable();
                });
            }
        }
    }
    public function down(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            $table->dropColumn(['service_hours', 'google_maps_embed_url']);
        });
    }
};