@extends('layouts.admin')
@section('title', 'Tambah Media Sosial')
@section('content')

<div class="my-8 rounded-2xl bg-white p-6 shadow-soft"><a href="{{ route('admin.social-media.index') }}" class="text-sm font-bold text-primary">Kembali ke media sosial</a>
<h1 class="mt-4 text-3xl">Tambah Media Sosial</h1>
<form action="{{ route('admin.social-media.store') }}" method="POST" class="mt-6 grid gap-4">
@include('admin.social-media._form')
</form></div>
@endsection