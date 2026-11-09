@extends('layouts.applicant')

@section('title', 'Bukti Pendaftaran')

@section('content')
    <div class="mx-auto my-8 max-w-2xl rounded-3xl bg-white p-8 shadow-sm">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold tracking-widest text-teal-700">SPMB ONLINE</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-800">Bukti Pendaftaran</h1>
                <p class="mt-1 text-slate-600">Simpan atau cetak bukti ini sebagai tanda pendaftaran.</p>
            </div>
            <img class="h-36 w-36" alt="QR Verifikasi Pendaftaran" src="data:image/svg+xml;base64,{{ $qr }}">
        </div>

        <dl class="mt-8 grid gap-4 border-y border-slate-200 py-6 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Nomor Pendaftaran</dt><dd class="font-bold text-slate-800">{{ $application->registration_number }}</dd></div>
            <div><dt class="text-sm text-slate-500">Nama Calon Siswa</dt><dd class="font-bold text-slate-800">{{ $application->studentProfile?->full_name ?? '-' }}</dd></div>
            <div><dt class="text-sm text-slate-500">Tahun Pelajaran</dt><dd class="font-semibold text-slate-800">{{ $application->period?->academic_year ?? '-' }}</dd></div>
            <div><dt class="text-sm text-slate-500">Gelombang</dt><dd class="font-semibold text-slate-800">{{ $application->wave?->name ?? '-' }}</dd></div>
        </dl>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('applicant.proof.pdf', $application) }}" class="rounded-lg bg-teal-700 px-5 py-3 font-bold text-white hover:bg-teal-800">UNDUH PDF</a>
            <a href="{{ route('applicant.dashboard') }}" class="rounded-lg border border-teal-700 px-5 py-3 font-bold text-teal-700 hover:bg-teal-50">KEMBALI KE DASHBOARD</a>
        </div>
    </div>
@endsection
