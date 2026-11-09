<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SpmbDocuments
{
    public const TYPES = ['student_photo', 'family_card', 'birth_certificate', 'father_id_card', 'mother_id_card', 'kindergarten_diploma', 'supporting_document'];

    public static function validPath(string $path): bool
    {
        return str_starts_with($path, 'spmb/') && !str_contains($path, '..') && !str_contains($path, '\\');
    }

    public static function disk()
    {
        return Storage::disk('spmb');
    }
}
