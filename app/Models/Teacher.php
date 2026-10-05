<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Teacher extends Model { protected $fillable=['name','nip','nuptk','position','subject','photo','bio','sort_order','is_active']; protected $casts=['is_active'=>'boolean','sort_order'=>'integer']; }
