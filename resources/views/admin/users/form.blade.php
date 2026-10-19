@extends('layouts.admin')
@section('title',$user->exists?'Edit Admin':'Tambah Admin')
@section('content')
<h1 class="text-3xl">{{ $user->exists?'Edit Admin':'Tambah Admin' }}</h1>
<form action="{{ $user->exists ? route('admin.users.update',$user) : route('admin.users.store') }}" method="POST" class="surface-card mt-6 grid max-w-3xl gap-5">
@csrf @if($user->exists) @method('PUT') @endif
@foreach(['name'=>'Nama','email'=>'Email','phone'=>'Nomor WhatsApp'] as $field=>$label)<label for="{{ $field }}" class="form-label">{{ $label }}<input id="{{ $field }}" name="{{ $field }}" type="{{ $field==='email'?'email':'text' }}" value="{{ old($field,$user->$field) }}" @required($field!=='phone') class="form-control"></label>@endforeach
<p class="text-sm text-slate-500">Role: admin</p>
@if(!$user->exists) @include('admin.users.password-fields') @endif
<input type="hidden" name="is_active" value="0"><label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$user->is_active))> Status Aktif</label>
<button class="btn-primary">SIMPAN ADMIN</button>
</form>
@if($user->exists)<section class="surface-card mt-8 max-w-3xl"><h2 class="text-xl">Reset Password</h2><form action="{{ route('admin.users.reset-password',$user) }}" method="POST" onsubmit="return confirm('Reset password akun admin ini?')" class="mt-5 grid gap-5">@csrf @include('admin.users.password-fields')<button class="btn-danger">RESET PASSWORD</button></form></section>@endif
@endsection