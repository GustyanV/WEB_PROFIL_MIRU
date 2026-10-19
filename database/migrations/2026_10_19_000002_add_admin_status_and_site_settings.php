<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users','is_active')) Schema::table('users',fn(Blueprint $t)=>$t->boolean('is_active')->default(true));
        foreach (['tagline','footer_text','copyright_text','primary_color','accent_color'] as $field) {
            if (!Schema::hasColumn('school_profiles',$field)) Schema::table('school_profiles',fn(Blueprint $t)=>$t->text($field)->nullable());
        }
    }
    public function down(): void {
        Schema::table('users',fn(Blueprint $t)=>$t->dropColumn('is_active'));
        Schema::table('school_profiles',fn(Blueprint $t)=>$t->dropColumn(['tagline','footer_text','copyright_text','primary_color','accent_color']));
    }
};