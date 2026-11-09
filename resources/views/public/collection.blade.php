@extends('layouts.app')
@section('title',$title)
@section('content')
<section class="page-shell">
<div class="max-w-2xl"><p class="text-sm font-bold tracking-wider text-primary">{{ $siteProfile?->name ?? 'Madrasah' }}</p><h1 class="mt-2 text-4xl">{{ $title }}</h1><p class="mt-3 leading-7 text-slate-600">Informasi terbaru dari madrasah.</p></div>
<div class="mt-9 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
@forelse($items as $item)
@php
    $image=$item->photo ?? $item->image ?? $item->thumbnail ?? $item->cover ?? null;
    $kind=['teacher'=>'teacher','news'=>'news','album'=>'gallery'][$type] ?? 'school';
    $source=$type==='album' ? $item->image_url : \App\Support\SchoolContent::image($image,$kind);
@endphp
<article class="group overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-soft">
<img src="{{ $source }}" alt="{{ $item->name ?? $item->title }}" loading="lazy" class="h-48 w-full object-cover object-center">
<div class="p-5"><h2 class="text-xl">{{ $item->name ?? $item->title }}</h2>
@if($type==='teacher')
@if($item->position)<p class="mt-1 text-sm font-semibold text-primary">{{ $item->position }}</p>@endif
@if($item->subject)<p class="mt-1 text-sm text-slate-500">{{ $item->subject }}</p>@endif
@if($item->nip || $item->nuptk)<p class="mt-2 text-xs text-slate-500">@if($item->nip)NIP: {{ $item->nip }}@endif @if($item->nuptk)NUPTK: {{ $item->nuptk }}@endif</p>@endif
@if($item->bio)<p class="mt-3 text-sm leading-6 text-slate-600">{{ $item->bio }}</p>@endif
@elseif($type==='achievement')
<p class="mt-1 text-sm font-semibold text-primary">{{ $item->student_name }} · {{ $item->level }}</p><p class="mt-1 text-sm text-slate-500">Tahun {{ $item->year }}</p>
@else
@if($type==='extracurricular')
@if($item->coach_name)<p class="mt-2 text-sm font-bold text-primary">Pembina: {{ $item->coach_name }}</p>@endif
@if($item->schedule)<p class="mt-1 text-sm text-slate-500">Jadwal: {{ $item->schedule }}</p>@endif
@endif
<p class="mt-2 text-sm leading-6 text-slate-600">{{ Str::limit($item->description ?? $item->excerpt ?? $item->content ?? '',130) }}</p>
@endif
@if($type==='news')<a href="{{ route('news.show',$item->slug) }}" class="mt-4 inline-block text-sm font-bold text-primary">Baca selengkapnya →</a>@elseif($type==='album')<a href="{{ route('gallery.show',$item->slug) }}" class="mt-4 inline-block text-sm font-bold text-primary">Lihat album →</a>@endif
</div></article>
@empty<div class="surface-card col-span-full text-center"><p class="font-bold text-slate-700">Belum ada data untuk ditampilkan.</p><p class="mt-1 text-sm text-slate-500">Informasi akan diperbarui setelah data tersedia.</p></div>@endforelse
</div><div class="mt-8">{{ $items->links() }}</div>
</section>
@endsection
