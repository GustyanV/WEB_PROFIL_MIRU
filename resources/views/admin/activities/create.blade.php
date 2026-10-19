@extends('layouts.activity-admin')
@section('title','Tambah Kegiatan')
@section('activity-content')
<h1 class="mb-6 text-3xl">Tambah Kegiatan</h1>
<form action="{{ route('admin.activities.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-4">@include('admin.activities._form')</form>
@endsection