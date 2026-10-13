@extends('layouts.app')

@section('title', 'MI Raudlatul Ulum | SPMB')

@section('content')
    <section class="overflow-hidden bg-[#02878b]" style="background-image: url('{{ asset('images/hero-pattern.png') }}'); background-size: 500px 350px;">
        <div class="mx-auto grid max-w-7xl items-stretch lg:grid-cols-2">
            <div class="relative min-h-[320px] overflow-hidden sm:min-h-[420px] lg:min-h-[535px]">
                <img src="{{ asset('images/hero-siswa.png') }}" alt="Siswa madrasah dalam kegiatan belajar dan prestasi" class="absolute inset-0 h-full w-full object-cover object-center">
                <div class="absolute inset-0 bg-gradient-to-t from-primary-dark/40 via-transparent to-transparent"></div>
            </div>
            <div class="relative flex items-center overflow-hidden px-6 py-14 sm:px-12 lg:px-16">
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full border-[28px] border-accent/20"></div>
                <div class="relative max-w-xl">
                    <span class="inline-flex rounded-full bg-accent px-4 py-2 text-xs font-extrabold tracking-[0.2em] text-slate-900">SPMB 2026/2027</span>
                    <h1 class="mt-5 text-4xl font-black leading-tight text-white sm:text-5xl">Sistem Penerimaan Murid Baru</h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-teal-50/90 sm:text-lg">Bersama {{ $school?->name ?? 'madrasah kami' }}, tumbuhkan ilmu, akhlak, dan potensi terbaik putra-putri Anda.</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row"><x-button :href="route('register')">DAFTAR SEKARANG</x-button><x-button variant="outline" href="#informasi">INFORMASI SPMB</x-button></div>
                    <div id="informasi" class="mt-10 grid gap-3 border-t border-white/20 pt-6 sm:grid-cols-2"><p class="text-sm text-teal-50"><strong class="block text-white">Pendaftaran Dibuka</strong>Januari - Juni 2026</p><p class="text-sm text-teal-50"><strong class="block text-white">Proses Mudah</strong>Informasi tersedia online</p></div>
                </div>
            </div>
        </div>
    </section>
    <section id="daftar" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8"><div class="rounded-3xl bg-white p-8 shadow-soft sm:p-10"><p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">Selamat datang</p><h2 class="mt-2 text-3xl font-extrabold text-ink">{{ $school?->name ?? 'Pendidikan bermakna untuk masa depan.' }}</h2><p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ $school?->headmaster_message ?? 'Profil dan informasi madrasah akan segera diperbarui.' }}</p></div></section>
    @foreach(['Program Unggulan'=>$programs,'Prestasi Terbaru'=>$achievements,'Berita Terbaru'=>$news,'Galeri'=>$albums] as $heading=>$items)<section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8"><h2 class="text-3xl font-extrabold">{{ $heading }}</h2><div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($items as $item)<article class="rounded-2xl bg-white p-5 shadow-soft"><h3 class="font-extrabold">{{ $item->title ?? $item->name }}</h3><p class="mt-2 text-sm text-slate-600">{{ Str::limit($item->description ?? $item->excerpt ?? '',100) }}</p></article>@empty<p class="text-slate-500">Data belum tersedia.</p>@endforelse</div></section>@endforeach
@endsection
