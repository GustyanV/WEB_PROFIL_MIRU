<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GalleryAlbum extends Model {
    protected $fillable=['title','slug','description','cover'];
    public function images(){return $this->hasMany(GalleryImage::class);}
    public function getImageUrlAttribute(): string {
        return \App\Support\SchoolContent::image($this->cover ?: $this->images->first()?->image,'gallery');
    }
}
