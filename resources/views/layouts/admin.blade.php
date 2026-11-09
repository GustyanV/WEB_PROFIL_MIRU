<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title','Dashboard Admin') | {{ $siteProfile?->name ?? 'MI Raudlatul Ulum' }}</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<x-site-branding />
</head>
<body class="screen-shell admin-shell bg-canvas" x-data="responsiveMenu" @keydown.escape.window="close()">
<div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]">
    <button x-show="open" x-cloak @click="close()" aria-label="Tutup menu admin" class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"></button>
    <aside id="admin-drawer" x-ref="panel" :class="open ? 'translate-x-0' : '-translate-x-full'"
        :inert="!open && !media?.matches" @keydown="trap($event)"
        class="sidebar-drawer fixed -translate-x-full inset-y-0 left-0 z-50 w-72 max-w-[calc(100%-2rem)] overflow-y-auto overscroll-contain bg-primary-dark text-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:w-auto lg:max-w-none lg:!translate-x-0">
        <div class="flex items-center justify-between gap-3 p-5">
            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3"><img src="{{ $siteProfile?->site_logo_url ?? asset('images/logo-miru.png') }}" alt="Logo" class="h-10 w-10 shrink-0 rounded-lg bg-white object-contain p-1"><span class="text-sm font-black">ADMIN CMS</span></a>
            <button @click="close()" class="min-h-11 rounded-lg border border-white/30 px-3 text-sm lg:hidden" aria-label="Tutup navigasi admin">Tutup</button>
        </div>
        <div id="admin-navigation" class="px-3 pb-5" @click="if ($event.target.closest('a')) close(false)"><x-admin-sidebar /></div>
    </aside>
    <div class="min-w-0">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b bg-white px-4 py-4 sm:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <button @click="toggle($event)" :aria-expanded="open" aria-controls="admin-drawer" aria-label="Buka navigasi admin" class="btn-secondary shrink-0 lg:hidden">☰ Menu</button>
                <div class="min-w-0"><p class="break-words font-bold">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">{{ auth()->user()->role }}</p></div>
            </div>
            <a href="{{ route('home') }}" class="btn-secondary">Lihat Website ↗</a>
        </header>
        <main class="min-w-0 p-4 sm:p-6 lg:p-8">
            @if(session('success'))<p role="status" class="mb-5 rounded-xl bg-teal-50 p-4 text-primary">{{ session('success') }}</p>@endif
            @if($errors->any())<div role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
