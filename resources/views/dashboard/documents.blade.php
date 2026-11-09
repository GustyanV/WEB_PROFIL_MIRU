@extends('layouts.applicant')
@section('title', 'Dokumen Pendaftaran')
@section('content')
@php
    $editable = in_array($app->status, ['draft', 'revision'], true);
    $required = ['student_photo', 'family_card', 'birth_certificate'];
    $documents = $app->documents->keyBy('document_type');
    $types = ['student_photo'=>'Foto Siswa','family_card'=>'Kartu Keluarga','birth_certificate'=>'Akta Kelahiran','father_id_card'=>'KTP Ayah','mother_id_card'=>'KTP Ibu','kindergarten_diploma'=>'Ijazah TK/RA','supporting_document'=>'Dokumen Pendukung'];
@endphp
<div class="page-shell max-w-4xl">
    <div class="surface-card">
        <p class="text-sm font-bold tracking-wider text-primary">LANGKAH 3 DARI 5</p>
        <h1 class="mt-1 text-3xl">Dokumen Pendaftaran</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Unggah dokumen dengan format JPG, JPEG, PNG, atau PDF sesuai ketentuan. Dokumen bertanda <span class="font-bold text-red-600">wajib</span> diperlukan sebelum review.</p>
        @if(session('success')) <div role="status" class="mt-5 rounded-xl border border-teal-100 bg-teal-50 p-4 text-sm font-medium text-primary">{{ session('success') }}</div> @endif
        @if($errors->any()) <div role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div> @endif
        <div class="mt-7 space-y-3">
            @foreach($types as $type => $label)
                @php($document = $documents->get($type))
                <article class="rounded-xl border border-slate-200 p-4 sm:flex sm:items-center sm:justify-between sm:gap-5">
                    <div class="min-w-0"><div class="flex items-center gap-2"><h2 class="text-base">{{ $label }}</h2>@if(in_array($type, $required))<span class="text-xs font-bold text-red-600">WAJIB</span>@endif</div>
                    @if($document)<p class="mt-1 truncate text-sm text-slate-500">{{ $document->original_name }} · {{ number_format(($document->file_size ?? 0) / 1024, 0) }} KB</p><span class="status-badge status-accepted mt-2">Sudah diunggah</span>@else <p class="mt-1 text-sm text-slate-500">Belum ada file</p><span class="status-badge status-draft mt-2">Belum diunggah</span>@endif</div>
                    <div class="mt-4 flex flex-wrap gap-2 sm:mt-0">
                        @if($document)<a class="btn-secondary" target="_blank" rel="noopener" href="{{ route('applicant.documents.show', $document) }}">Lihat</a>@if($editable)<form method="POST" action="{{ route('applicant.documents.delete', $document) }}">@csrf @method('DELETE')<button type="submit" class="btn-danger" onclick="return confirm('Hapus dokumen ini?')">Hapus</button></form>@endif @endif
                        @if($editable)<form method="POST" enctype="multipart/form-data" action="{{ route('applicant.documents.upload', $type) }}" class="flex items-center gap-2">@csrf<label class="btn-primary relative cursor-pointer focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2">{{ $document ? 'Ganti' : 'Unggah' }}<input class="sr-only" type="file" name="file" required accept="{{ $type === 'student_photo' ? '.jpg,.jpeg,.png' : '.jpg,.jpeg,.png,.pdf' }}" onchange="this.form.submit()"></label></form>@endif
                    </div>
                </article>
            @endforeach
        </div>
        @if(collect($required)->every(fn($type) => $documents->has($type)))
            <a href="{{ route('applicant.review') }}" class="btn-primary mt-7">LANJUT KE REVIEW PENDAFTARAN</a>
        @else
            <div class="mt-7 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">Lengkapi Foto Siswa, Kartu Keluarga, dan Akta Kelahiran untuk melanjutkan.</div>
        @endif
    </div>
</div>
@endsection
