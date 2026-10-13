@extends('layouts.app')
@section('title', $school?->name ?? 'Profil Madrasah')
@section('content')
<section class="page-shell max-w-5xl">
    <p class="text-sm font-bold tracking-wider text-primary">BERANDA / PROFIL</p>
    <h1 class="mt-2 text-4xl">{{ $school?->name ?? 'Profil Madrasah' }}</h1>
    @if(! $school)
        <div class="surface-card mt-8 text-slate-500">Profil madrasah belum tersedia.</div>
    @elseif($page === 'history')
        <article class="surface-card mt-8"><h2 class="text-2xl">Sejarah Madrasah</h2><p class="mt-5 whitespace-pre-line leading-8 text-slate-600">{{ $school->history ?: 'Sejarah madrasah belum diisi.' }}</p></article>
    @elseif($page === 'vision')
        <div class="mt-8 grid gap-6"><article class="surface-card"><h2 class="text-2xl">Visi Madrasah</h2><p class="mt-5 whitespace-pre-line leading-8 text-slate-600">{{ $school->vision ?: 'Visi madrasah belum diisi.' }}</p></article><article class="surface-card"><h2 class="text-2xl">Misi Madrasah</h2><p class="mt-5 whitespace-pre-line leading-8 text-slate-600">{{ $school->mission ?: 'Misi madrasah belum diisi.' }}</p></article><article class="surface-card"><h2 class="text-2xl">Tujuan Madrasah</h2><p class="mt-5 whitespace-pre-line leading-8 text-slate-600">{{ $school->goals ?: 'Tujuan madrasah belum diisi.' }}</p></article></div>
    @elseif($page === 'headmaster')
        <article class="surface-card mt-8"><h2 class="text-2xl">Sambutan Kepala Madrasah</h2><p class="mt-5 whitespace-pre-line leading-8 text-slate-600">{{ $school->headmaster_message ?: 'Sambutan kepala madrasah belum diisi.' }}</p></article>
    @elseif($page === 'structure')
        <article class="surface-card mt-8"><h2 class="text-2xl">Struktur Organisasi</h2><p class="mt-5 text-slate-600">Struktur organisasi akan ditampilkan setelah datanya dilengkapi.</p></article>
    @else
        <div class="mt-8 grid gap-6"><article class="surface-card"><h2 class="text-2xl">Tentang Madrasah</h2><p class="mt-5 whitespace-pre-line leading-8 text-slate-600">{{ $school->about ?: 'Informasi tentang madrasah belum diisi.' }}</p></article><article class="rounded-2xl bg-primary p-7 text-white shadow-soft"><h2 class="text-2xl text-white">Komitmen Pendidikan</h2><p class="mt-5 whitespace-pre-line leading-8 text-teal-50">{{ $school->education_commitment ?: 'Komitmen pendidikan belum diisi.' }}</p></article></div>
    @endif
</section>
@endsection
