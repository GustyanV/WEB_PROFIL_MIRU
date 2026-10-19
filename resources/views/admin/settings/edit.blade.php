@extends('layouts.admin')
@section('title','Pengaturan Website')
@section('content')
<h1 class="text-3xl">Pengaturan Website</h1>
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="surface-card mt-6 grid max-w-3xl gap-5">
@csrf @method('PUT')
@foreach(['name'=>'Nama Website','tagline'=>'Tagline','copyright_text'=>'Copyright Text'] as $field=>$label)<label for="{{ $field }}" class="form-label">{{ $label }}<input name="{{ $field }}" id="{{ $field }}" value="{{ old($field,$profile->$field) }}" @required($field==='name') class="form-control"></label>@endforeach
<label for="footer_text" class="form-label">Footer Text<textarea id="footer_text" name="footer_text" rows="4" class="form-control">{{ old('footer_text',$profile->footer_text) }}</textarea></label>
@foreach(['logo'=>'Logo','favicon'=>'Favicon'] as $field=>$label)<label for="{{ $field }}" class="form-label">{{ $label }}<input type="file" id="{{ $field }}" name="{{ $field }}" accept="{{ $field==='logo'?'.jpg,.jpeg,.png,.webp':'.png,.ico' }}" class="mt-2 block w-full"></label>
<p class="text-xs text-slate-500">{{ $field==='logo'?'JPG, JPEG, PNG, WEBP. Maksimal 2 MB.':'PNG atau ICO. Maksimal 1 MB.' }} Kosongkan untuk mempertahankan gambar.</p>
<img src="{{ $field==='logo'?$profile->site_logo_url:$profile->site_favicon_url }}" alt="{{ $label }} saat ini" class="h-16 w-16 rounded-lg bg-slate-100 object-contain p-1">
@endforeach
<div class="grid gap-5 sm:grid-cols-2">@foreach(['primary_color'=>'Warna Utama','accent_color'=>'Warna Aksen'] as $field=>$label)<label for="{{ $field }}" class="form-label">{{ $label }} (opsional)<input id="{{ $field }}" name="{{ $field }}" value="{{ old($field,$profile->$field) }}" placeholder="{{ $field==='primary_color'?'#0F8B83':'#F4B942' }}" pattern="#[0-9a-fA-F]{6}" class="form-control"></label>@endforeach</div>
<p class="text-xs text-slate-500">Kosongkan warna untuk menggunakan warna bawaan website.</p><button class="btn-primary">SIMPAN PENGATURAN</button>
</form>
@endsection