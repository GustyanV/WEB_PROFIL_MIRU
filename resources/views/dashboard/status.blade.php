@extends('layouts.applicant')
@section('title', 'Status Pendaftaran')
@section('content')
@php($statusClass = 'status-'.str_replace('_', '-', $app->status))
<div class="page-shell max-w-4xl">
    <div class="surface-card">
        <p class="text-sm font-bold tracking-wider text-primary">STATUS PENDAFTARAN</p>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-4"><div><h1 class="text-3xl">{{ $app->studentProfile?->full_name ?? 'Pendaftaran SPMB' }}</h1><p class="mt-1 text-sm text-slate-500">Nomor pendaftaran: {{ $app->registration_number ?? 'Belum tersedia' }}</p></div><span class="status-badge {{ $statusClass }} text-sm">{{ strtoupper(str_replace('_', ' ', $app->status)) }}</span></div>
        @if($app->status === 'revision')<div role="alert" class="mt-6 rounded-xl border border-orange-200 bg-orange-50 p-4 text-orange-900"><b>Perlu perbaikan data.</b><p class="mt-1">Silakan periksa catatan admin pada riwayat berikut, perbarui data yang diminta, lalu kirim ulang pendaftaran.</p></div>@endif
        <h2 class="mt-8 text-xl">Riwayat Pendaftaran</h2>
        <ol class="mt-4 space-y-4 border-l-2 border-teal-100 pl-5">@forelse($app->statusHistories as $history)<li class="relative"><span class="absolute -left-[29px] top-1 h-3 w-3 rounded-full bg-primary ring-4 ring-teal-50"></span><p class="font-bold text-slate-800">{{ strtoupper(str_replace('_', ' ', $history->status)) }}</p><p class="text-sm text-slate-500">{{ $history->created_at?->format('d M Y, H:i') }}</p>@if($history->note)<p class="mt-1 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">{{ $history->note }}</p>@endif</li>@empty<li class="text-sm text-slate-500">Belum ada riwayat perubahan status.</li>@endforelse</ol>
    </div>
</div>
@endsection
