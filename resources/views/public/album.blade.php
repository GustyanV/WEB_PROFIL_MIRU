@extends('layouts.app')
@section('title', $album->title)
@section('content')
<section class="page-shell">
    <p class="text-sm font-bold tracking-wider text-primary">GALERI MADRASAH</p><h1 class="mt-2 text-4xl">{{ $album->title }}</h1><p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ $album->description }}</p>
    <div class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($album->images as $image)
            @php($source = \App\Support\SchoolContent::image($image->image,'gallery'))
            <a href="{{ $source }}" target="_blank" rel="noopener" class="group overflow-hidden rounded-2xl bg-white shadow-sm"><img src="{{ $source }}" alt="{{ $image->caption ?: $album->title }}" class="h-56 w-full object-cover transition duration-300 group-hover:scale-105"><p class="p-4 text-sm font-medium text-slate-600">{{ $image->caption ?: 'Dokumentasi kegiatan madrasah' }}</p></a>
        @empty
            <div class="surface-card col-span-full text-center text-slate-500">Belum ada foto pada album ini.</div>
        @endforelse
    </div>
</section>
@endsection
