<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title','Dashboard Pendaftar') | {{ $siteProfile?->name ?: 'Madrasah' }}</title>
<x-site-branding />
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="screen-shell applicant-shell bg-canvas" x-data="responsiveMenu" @keydown.escape.window="close()">
@php
    $navigationApplication = $application ?? (($app ?? null) instanceof \App\Models\StudentApplication ? $app : null);
    $menu = [
        ['Dashboard', route('applicant.dashboard')],
        ['Data Calon Siswa', route('applicant.biodata')],
        ['Data Orang Tua', route('applicant.parents')],
        ['Dokumen', route('applicant.documents')],
        ['Review Pendaftaran', route('applicant.review')],
        ['Status Pendaftaran', route('applicant.status')],
        ['Cetak Bukti', $navigationApplication?->registration_number ? route('applicant.proof', $navigationApplication) : route('applicant.review')],
    ];
@endphp
<div class="min-h-screen lg:grid lg:grid-cols-[240px_minmax(0,1fr)]">
    <button x-show="open" x-cloak @click="close()" aria-label="Tutup menu pendaftar" class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"></button>
    <aside id="applicant-drawer" x-ref="panel" :class="open ? 'translate-x-0' : '-translate-x-full'"
        :inert="!open && !media?.matches" @keydown="trap($event)"
        class="sidebar-drawer fixed -translate-x-full inset-y-0 left-0 z-50 w-72 max-w-[calc(100%-2rem)] overflow-y-auto overscroll-contain bg-primary text-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:w-auto lg:max-w-none lg:!translate-x-0">
        <div class="flex items-center justify-between gap-3 p-5"><p class="font-black">PENDAFTAR</p><button @click="close()" aria-label="Tutup navigasi pendaftar" class="min-h-11 rounded-lg border border-white/30 px-3 text-sm lg:hidden">Tutup</button></div>
        <nav class="space-y-1 px-3 pb-5" aria-label="Navigasi pendaftar" @click="if ($event.target.closest('a')) close(false)">
            @foreach($menu as [$label, $url])<a href="{{ $url }}" @if(url()->current() === $url) aria-current="page" @endif class="flex min-h-11 items-center rounded-lg px-3 py-2 text-sm hover:bg-white/10 {{ url()->current() === $url ? 'bg-white/20 font-bold' : '' }}">{{ $label }}</a>@endforeach
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="mt-4 min-h-11 w-full rounded-lg border border-white/30 px-3 py-2 text-left text-sm">Logout</button></form>
        </nav>
    </aside>
    <div class="min-w-0">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b bg-white px-4 py-4 sm:px-6 lg:px-8">
            <button @click="toggle($event)" :aria-expanded="open" aria-controls="applicant-drawer" aria-label="Buka navigasi pendaftar" class="btn-secondary lg:hidden">☰ Menu Pendaftar</button>
            <div class="flex min-w-0 max-w-full items-center gap-3">
                <img src="{{ $siteProfile?->site_logo_url ?? asset('images/logo-miru.png') }}" alt="Logo {{ $siteProfile?->name ?: 'MIRU' }}" width="64" height="56" class="h-12 w-14 shrink-0 object-contain sm:h-14 sm:w-16">
                <span class="min-w-0 break-words text-sm font-bold text-primary">{{ $siteProfile?->name ?: 'Madrasah' }}</span>
            </div>
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center text-sm font-bold text-primary">Kembali ke beranda</a>
        </header>
        <main class="mx-auto w-full min-w-0 max-w-7xl p-4 sm:p-6 lg:p-8">@yield('content')</main>
    </div>
</div>
</body>
</html>
