<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class GoogleMapsEmbedUrl implements Rule
{
    public static function isSafe(?string $value): bool
    {
        if (!$value || !filter_var($value, FILTER_VALIDATE_URL)) return false;
        $parts = parse_url($value);
        return ($parts['scheme'] ?? '') === 'https'
            && in_array(strtolower($parts['host'] ?? ''), ['www.google.com', 'google.com', 'maps.google.com'], true)
            && preg_match('~^/maps/embed(?:/|$)~', $parts['path'] ?? '')
            && !isset($parts['user']) && !isset($parts['pass'])
            && (!isset($parts['port']) || $parts['port'] === 443);
    }
    public function passes($attribute, $value) { return self::isSafe($value); }
    public function message() { return 'Gunakan URL embed Google Maps HTTPS (https://www.google.com/maps/embed?...), bukan kode iframe.'; }
}