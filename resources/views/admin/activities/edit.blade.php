@extends('layouts.activity-admin')
@section('title','Edit Kegiatan')
@section('activity-content')
<h1 class="mb-6 text-3xl">Edit Kegiatan</h1>
<form action="{{ route('admin.activities.update',$activity) }}" method="POST" enctype="multipart/form-data" class="grid gap-4">@include('admin.activities._form')</form>
<section class="mt-10 border-t pt-7" x-data="{ previews: [], preview(files) { this.previews.forEach(photo => URL.revokeObjectURL(photo.url)); this.previews = Array.from(files).map(file => ({ url: URL.createObjectURL(file), name: file.name })); } }"><h2 class="text-2xl">GALERI KEGIATAN</h2>
<form action="{{ route('admin.activities.images.store',$activity) }}" method="POST" enctype="multipart/form-data" class="mt-5 flex flex-wrap items-center gap-4">@csrf
<label class="form-label">Tambah foto<input name="images[]" type="file" multiple required accept=".jpg,.jpeg,.png,.webp" @change="preview($event.target.files)" class="mt-2 block w-full"></label><button class="btn-primary">Upload Foto</button>
<p class="w-full text-xs text-slate-500">Maksimal 20 foto sekaligus, 2 MB per foto. Setelah diunggah, foto dapat dilihat dan diberi caption di bawah.</p></form>
<div x-cloak x-show="previews.length" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
<template x-for="photo in previews" :key="photo.url"><figure><img :src="photo.url" :alt="photo.name" class="h-28 w-full rounded-lg object-cover"><figcaption x-text="photo.name" class="mt-1 truncate text-xs text-slate-500"></figcaption></figure></template>
</div>
<div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
@forelse($activity->images as $image)<article class="rounded-xl border p-3">
<a href="{{ \App\Support\SchoolContent::image($image->image,'gallery') }}" target="_blank" rel="noopener"><img src="{{ \App\Support\SchoolContent::image($image->image,'gallery') }}" alt="{{ $image->caption ?: $activity->title }}" class="h-40 w-full rounded-lg object-cover"></a>
<form action="{{ route('admin.activities.images.update',[$activity,$image]) }}" method="POST" class="mt-3 grid gap-3">@csrf @method('PATCH')
<label for="caption-{{ $image->id }}" class="form-label">Caption<input id="caption-{{ $image->id }}" name="caption" value="{{ $image->caption }}" maxlength="255" class="form-control"></label>
<label for="order-{{ $image->id }}" class="form-label">Urutan<input id="order-{{ $image->id }}" name="sort_order" type="number" min="0" required value="{{ $image->sort_order }}" class="form-control"></label>
<input name="is_cover" type="hidden" value="0"><label class="text-sm"><input name="is_cover" type="checkbox" value="1" @checked($image->is_cover)> Jadikan cover</label><button class="btn-secondary">Simpan Foto</button>
</form>
<form action="{{ route('admin.activities.images.destroy',[$activity,$image]) }}" method="POST" onsubmit="return confirm('Hapus foto ini?')" class="mt-3">@csrf @method('DELETE')<button class="text-sm font-bold text-red-600">Hapus Foto</button></form>
</article>@empty<p class="text-slate-500">Belum ada foto galeri.</p>@endforelse
</div></section>
@endsection