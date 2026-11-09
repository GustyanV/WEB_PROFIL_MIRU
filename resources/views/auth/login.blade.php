@extends('layouts.login')
@section('content')
<div class="mb-6 h-1 w-10 rounded-full bg-accent" aria-hidden="true"></div>
<p class="text-xs font-bold uppercase tracking-[.16em] text-primary">Selamat datang kembali</p>
<h1 class="mt-2 text-3xl font-extrabold text-ink">Masuk ke akun Anda</h1>
<p class="mt-3 text-sm leading-6 text-slate-500">Lanjutkan pendaftaran dan pantau informasi SPMB melalui akun Anda.</p>
@if(session('status'))<p role="status" class="mt-5 rounded-xl bg-teal-50 p-4 text-sm text-primary">{{ session('status') }}</p>@endif
<form class="mt-7 space-y-5" method="POST" action="{{ route('login.store') }}">
    @csrf
    <div>
        <label for="login-email" class="text-sm font-bold text-slate-700">Email</label>
        <input id="login-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com" @error('email') aria-invalid="true" aria-describedby="login-email-error" @enderror class="mt-2 block min-h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base transition focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
        @error('email')<p id="login-email-error" role="alert" class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="login-password" class="text-sm font-bold text-slate-700">Password</label>
        <input id="login-password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan password" @error('password') aria-invalid="true" aria-describedby="login-password-error" @enderror class="mt-2 block min-h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base transition focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20">
        @error('password')<p id="login-password-error" role="alert" class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 text-sm">
        <label class="flex min-h-11 cursor-pointer items-center gap-2 text-slate-600"><input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 accent-primary"> Ingat saya</label>
        <a href="{{ route('password.request') }}" class="inline-flex min-h-11 items-center font-bold text-primary hover:underline">Lupa password?</a>
    </div>
    <button type="submit" class="flex min-h-12 w-full items-center justify-center gap-3 rounded-xl bg-primary px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-primary-dark focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">MASUK <span aria-hidden="true">→</span></button>
</form>
<div class="mt-7 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center font-bold text-primary hover:underline">Daftar SPMB</a></div>
@endsection
