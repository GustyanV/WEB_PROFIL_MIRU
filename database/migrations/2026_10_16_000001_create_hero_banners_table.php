<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasTable('hero_banners')) {
            Schema::create('hero_banners', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('subtitle')->nullable();
                foreach (['small_text', 'academic_year', 'image', 'background_image', 'primary_button_label', 'primary_button_url', 'secondary_button_label', 'secondary_button_url'] as $field) {
                    $table->string($field)->nullable();
                }
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('hero_banners');
    }
};