@extends('layouts.applicant')
@section('title', 'Review Pendaftaran')
@section('content')
<div class="page-shell max-w-4xl">
 <div class="surface-card"><p class="text-sm font-bold tracking-wider text-primary">LANGKAH 4 DARI 5</p><h1 class="mt-1 text-3xl">Review Pendaftaran</h1><p class="mt-2 text-slate-600">Pastikan data sudah benar sebelum mengirim pendaftaran.</p>
 @if($errors->has('submit'))<div role="alert" class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900"><b>Belum dapat dikirim.</b><p class="mt-1">{{ $errors->first('submit') }}</p></div>@endif
 <div class="mt-7 grid gap-4 sm:grid-cols-2"><section class="rounded-xl bg-teal-50 p-5"><h2 class="text-lg">Data Calon Siswa</h2><p class="mt-2 font-semibold">{{ $app->studentProfile?->full_name ?? 'Belum diisi' }}</p><p class="text-sm text-slate-600">NIK: {{ $app->studentProfile?->nik ?? '-' }}</p><a class="mt-4 inline-block text-sm font-bold text-primary" href="{{ route('applicant.biodata') }}">Edit data siswa →</a></section><section class="rounded-xl bg-teal-50 p-5"><h2 class="text-lg">Data Orang Tua</h2><p class="mt-2 font-semibold">{{ $app->parentProfile?->father_name ?: ($app->parentProfile?->mother_name ?: 'Belum diisi') }}</p><p class="text-sm text-slate-600">Data wali/orang tua</p><a class="mt-4 inline-block text-sm font-bold text-primary" href="{{ route('applicant.parents') }}">Edit data orang tua →</a></section><section class="rounded-xl bg-teal-50 p-5 sm:col-span-2"><h2 class="text-lg">Dokumen</h2><p class="mt-2 text-sm text-slate-600">{{ $app->documents->count() }} dokumen telah diunggah.</p><a class="mt-4 inline-block text-sm font-bold text-primary" href="{{ route('applicant.documents') }}">Periksa dokumen →</a></section></div>
 <form method="POST" action="{{ route('applicant.submit') }}" class="mt-7">@csrf<button type="submit" class="btn-primary" onclick="this.disabled=true; this.innerText='Memproses...'; this.form.submit();">KIRIM PENDAFTARAN</button></form>
 </div>
</div>
@endsection
