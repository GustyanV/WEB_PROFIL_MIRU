<header class="bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
            <img src="{{ $siteProfile?->site_logo_url ?? asset('images/logo-miru.png') }}" alt="Logo {{ $siteProfile?->name ?: 'Madrasah' }}" class="h-11 w-11 shrink-0 md:h-14 md:w-14 rounded-xl object-contain">
            <span class="min-w-0">
                <span class="block text-[11px] font-bold uppercase tracking-[0.18em] text-primary">{{ $siteProfile?->tagline ?? 'Madrasah Ibtidaiyah' }}</span>
                <span class="block max-w-[190px] break-words text-lg font-extrabold text-ink sm:max-w-xs sm:text-xl">{{ $siteProfile?->name ?? 'MI Raudlatul Ulum' }}</span>
            </span>
        </a>
        <div class="flex shrink-0 items-center gap-3 text-sm text-slate-600 sm:gap-6">
            @if($publicContact?->address)
                <div class="hidden items-center gap-2 xl:flex">
                    <span aria-hidden="true" class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-teal-50 text-xs font-bold text-primary">Lok</span>
                    <span class="min-w-0"><small class="block text-slate-400">Alamat</small><span class="block max-w-xs truncate" title="{{ $publicContact->address }}">{{ $publicContact->address }}</span></span>
                </div>
            @endif
            @if($publicContact?->whatsapp)
                <div class="hidden items-center gap-2 md:flex">
                    <span aria-hidden="true" class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-teal-50 text-xs font-bold text-primary">WA</span>
                    <span class="min-w-0"><small class="block text-slate-400">WhatsApp</small>
                    @if($publicWhatsappUrl)<a href="{{ $publicWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="whitespace-nowrap hover:text-primary">{{ $publicContact->whatsapp }}</a>
                    @else<span>{{ $publicContact->whatsapp }}</span>@endif</span>
                </div>
            @endif
            <div class="hidden items-center gap-2 lg:flex">
                @foreach($publicSocialMedia as $social)
                    <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ \App\Models\SocialMedia::PLATFORMS[$social->platform] }}" title="{{ \App\Models\SocialMedia::PLATFORMS[$social->platform] }}" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-bold text-primary transition hover:bg-teal-100">
                        <span aria-hidden="true">{{ ['instagram'=>'ig', 'facebook'=>'f', 'youtube'=>'▶', 'tiktok'=>'♪'][$social->platform] }}</span>
                    </a>
                @endforeach
                @if($publicContact?->email)
                    <a href="mailto:{{ $publicContact->email }}" aria-label="Email madrasah" title="{{ $publicContact->email }}" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-sm font-bold text-primary transition hover:bg-teal-100">@</a>
                @endif
            </div>
        </div>
    </div>
</header>