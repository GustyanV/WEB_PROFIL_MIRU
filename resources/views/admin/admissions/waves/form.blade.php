@extends('layouts.admin')
@section('title',$wave->exists?'Edit Gelombang':'Tambah Gelombang')
@section('content')
<h1 class="text-3xl">{{ $wave->exists?'Edit Gelombang':'Tambah Gelombang' }}</h1>
<form method="POST" action="{{ $wave->exists?route('admin.waves.update',$wave):route('admin.waves.store') }}" class="surface-card mt-6 grid max-w-3xl gap-5">
@csrf @if($wave->exists) @method('PUT') @endif
<label for="admission_period_id" class="form-label">Periode<select id="admission_period_id" name="admission_period_id" required class="form-control"><option value="">Pilih periode</option>@foreach($periods as $period)<option value="{{ $period->id }}" @selected((string)old('admission_period_id',$wave->admission_period_id)===(string)$period->id)>{{ $period->name }} ({{ $period->start_date->format('d/m/Y') }} – {{ $period->end_date->format('d/m/Y') }})</option>@endforeach</select></label>
@foreach(['name'=>'Nama Gelombang','start_date'=>'Tanggal Mulai','end_date'=>'Tanggal Selesai','quota'=>'Kuota'] as $field=>$label)<label for="{{ $field }}" class="form-label">{{ $label }}<input id="{{ $field }}" name="{{ $field }}" type="{{ str_ends_with($field,'date')?'date':($field==='quota'?'number':'text') }}" value="{{ old($field,str_ends_with($field,'date')?$wave->$field?->format('Y-m-d'):$wave->$field) }}" @required($field!=='quota') @if($field==='quota') min="0" @endif class="form-control"></label>@endforeach
<input type="hidden" name="is_active" value="0"><label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$wave->is_active))> Status Aktif</label>
<button class="btn-primary">SIMPAN GELOMBANG</button></form>
@endsection