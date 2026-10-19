<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title','Dashboard Admin') | {{ $siteProfile?->name ?? 'MI Raudlatul Ulum' }}</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<x-site-branding />
</head>
<body class="admin-shell bg-canvas" x-data="{ menuOpen: false }">
<div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]">
<aside class="bg-primary-dark text-white lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
<div class="flex items-center justify-between gap-3 p-5"><a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3"><img src="{{ $siteProfile?->site_logo_url ?? asset('images/logo-miru.png') }}" alt="Logo" class="h-10 w-10 shrink-0 rounded-lg bg-white object-contain p-1"><span class="text-sm font-black">ADMIN CMS</span></a>
<button @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="admin-navigation" class="rounded-lg border border-white/30 px-3 py-2 text-xs font-bold lg:hidden">MENU</button></div>
<div id="admin-navigation" :class="{ 'hidden': !menuOpen }" class="hidden px-3 pb-5 lg:!block"><x-admin-sidebar /></div>
</aside>
<div class="min-w-0"><header class="flex flex-wrap items-center justify-between gap-3 border-b bg-white px-5 py-4 sm:px-8"><div><p class="font-bold">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">{{ auth()->user()->role }}</p></div><a href="{{ route('home') }}" class="btn-secondary">Lihat Website ↗</a></header>
<main class="min-w-0 p-4 sm:p-8">
@if(session('success'))<p role="status" class="mb-5 rounded-xl bg-teal-50 p-4 text-primary">{{ session('success') }}</p>@endif
@if($errors->any())<div role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main></div></div>
</body></html>