<?php
namespace App\Providers;

use App\Models\SchoolProfile;
use App\Models\SocialMedia;
use App\Rules\GoogleMapsEmbedUrl;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register() {}

    public function boot()
    {
        View::composer('*', function ($view) {
            $request=request();
            if (!$request->attributes->has('site_profile_data')) $request->attributes->set('site_profile_data',['profile'=>\App\Support\SchoolContent::profile(SchoolProfile::orderBy('id')->first())]);
            $view->with('siteProfile',$request->attributes->get('site_profile_data')['profile']);
        });
        View::composer(['components.site-header', 'components.site-footer', 'components.contact-details', 'components.social-links', 'public.information'], function ($view) {
            $request = request();
            if (!$request->attributes->has('public_contact_data')) {
                $profile = \App\Support\SchoolContent::profile(SchoolProfile::orderBy('id')->first());
                $number = preg_replace('/\D/', '', $profile?->whatsapp ?? '');
                if (str_starts_with($number, '0')) $number = '62'.substr($number, 1);
                $social = Schema::hasTable('social_media') ? SocialMedia::active()->get()->filter(function ($item) {
                    return in_array($item->platform, array_keys(SocialMedia::PLATFORMS), true)
                        && filter_var($item->url, FILTER_VALIDATE_URL)
                        && in_array(strtolower(parse_url($item->url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true);
                }) : collect();
                $request->attributes->set('public_contact_data', [
                    'publicContact'=>$profile,
                    'publicWhatsappUrl'=>$number ? 'https://wa.me/'.$number : null,
                    'publicMapsUrl'=>GoogleMapsEmbedUrl::isSafe($profile?->google_maps_embed_url) ? $profile->google_maps_embed_url : null,
                    'publicSocialMedia'=>$social,
                ]);
            }
            $view->with($request->attributes->get('public_contact_data'));
        });
    }
}