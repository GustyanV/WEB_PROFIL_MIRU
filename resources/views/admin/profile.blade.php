@extends('layouts.admin')
@section('title','Profil Madrasah')
@section('content')
<div class="rounded-2xl bg-white p-6 shadow-soft"><h1 class="text-3xl">Profil Madrasah</h1>
<p class="mt-3 text-sm text-slate-500">Isi berdasarkan dokumen resmi sekolah. Kontak contoh tidak dianggap sebagai kontak final. Tagline dan favicon ICO tersedia di <a class="font-bold text-primary" href="{{ route('admin.settings.edit') }}">Pengaturan Website</a>.</p>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.profile.update') }}" class="mt-6 grid gap-4 md:grid-cols-2">
@csrf @method('PUT')
@foreach(['name'=>'Nama Madrasah','nsm'=>'NSM','npsn'=>'NPSN','address'=>'Alamat','phone'=>'Telepon','whatsapp'=>'WhatsApp','email'=>'Email','website'=>'Website','headmaster_name'=>'Nama Kepala Madrasah'] as $field=>$label)
<label class="form-label" for="{{ $field }}">{{ $label }}<input id="{{ $field }}" name="{{ $field }}" value="{{ old($field,$profile->$field) }}" @required($field==='name') class="form-control"></label>
@endforeach
@foreach(['about'=>'Tentang Madrasah','history'=>'Sejarah','vision'=>'Visi','mission'=>'Misi','goals'=>'Tujuan Madrasah','education_commitment'=>'Komitmen Pendidikan','headmaster_message'=>'Sambutan Kepala Madrasah'] as $field=>$label)
<label for="{{ $field }}" class="form-label md:col-span-2">{{ $label }}<textarea id="{{ $field }}" name="{{ $field }}" rows="5" class="form-control">{{ old($field,$profile->$field) }}</textarea></label>
@endforeach
@foreach(['logo'=>'Logo','favicon'=>'Favicon PNG','headmaster_photo'=>'Foto Kepala Madrasah'] as $field=>$label)
<div><label for="{{ $field }}" class="form-label">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="file" accept="{{ $field==='favicon'?'.png':'.jpg,.jpeg,.png,.webp' }}" class="mt-2 block w-full">
<img src="{{ $field==='logo'?$profile->site_logo_url:($field==='favicon'?$profile->site_favicon_url:\App\Support\SchoolContent::image($profile->headmaster_photo,'headmaster')) }}" alt="{{ $label }} saat ini" class="mt-3 h-24 w-24 rounded-xl object-contain">
<p class="mt-1 text-xs text-slate-500">Kosongkan untuk mempertahankan gambar. {{ $field==='favicon'?'PNG maksimal 1 MB.':'JPG/JPEG/PNG/WEBP maksimal 2 MB.' }}</p></div>
@endforeach
<button class="btn-primary md:col-span-2">SIMPAN PROFIL</button>
</form></div>
@endsection
