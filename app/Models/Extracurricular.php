<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Extracurricular extends Model {
    protected $fillable=['name','coach_name','schedule','description','image','is_active'];
    protected $casts=['is_active'=>'boolean'];
}
