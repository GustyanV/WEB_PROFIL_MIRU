@extends('layouts.admin')
@section('title',$title)
@section('content')
<div class="my-4 rounded-2xl bg-white p-5 shadow-soft sm:p-7">
<h1 class="text-3xl">{{ isset($item)?'Edit':'Tambah' }} {{ $title }}</h1>
<form class="mt-6 grid gap-5" method="POST" enctype="multipart/form-data" action="{{ isset($item)?route('admin.content.update',[$type,$item->id]):route('admin.content.store',$type) }}">
@csrf @isset($item) @method('PUT') @endisset
@foreach($fields as $key=>$label)
@php
    [$caption,$kind]=array_pad(explode(':',$label,2),2,'');
    $value=old($key,$item->$key??'');
    if($kind==='datetime' && $value instanceof \DateTimeInterface) $value=$value->format('Y-m-d\TH:i');
@endphp
<div><label for="{{ $key }}" class="form-label">{{ $caption }}</label>
@if($kind==='textarea')
<textarea id="{{ $key }}" name="{{ $key }}" rows="4" class="form-control">{{ $value }}</textarea>
@elseif(str_starts_with($kind,'select'))
<select id="{{ $key }}" name="{{ $key }}" class="form-control">@foreach(explode('|',trim(str_replace(['select(',')'],'',$kind))) as $opt)<option @selected($value===$opt)>{{ $opt }}</option>@endforeach</select>
@elseif($kind==='image')
<input id="{{ $key }}" name="{{ $key }}" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full">
@elseif($kind==='boolean')
<input type="hidden" name="{{ $key }}" value="0"><label class="mt-2 flex gap-2"><input id="{{ $key }}" type="checkbox" name="{{ $key }}" value="1" @checked(old($key,$item->$key??true))> Aktif</label>
@else
<input id="{{ $key }}" name="{{ $key }}" type="{{ $kind==='datetime'?'datetime-local':($kind==='number'?'number':'text') }}" value="{{ $value }}" class="form-control">
@endif
@error($key)<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
@endforeach
<button class="btn-primary">SIMPAN</button></form>
@if($type==='galeri' && isset($item))
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.gallery.images.store',$item->id) }}" class="mt-8 grid gap-4 border-t pt-6">@csrf
<label class="form-label">Unggah banyak foto<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required class="mt-2 block w-full"></label>
<label class="form-label">Caption opsional<input name="caption" class="form-control"></label><button class="btn-primary">Unggah</button></form>
<section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@forelse($item->images as $image)<article class="rounded-xl border p-3"><img src="{{ \App\Support\SchoolContent::image($image->image,'gallery') }}" alt="{{ $image->caption ?: $item->title }}" class="h-40 w-full rounded-lg object-cover"><p class="mt-2 text-sm">{{ $image->caption }}</p>
<form method="POST" action="{{ route('admin.gallery.images.destroy',$image->id) }}" onsubmit="return confirm('Hapus foto galeri ini?')" class="mt-3">@csrf @method('DELETE')<button class="text-sm font-bold text-red-600">Hapus Foto</button></form></article>
@empty<p class="text-sm text-slate-500">Belum ada foto. Unggah dokumentasi asli di atas.</p>@endforelse
</section>
@endif
</div>
@endsection