<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | {{ $siteProfile?->name ?: 'Madrasah' }}</title>
    <x-site-branding />
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="screen-shell login-page min-h-screen bg-canvas">
    <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col justify-center px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
        <a href="{{ route('home') }}" class="mb-5 inline-flex min-h-11 w-fit items-center gap-2 text-sm font-bold text-primary">
            <span aria-hidden="true">←</span> Kembali ke beranda
        </a>
        <div class="grid min-w-0 overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-soft lg:grid-cols-2">
            <section class="login-brand-panel relative isolate flex min-w-0 flex-col justify-between gap-10 p-6 text-white sm:p-8 lg:p-10">
                <div aria-hidden="true" class="pointer-events-none absolute -right-20 -top-20 -z-10 h-64 w-64 rounded-full border-[28px] border-white/5"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-24 -z-10 h-80 w-80 rounded-full border-[32px] border-white/5"></div>
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-16 w-20 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm">
                        <img src="{{ $siteProfile?->site_logo_url ?? asset('images/logo-miru.png') }}" alt="Logo {{ $siteProfile?->name ?: 'MIRU' }}" width="80" height="64" class="h-16 w-20 object-contain">
                    </span>
                    <span class="min-w-0"><span class="block text-[10px] font-bold uppercase tracking-[.18em] text-teal-100">Madrasah Ibtidaiyah</span><span class="mt-1 block break-words text-base font-extrabold leading-6 sm:text-lg">{{ $siteProfile?->name ?: 'MI Raudlatul Ulum' }}</span></span>
                </a>
                <div class="hidden lg:block">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[11px] font-bold tracking-[.16em] text-accent"><span aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-accent"></span> SPMB ONLINE</span>
                    <h2 class="mt-5 max-w-md text-2xl font-extrabold leading-tight text-white sm:text-3xl lg:text-4xl">Langkah kecil hari ini,<br class="hidden sm:block"> masa depan yang berarti.</h2>
                    <p class="mt-4 max-w-sm text-sm leading-7 text-teal-50">Mulai perjalanan pendidikan putra-putri Anda bersama lingkungan belajar yang Islami, ramah, dan berkarakter.</p>
                    <ol class="mt-7 hidden space-y-4 lg:block" aria-label="Langkah pendaftaran">
                        @foreach([['01','Lengkapi data','Isi biodata dan unggah dokumen.'],['02','Kirim pendaftaran','Periksa data sebelum dikirim.'],['03','Pantau hasil','Lihat status dan catatan dari admin.']] as [$number,$heading,$description])
                        <li class="flex items-start gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-white/20 bg-white/10 text-xs font-extrabold text-accent">{{ $number }}</span><div><p class="text-sm font-bold">{{ $heading }}</p><p class="mt-0.5 text-xs leading-5 text-teal-100">{{ $description }}</p></div></li>
                        @endforeach
                    </ol>
                </div>
                <p class="hidden border-t border-white/20 pt-5 text-xs leading-6 text-teal-100 lg:block">{{ $siteProfile?->address ?: 'Website Profil Madrasah · SPMB Online' }}</p>
            </section>
            <section class="flex min-w-0 items-center p-6 sm:p-8 lg:p-10" aria-label="Form login">
                <div class="mx-auto w-full max-w-md">@yield('content')</div>
            </section>
        </div>
        <p class="mt-5 text-center text-xs leading-6 text-slate-500">Website Profil Madrasah <span aria-hidden="true">·</span> Sistem Penerimaan Murid Baru Online</p>
    </main>
</body>
</html>
