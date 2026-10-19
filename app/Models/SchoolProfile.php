<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class SchoolProfile extends Model {
    protected $fillable=['name','nsm','npsn','service_hours','google_maps_embed_url','address','phone','whatsapp','email','website','logo','favicon','about','history','vision','mission','goals','education_commitment','headmaster_name','headmaster_message','headmaster_photo','tagline','footer_text','copyright_text','primary_color','accent_color'];
    private function siteAsset(?string $path): string {
        if ($path && !str_contains($path,'..')) {
            if (str_starts_with($path,'images/') && is_file(public_path($path))) return asset($path);
            if (Storage::disk('public')->exists($path)) return asset('storage/'.$path);
        }
        return asset('images/logo-miru.png');
    }
    public function getSiteLogoUrlAttribute(): string { return $this->siteAsset($this->logo); }
    public function getSiteFaviconUrlAttribute(): string { return $this->siteAsset($this->favicon); }
}