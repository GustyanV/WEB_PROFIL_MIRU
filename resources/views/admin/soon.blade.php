@extends('layouts.auth')
@section('title','Dalam Pengembangan')
@section('content')
<div class="my-8 rounded-2xl bg-white p-8 shadow-soft"><p class="text-sm font-bold text-primary">ADMIN PANEL</p><h1 class="mt-2 text-3xl font-extrabold">Modul {{ ucfirst($module) }}</h1><p class="mt-3 text-slate-600">Menu ini sudah dapat dibuka. Fitur pengelolaan lengkapnya dijadwalkan pada tahap berikutnya.</p><a href="{{ route('admin.dashboard') }}" class="mt-6 inline-block rounded-xl bg-primary px-4 py-3 font-bold text-white">KEMBALI KE DASHBOARD</a></div>
@endsection
