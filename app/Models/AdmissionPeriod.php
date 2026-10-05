<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class AdmissionPeriod extends Model { protected $fillable=['name','academic_year','start_date','end_date','quota','is_active']; protected $casts=['start_date'=>'date','end_date'=>'date','is_active'=>'boolean']; public function waves(){return $this->hasMany(AdmissionWave::class);} public function applications(){return $this->hasMany(StudentApplication::class);} }
