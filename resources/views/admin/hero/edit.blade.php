@extends('layouts.admin')
@section('title','Hero / Banner')
@section('content')
<div class="rounded-2xl bg-white p-5 shadow-soft sm:p-7">
<h1 class="text-3xl">Hero / Banner</h1>
<p class="mt-3 text-sm leading-6 text-slate-600">Gunakan satu desain gabungan foto siswa dan background. Teks serta tombol tetap diatur di form ini.</p>
<div class="mt-5 rounded-xl bg-teal-50 p-4 text-sm leading-6 text-slate-700">
<p><b>Desktop: 1920 × 720 px.</b> Siswa di sisi kiri; sisakan sekitar 50% sisi kanan untuk teks.</p>
<p class="mt-2"><b>Mobile opsional: 768 × 960 px.</b> Letakkan siswa di bagian atas; sisakan area bawah untuk teks.</p>
<p class="mt-2">Tanpa teks/tombol di dalam gambar. Gambar ditampilkan penuh sebagai background dengan pemotongan responsif di tepi; jaga objek penting jauh dari tepi desain. Banner tidak diulang sebagai motif.</p>
</div>
@if(!$hero->banner_desktop && ($hero->image || $hero->background_image))<p class="mt-4 text-sm text-amber-800">Foto dan motif versi lama tetap tersimpan. Unggah banner gabungan di bawah untuk menggantikan tampilan dua gambar.</p>@endif
<form method="POST" action="{{ route('admin.hero.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-6">
@csrf @method('PUT')
<div class="grid gap-5 lg:grid-cols-2">
@foreach(['banner_desktop'=>'Banner Desktop','banner_mobile'=>'Banner Mobile (opsional)'] as $field=>$label)
<div x-data="{ preview: null }">
<label for="{{ $field }}" class="form-label">{{ $label }}</label>
@php($currentBanner = $field==='banner_desktop'?$hero->desktop_banner_url:$hero->mobile_banner_url)
@if($currentBanner)<img src="{{ $field==='banner_desktop'?$hero->desktop_banner_url:$hero->mobile_banner_url }}" alt="{{ $label }} saat ini" class="mt-3 h-44 w-full rounded-xl bg-slate-100 object-contain">@else<div class="mt-3 flex h-44 items-center justify-center rounded-xl bg-slate-100 px-5 text-center text-sm text-slate-500">{{ $field==='banner_mobile'?'Belum diisi. Akan mengikuti banner desktop.':'Unggah desain banner gabungan Anda.' }}</div>@endif
<input id="{{ $field }}" type="file" name="{{ $field }}" accept=".jpg,.jpeg,.png,.webp" @change="if(preview) URL.revokeObjectURL(preview); preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null" class="mt-3 block w-full">
<template x-if="preview"><img :src="preview" alt="Preview banner pilihan" class="mt-3 h-44 w-full rounded-xl bg-slate-100 object-contain"></template>
<p class="mt-2 text-xs text-slate-500">JPG/JPEG/PNG/WEBP, maksimal 2 MB. Kosongkan untuk mempertahankan file.</p>
@error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
@endforeach
</div>
@if($hero->banner_mobile)<label class="text-sm"><input type="checkbox" name="remove_mobile" value="1" @checked(old('remove_mobile'))> Hapus banner mobile dan gunakan banner desktop pada HP</label>@endif
<div class="grid gap-5 sm:grid-cols-2">
@foreach(['small_text'=>'Small Text','title'=>'Judul','academic_year'=>'Tahun Pelajaran','primary_button_label'=>'Label Tombol Utama','primary_button_url'=>'URL Tombol Utama','secondary_button_label'=>'Label Tombol Kedua','secondary_button_url'=>'URL Tombol Kedua'] as $field=>$label)
<label for="{{ $field }}" class="form-label">{{ $label }}<input id="{{ $field }}" name="{{ $field }}" value="{{ old($field,$hero->$field) }}" maxlength="255" @required($field==='title') class="form-control">@error($field)<span class="text-sm text-red-600">{{ $message }}</span>@enderror</label>
@endforeach
</div>
<label for="subtitle" class="form-label">Subjudul<textarea id="subtitle" name="subtitle" rows="4" maxlength="5000" class="form-control">{{ old('subtitle',$hero->subtitle) }}</textarea>@error('subtitle')<span class="text-sm text-red-600">{{ $message }}</span>@enderror</label>
<p class="text-xs text-slate-500">URL dapat berupa https://..., /register, atau #informasi-spmb.</p>
<input type="hidden" name="is_active" value="0"><label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$hero->is_active))> Aktifkan hero</label>
<button class="btn-primary">SIMPAN HERO</button>
</form>
</div>
@endsection
