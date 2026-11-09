<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroBanner extends Model
{
    protected $fillable=[
        'title','subtitle','small_text','academic_year','image','background_image',
        'banner_desktop','banner_mobile','primary_button_label','primary_button_url',
        'secondary_button_label','secondary_button_url','is_active',
    ];
    protected $casts=['is_active'=>'boolean'];
    private function bannerUrl(?string $path): ?string {
        return $path && str_starts_with($path,'hero/') && !str_contains($path,'..') && Storage::disk('public')->exists($path) ? url(Storage::disk('public')->url($path)) : null;
    }
    public function getDesktopBannerUrlAttribute(): ?string { return $this->bannerUrl($this->banner_desktop); }
    public function getMobileBannerUrlAttribute(): ?string { return $this->bannerUrl($this->banner_mobile); }
}
