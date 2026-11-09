<?php
namespace App\Support;

use Illuminate\Support\Facades\Storage;

class SchoolContent
{
    public static function image(?string $path, string $kind='school'): string
    {
        $fallbacks=['teacher'=>'teacher','headmaster'=>'teacher','news'=>'news','activity'=>'activity','gallery'=>'gallery','school'=>'school'];
        if ($path && !str_contains($path,'..')) {
            if (str_starts_with($path,'images/') && is_file(public_path($path))) return asset($path);
            if (!str_starts_with($path,'images/') && Storage::disk('public')->exists($path)) return url(Storage::disk('public')->url($path));
        }
        return asset('images/fallback-'.($fallbacks[$kind] ?? 'school').'.svg');
    }

    public static function text(?string $value): ?string
    {
        if (!$value || preg_match('/konten berikut merupakan data demo|konten dummy|pengumuman demo|lorem ipsum/i',$value)) return null;
        return $value;
    }

    public static function profile($profile)
    {
        if (!$profile) return null;
        $public=clone $profile;
        foreach (['phone','whatsapp'] as $field) {
            $digits=preg_replace('/\D/','',$public->$field ?? '');
            if (in_array($digits,['0335000000','081200000000','6281200000000','08123456789'],true)) $public->$field=null;
        }
        foreach (['email','website'] as $field) {
            $value=$public->$field;
            $host=$field==='email' ? substr(strrchr($value ?? '','@') ?: '',1) : parse_url($value ?? '',PHP_URL_HOST);
            if ($host && (preg_match('/\.(test|invalid|example)$/i',$host) || in_array(strtolower($host),['example.com','example.org','example.net'],true))) $public->$field=null;
            if ($value==='fake@email') $public->$field=null;
        }
        foreach (['history','about','headmaster_message','vision','mission','goals','education_commitment'] as $field) $public->$field=self::text($public->$field);
        return $public;
    }

    public static function visible($query, array $fields=['title','content'])
    {
        foreach ($fields as $field) {
            $query->where(function($q) use ($field) {
                $q->whereNull($field)->orWhere(function($q)use($field) {
                    foreach (['konten dummy','pengumuman demo','lorem ipsum'] as $marker) $q->where($field,'not like','%'.$marker.'%');
                    if ($field==='title') {
                        foreach (['test','dummy','example','placeholder'] as $title) {
                            $q->whereRaw('LOWER(title) != ?',[$title])->whereRaw('LOWER(title) NOT LIKE ?',[$title.' %']);
                        }
                    }
                });
            });
        }
        return $query;
    }
}
