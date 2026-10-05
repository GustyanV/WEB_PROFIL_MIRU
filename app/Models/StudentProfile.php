<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class StudentProfile extends Model { protected $fillable=['student_application_id','nik','nisn','full_name','birth_place','birth_date','gender','religion','address','province','regency','district','village','postal_code','previous_school']; protected $casts=['birth_date'=>'date']; public function application(){return $this->belongsTo(StudentApplication::class,'student_application_id');} }
