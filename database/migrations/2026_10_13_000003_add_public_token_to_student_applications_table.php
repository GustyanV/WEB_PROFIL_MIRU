<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void { Schema::table('student_applications',fn(Blueprint $t)=>$t->uuid('public_token')->nullable()->unique()->after('registration_number')); } public function down():void { Schema::table('student_applications',fn(Blueprint $t)=>$t->dropUnique(['public_token'])->dropColumn('public_token')); } };
