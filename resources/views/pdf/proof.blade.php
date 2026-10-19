<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pendaftaran</title>
    <style>
        @page { margin: 25px 35px; }
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .letterhead { border-bottom: 4px solid #e87500; padding-bottom: 7px; }
        .letterhead-table { width: 100%; border-collapse: collapse; }
        .logo-cell { width: 105px; text-align: center; vertical-align: middle; }
        .logo { width: 82px; height: 82px; object-fit: contain; }
        .school-info { text-align: center; vertical-align: middle; line-height: 1.35; }
        .foundation { font-family: DejaVu Serif, serif; font-size: 15px; font-weight: bold; }
        .school-name { font-family: DejaVu Serif, serif; font-size: 18px; font-weight: bold; }
        .detail { font-family: DejaVu Serif, serif; font-size: 12px; font-weight: bold; }
        .address { background: #e87500; color: #111111; font-family: DejaVu Serif, serif; font-size: 10px; font-style: italic; margin-top: 5px; padding: 3px; text-align: center; }
        .title { color: #086a64; font-size: 17px; margin: 24px 0 14px; text-align: center; }
        .content-table { border-collapse: collapse; width: 100%; }
        .content-table td { border: 1px solid #d1d5db; padding: 8px 9px; vertical-align: top; }
        .label { background: #f0fdfa; font-weight: bold; width: 31%; }
        .qr { margin-top: 22px; text-align: center; }
        .qr img { height: 115px; width: 115px; }
        .note { color: #4b5563; font-size: 9px; margin-top: 14px; text-align: center; }
    </style>
</head>
<body>
    <header class="letterhead">
        <table class="letterhead-table">
            <tr>
                <td class="logo-cell">
                    @if($logo)<img class="logo" src="{{ $logo }}" alt="Logo {{ $school?->name ?: 'Madrasah' }}">@endif
                </td>
                <td class="school-info">
                    <div class="foundation">PENERIMAAN MURID BARU</div>
                    <div class="school-name">{{ $school?->name ?: 'Madrasah' }}</div>

                    <div class="detail">@if($school?->nsm)NSM : {{ $school->nsm }}@endif @if($school?->npsn)&nbsp; NPSN : {{ $school->npsn }}@endif</div>

                </td>
            </tr>
        </table>
        <div class="address">{{ $school?->address ? 'Alamat : '.$school->address : 'Alamat madrasah belum diisi.' }}</div>
    </header>

    <h1 class="title">BUKTI PENDAFTARAN SPMB</h1>

    <table class="content-table">
        <tr><td class="label">Nomor Pendaftaran</td><td>{{ $application->registration_number }}</td></tr>
        <tr><td class="label">Nama Calon Siswa</td><td>{{ $application->studentProfile?->full_name }}</td></tr>
        <tr><td class="label">NIK / NISN</td><td>{{ $application->studentProfile?->nik ?: '-' }} / {{ $application->studentProfile?->nisn ?: '-' }}</td></tr>
        <tr><td class="label">Tempat, Tanggal Lahir</td><td>{{ $application->studentProfile?->birth_place ?: '-' }}, {{ $application->studentProfile?->birth_date ?: '-' }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td>{{ $application->studentProfile?->gender ?: '-' }}</td></tr>
        <tr><td class="label">Asal TK/RA</td><td>{{ $application->studentProfile?->previous_school ?: '-' }}</td></tr>
        <tr><td class="label">Orang Tua/Wali</td><td>{{ $application->parentProfile?->father_name ?: ($application->parentProfile?->mother_name ?: '-') }}</td></tr>
        <tr><td class="label">WhatsApp</td><td>{{ $application->user?->phone ?: '-' }}</td></tr>
        <tr><td class="label">Tahun Pelajaran</td><td>{{ $application->period?->academic_year ?: '-' }}</td></tr>
        <tr><td class="label">Gelombang</td><td>{{ $application->wave?->name ?: '-' }}</td></tr>
        <tr><td class="label">Status Pendaftaran</td><td>{{ strtoupper(str_replace('_', ' ', $application->status)) }}</td></tr>
    </table>

    <div class="qr">
        <img alt="QR Verifikasi" src="data:image/svg+xml;base64,{{ $qr }}">
        <div>Scan QR Code untuk verifikasi bukti pendaftaran.</div>
    </div>
    <p class="note">Simpan bukti ini sebagai dokumen resmi proses SPMB {{ $school?->name ?: 'madrasah' }}.</p>
</body>
</html>
