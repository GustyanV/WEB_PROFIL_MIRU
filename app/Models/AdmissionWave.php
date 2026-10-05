<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class AdmissionWave extends Model { protected $fillable=['admission_period_id','name','start_date','end_date','quota','is_active']; protected $casts=['start_date'=>'date','end_date'=>'date','is_active'=>'boolean']; public function period(){return $this->belongsTo(AdmissionPeriod::class,'admission_period_id');} public function applications(){return $this->hasMany(StudentApplication::class);} }
