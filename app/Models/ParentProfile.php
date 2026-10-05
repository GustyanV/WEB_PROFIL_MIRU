<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class ParentProfile extends Model { protected $fillable=['student_application_id','father_name','father_nik','father_education','father_occupation','father_income','father_phone','mother_name','mother_nik','mother_education','mother_occupation','mother_income','mother_phone','guardian_name','guardian_relationship','guardian_occupation','guardian_phone']; public function application(){return $this->belongsTo(StudentApplication::class,'student_application_id');} }
