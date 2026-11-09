@extends('layouts.app')
@section('title',$activity->title)
@section('content')
<article class="page-shell">
<a href="{{ route('activities') }}" class="text-sm font-bold text-primary">← Semua Kegiatan</a>
<header class="mt-6 max-w-3xl"><span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-primary">{{ $activity->category }}</span>
<h1 class="mt-4 text-3xl sm:text-5xl">{{ $activity->title }}</h1><time datetime="{{ $activity->activity_date->format('Y-m-d') }}" class="mt-4 block text-sm text-slate-500">{{ $activity->activity_date->format('d/m/Y') }}</time></header>
<img src="{{ $activity->image_url }}" alt="{{ $activity->title }}" class="mt-8 max-h-[520px] w-full rounded-2xl object-cover">
<div class="mt-8 max-w-3xl whitespace-pre-line break-words text-lg leading-8 text-slate-700">{{ $activity->content }}</div>
<section class="mt-12" x-data="{ open: false, source: '', caption: '', trigger: null, show(event, image) { this.trigger = event.currentTarget; this.source = image.src; this.caption = image.alt; this.open = true; this.$nextTick(() => requestAnimationFrame(() => requestAnimationFrame(() => { if (this.open) this.$refs.close.focus(); }))); }, close() { this.open = false; this.trigger?.focus(); } }" @keydown.escape.window="if (open) close()">
<h2 class="text-2xl">Galeri Kegiatan</h2>
<div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
@forelse($activity->images as $image)<figure class="overflow-hidden rounded-2xl bg-white shadow-sm">
<a href="{{ \App\Support\SchoolContent::image($image->image,'gallery') }}" target="_blank" rel="noopener" @click.prevent="show($event, $event.currentTarget.querySelector('img'))" aria-label="Perbesar foto">
<img src="{{ \App\Support\SchoolContent::image($image->image,'gallery') }}" alt="{{ $image->caption ?: $activity->title }}" loading="lazy" class="h-56 w-full object-cover"></a>
@if($image->caption)<figcaption class="p-4 text-sm text-slate-600">{{ $image->caption }}</figcaption>@endif
</figure>@empty<p class="text-slate-500">Dokumentasi foto akan segera ditambahkan.</p>@endforelse
</div>
<template x-teleport="body"><div x-show="open" x-cloak x-effect="document.body.style.overflow = open ? 'hidden' : ''" role="dialog" aria-modal="true" aria-label="Pratinjau foto kegiatan" @click.self="close()" @keydown.tab.prevent="$refs.close.focus()" class="activity-lightbox fixed inset-0 z-[100] overflow-y-auto overscroll-contain bg-slate-950/95 p-4 pt-20 sm:p-6 sm:pt-20">
<button x-ref="close" @click="close()" class="absolute right-5 top-5 rounded-xl bg-white px-4 py-2 font-bold text-slate-900">Tutup ✕</button>
<img :src="source" :alt="caption" class="mx-auto max-h-[60dvh] max-w-full rounded-lg object-contain"><p x-text="caption" class="mx-auto mt-4 max-w-3xl break-words text-center text-sm leading-6 text-white"></p>
</div></template>
</section></article>
@endsection