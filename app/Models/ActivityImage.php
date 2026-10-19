<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityImage extends Model
{
    protected $fillable = ['activity_id','image','caption','is_cover','sort_order'];
    protected $casts = ['activity_id'=>'integer','is_cover'=>'boolean','sort_order'=>'integer'];
    public function activity() { return $this->belongsTo(Activity::class); }
}