<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class ApplicationDocument extends Model { protected $fillable=['student_application_id','document_type','file_path','original_name','mime_type','file_size']; public function application(){return $this->belongsTo(StudentApplication::class,'student_application_id');} }
