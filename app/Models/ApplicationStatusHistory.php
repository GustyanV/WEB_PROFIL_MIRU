<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class ApplicationStatusHistory extends Model { protected $fillable=['student_application_id','status','note','changed_by']; public function application(){return $this->belongsTo(StudentApplication::class,'student_application_id');} public function changedBy(){return $this->belongsTo(User::class,'changed_by');} }
