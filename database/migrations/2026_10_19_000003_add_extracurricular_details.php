<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        foreach(['coach_name','schedule'] as $field) if(!Schema::hasColumn('extracurriculars',$field)) Schema::table('extracurriculars',fn(Blueprint $t)=>$t->string($field)->nullable());
    }
    public function down(): void { Schema::table('extracurriculars',fn(Blueprint $t)=>$t->dropColumn(['coach_name','schedule'])); }
};
