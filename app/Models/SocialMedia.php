<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    public const PLATFORMS = ['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'];
    protected $table = 'social_media';
    protected $fillable = ['platform', 'url', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNotNull('url')->where('url', '!=', '')
            ->orderBy('sort_order')->orderBy('id');
    }
}