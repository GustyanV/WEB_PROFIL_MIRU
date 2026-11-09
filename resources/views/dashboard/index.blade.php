@extends('layouts.applicant')
@section('title','Dashboard Pendaftar')
@section('content')
<section class="rounded-2xl bg-white p-5 shadow-soft sm:p-7">
    <p class="text-sm font-bold text-primary">DASHBOARD PENDAFTAR</p>
    <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">Assalamu'alaikum, {{ auth()->user()->name }}</h1>
    <p class="mt-2 text-slate-600">Status pendaftaran: <b>{{ $app->status }}</b></p>
    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="min-w-0 rounded-xl bg-teal-50 p-4"><p class="text-sm text-slate-500">Email</p><p class="break-words font-bold">{{ auth()->user()->email }}</p></div>
        <div class="min-w-0 rounded-xl bg-teal-50 p-4"><p class="text-sm text-slate-500">Nomor WhatsApp</p><p class="font-bold">{{ auth()->user()->phone }}</p></div>
        <div class="min-w-0 rounded-xl bg-accent/20 p-4"><p class="text-sm text-slate-500">Nomor Pendaftaran</p><p class="font-bold">{{ $app->registration_number ?? '-' }}</p></div>
    </div>
    <a href="{{ route('applicant.biodata') }}" class="btn-primary mt-6 w-full sm:w-auto">LANJUTKAN PENDAFTARAN</a>
</section>
@endsection
