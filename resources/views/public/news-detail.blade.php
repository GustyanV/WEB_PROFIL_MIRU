@extends('layouts.app')
@section('title',$item->title)
@section('content')
<article class="mx-auto max-w-3xl px-4 py-16"><p class="font-bold text-primary">BERITA</p><h1 class="mt-2 text-4xl">{{ $item->title }}</h1>
<p class="mt-3 text-slate-500">{{ $item->published_at?->format('d M Y') }}</p>
<img src="{{ \App\Support\SchoolContent::image($item->thumbnail,'news') }}" alt="{{ $item->title }}" class="mt-7 w-full rounded-2xl">
<p class="mt-8 whitespace-pre-line leading-8 text-slate-700">{{ $item->content }}</p></article>
@endsection
