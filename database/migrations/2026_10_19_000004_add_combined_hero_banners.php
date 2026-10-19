<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('hero_banners',function(Blueprint $table) {
            $table->string('banner_desktop')->nullable();
            $table->string('banner_mobile')->nullable();
        });
    }
    public function down(): void { Schema::table('hero_banners',fn(Blueprint $table)=>$table->dropColumn(['banner_desktop','banner_mobile'])); }
};
