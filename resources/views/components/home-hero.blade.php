@props(['hero'=>null,'school'=>null,'admissionPeriod'=>null,'admissionWave'=>null])
@if(!$hero || $hero->is_active)
@php
    $desktop=$hero?->desktop_banner_url;
    $mobile=$hero?->mobile_banner_url;
@endphp
<section class="combined-hero {{ ($desktop || $mobile) ? 'has-banner' : '' }} relative isolate overflow-hidden bg-primary-dark" aria-label="Informasi utama madrasah">
    @if($desktop || $mobile)
    <picture class="absolute inset-0 -z-20 block h-full w-full">
        @if($mobile)<source media="(max-width: 767px)" srcset="{{ $mobile }}">@endif
        <img src="{{ $desktop ?: $mobile }}" alt="" fetchpriority="high" class="h-full w-full object-cover object-center">
    </picture>
    @endif
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-black/65 via-black/10 to-transparent md:bg-gradient-to-l md:from-black/40 md:via-transparent md:to-transparent"></div>
    <div class="combined-hero-inner mx-auto flex w-full max-w-7xl items-end px-5 pb-10 {{ ($desktop || $mobile) ? 'pt-72' : 'pt-10' }} md:items-center md:px-8 md:py-12">
        <div class="w-full min-w-0 md:ml-auto md:w-3/5 lg:w-1/2 md:pl-8 lg:pl-10">
            @if(!$hero || $hero->small_text || $hero->academic_year)
            <span class="inline-block max-w-full break-words rounded-full bg-accent px-4 py-2 text-xs font-extrabold tracking-widest text-slate-900">{{ $hero ? $hero->small_text : 'SPMB' }} {{ $hero ? $hero->academic_year : $admissionPeriod?->academic_year }}</span>
            @endif
            <h1 class="mt-5 break-words text-3xl font-black leading-tight text-white sm:text-4xl lg:text-5xl xl:text-6xl">{{ $hero?->title ?: 'Sistem Penerimaan Murid Baru' }}</h1>
            @if(!$hero || $hero->subtitle)
            <p class="mt-5 whitespace-pre-line break-words text-base leading-7 text-white/95 sm:text-lg sm:leading-8">{{ $hero ? $hero->subtitle : 'Informasi pendaftaran '.($school?->name ?: 'madrasah').' dan layanan calon murid.' }}</p>
            @endif
            <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @if(!$hero || ($hero->primary_button_label && $hero->primary_button_url))<x-button :href="$hero ? $hero->primary_button_url : route('register')">{{ $hero ? $hero->primary_button_label : 'DAFTAR SEKARANG' }}</x-button>@endif
                @if(!$hero || ($hero->secondary_button_label && $hero->secondary_button_url))<x-button variant="outline" :href="$hero ? $hero->secondary_button_url : '#informasi-spmb'">{{ $hero ? $hero->secondary_button_label : 'INFORMASI SPMB' }}</x-button>@endif
            </div>
            <div id="informasi-spmb" class="mt-8 grid gap-4 border-t border-white/25 pt-5 sm:grid-cols-2">
                <p class="text-sm text-white/90"><b class="block text-white">{{ $admissionWave ? 'Pendaftaran Dibuka' : 'Informasi Pendaftaran' }}</b>{{ $admissionPeriod?->academic_year ?: 'Jadwal akan diumumkan' }}</p>
                <p class="text-sm text-white/90"><b class="block text-white">Proses Mudah</b>Daftar dan pantau dari satu tempat</p>
            </div>
        </div>
    </div>
</section>
@endif
