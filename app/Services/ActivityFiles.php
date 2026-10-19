<?php
namespace App\Services;
use App\Models\Activity;
use App\Models\ActivityImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ActivityFiles
{
    public static function store($file, string $directory): string {
        $path = $file->store($directory,'public');
        if (!$path) throw ValidationException::withMessages(['thumbnail'=>'Gambar gagal disimpan. Silakan coba kembali.']);
        return $path;
    }
    public static function deleteUnused(?string $path): void {
        if ($path && str_starts_with($path,'activities/') && !str_contains($path,'..')
            && !Activity::where('thumbnail',$path)->exists() && !ActivityImage::where('image',$path)->exists()) {
            Storage::disk('public')->delete($path);
        }
    }
}