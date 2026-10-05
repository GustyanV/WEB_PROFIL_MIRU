<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class SchoolProfile extends Model { protected $fillable=['name','nsm','npsn','address','phone','whatsapp','email','website','logo','favicon','history','vision','mission','headmaster_name','headmaster_message','headmaster_photo']; }
