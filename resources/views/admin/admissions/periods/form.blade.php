@extends('layouts.admin')
@section('title',$period->exists?'Edit Periode':'Tambah Periode')
@section('content')
<h1 class="text-3xl">{{ $period->exists?'Edit Periode':'Tambah Periode' }}</h1>
<form method="POST" action="{{ $period->exists?route('admin.periods.update',$period):route('admin.periods.store') }}" class="surface-card mt-6 grid max-w-3xl gap-5">
@csrf @if($period->exists) @method('PUT') @endif
@foreach(['name'=>'Nama Periode','academic_year'=>'Tahun Pelajaran','start_date'=>'Tanggal Mulai','end_date'=>'Tanggal Selesai','quota'=>'Kuota'] as $field=>$label)
<label for="{{ $field }}" class="form-label">{{ $label }}<input id="{{ $field }}" name="{{ $field }}" type="{{ str_ends_with($field,'date')?'date':($field==='quota'?'number':'text') }}" value="{{ old($field,str_ends_with($field,'date')?$period->$field?->format('Y-m-d'):$period->$field) }}" @required($field!=='quota') @if($field==='quota') min="1" @endif @if($field==='academic_year') placeholder="2027/2028" @endif class="form-control"></label>
@endforeach
<input type="hidden" name="is_active" value="0"><label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$period->is_active))> Aktifkan periode ini</label>
<button class="btn-primary">SIMPAN PERIODE</button></form>
@endsection