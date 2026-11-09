@extends('layouts.app')
@section('title','Kegiatan Siswa')
@section('content')
<section class="page-shell"><p class="text-sm font-bold tracking-wider text-primary">KESISWAAN</p><h1 class="mt-2 text-4xl">Kegiatan Siswa</h1><p class="mt-4 max-w-2xl leading-7 text-slate-600">Cerita pembelajaran, kebersamaan, dan tumbuhnya karakter siswa {{ $siteProfile?->name ?: 'madrasah' }}.</p>
<div class="mt-9 grid gap-6 md:grid-cols-2 lg:grid-cols-3">@forelse($activities as $activity)<x-activity-card :activity="$activity" />@empty<p class="surface-card col-span-full text-slate-500">Kegiatan akan segera diperbarui.</p>@endforelse</div>
<div class="mt-8">{{ $activities->links() }}</div></section>
@endsection