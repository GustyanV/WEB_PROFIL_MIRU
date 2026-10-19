<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $t) {
            $t->id(); $t->string('title'); $t->string('slug')->unique(); $t->string('category');
            $t->date('activity_date'); $t->text('excerpt')->nullable(); $t->longText('content');
            $t->string('thumbnail')->nullable(); $t->string('status')->default('draft');
            $t->timestamp('published_at')->nullable(); $t->timestamps();
        });
        Schema::create('activity_images', function (Blueprint $t) {
            $t->id(); $t->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $t->string('image'); $t->string('caption')->nullable();
            $t->boolean('is_cover')->default(false); $t->integer('sort_order')->default(0); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('activity_images'); Schema::dropIfExists('activities'); }
};