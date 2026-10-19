@extends('layouts.admin')
@section('title', 'Kontak')
@section('content')

<div class="my-8 rounded-2xl bg-white p-6 shadow-soft">
<a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-primary">Kembali ke dashboard</a>
<h1 class="mt-4 text-3xl">Kontak</h1>

<form action="{{ route('admin.contact.update') }}" method="POST" class="mt-6 grid gap-4">
@csrf @method('PUT')
@foreach(['address'=>'Alamat', 'phone'=>'Telepon', 'whatsapp'=>'WhatsApp', 'email'=>'Email', 'service_hours'=>'Jam Layanan', 'google_maps_embed_url'=>'Google Maps Embed URL'] as $field=>$label)
<div><label for="{{ $field }}" class="text-sm font-bold">{{ $label }}</label>
@if(in_array($field, ['address','service_hours']))
<textarea id="{{ $field }}" name="{{ $field }}" rows="3" class="mt-1 w-full rounded-xl border-slate-200">{{ old($field, $profile->$field) }}</textarea>
@else
<input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : ($field === 'google_maps_embed_url' ? 'url' : 'text') }}" value="{{ old($field, $profile->$field) }}" class="mt-1 w-full rounded-xl border-slate-200">
@endif
@error($field)<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
@endforeach
<p class="text-xs text-slate-500">Dari Google Maps, pilih Bagikan → Sematkan peta. Salin URL https://www.google.com/maps/embed?... pada src saja, tanpa kode iframe. WhatsApp dapat menggunakan awalan 08 atau +62.</p>
<button class="rounded-xl bg-primary px-5 py-3 font-bold text-white">SIMPAN KONTAK</button>
</form></div>
@endsection