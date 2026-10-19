@if($publicSocialMedia->isNotEmpty())
<div class="flex flex-wrap gap-2" aria-label="Media sosial">
@foreach($publicSocialMedia as $social)
<a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ \App\Models\SocialMedia::PLATFORMS[$social->platform] ?? $social->platform }}" class="inline-flex items-center gap-1 rounded-lg bg-teal-50 px-2 py-2 text-xs font-bold text-primary">
<span aria-hidden="true">{{ ['instagram'=>'ig','facebook'=>'f','youtube'=>'▶','tiktok'=>'♪'][$social->platform] ?? '' }}</span>
{{ \App\Models\SocialMedia::PLATFORMS[$social->platform] ?? $social->platform }}</a>
@endforeach
</div>
@endif