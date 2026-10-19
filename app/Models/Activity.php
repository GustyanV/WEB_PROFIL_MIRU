<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public const CATEGORIES = ['Keagamaan','Akademik','Pramuka','Sosial','Olahraga','Peringatan Hari Besar','Lainnya'];
    protected $fillable = ['title','slug','category','activity_date','excerpt','content','thumbnail','status','published_at'];
    protected $casts = ['activity_date'=>'date','published_at'=>'datetime'];
    public function images() { return $this->hasMany(ActivityImage::class)->orderBy('sort_order')->orderBy('id'); }
    public function scopePublished($query) {
        return $query->where('status','published')->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at','<=',now());
        });
    }
    public function getImageUrlAttribute() {
        $path = $this->thumbnail ?: $this->images->firstWhere('is_cover',true)?->image;
        return \App\Support\SchoolContent::image($path,'activity');
    }
}