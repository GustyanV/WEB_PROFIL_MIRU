@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<div class="mt-8 rounded-3xl bg-white p-7 shadow-soft">
    <h1 class="text-3xl font-extrabold">Buat password baru</h1>
    @if($errors->any())<div role="alert" class="mt-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="block text-sm font-bold">Email<input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email" class="mt-2 w-full rounded-xl border-slate-200"></label>
        <label class="block text-sm font-bold">Password baru<input type="password" name="password" required minlength="8" autocomplete="new-password" class="mt-2 w-full rounded-xl border-slate-200"></label>
        <label class="block text-sm font-bold">Konfirmasi password<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="mt-2 w-full rounded-xl border-slate-200"></label>
        <button class="w-full rounded-xl bg-primary py-3 font-bold text-white">SIMPAN PASSWORD</button>
    </form>
</div>
@endsection
