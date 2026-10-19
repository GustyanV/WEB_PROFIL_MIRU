@extends('layouts.admin')
@section('title','Periode SPMB')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-3xl">Periode SPMB</h1><a href="{{ route('admin.periods.create') }}" class="btn-primary">Tambah Periode</a></div>
<p class="mt-3 text-sm text-slate-500">Hanya satu periode aktif. Mengaktifkan periode baru otomatis menonaktifkan periode sebelumnya.</p>
<div class="surface-card mt-6 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr>@foreach(['Nama / Tahun','Tanggal','Kuota','Status','Pendaftar','Aksi'] as $label)<th class="p-3">{{ $label }}</th>@endforeach</tr></thead><tbody>
@forelse($periods as $period)<tr class="border-t"><td class="p-3 font-bold">{{ $period->name }}<span class="mt-1 block text-xs font-normal">{{ $period->academic_year }}</span></td><td class="whitespace-nowrap p-3">{{ $period->start_date->format('d/m/Y') }} – {{ $period->end_date->format('d/m/Y') }}</td><td class="p-3">{{ $period->quota ?? '—' }}</td><td class="p-3">{{ $period->is_active?'Aktif':'Nonaktif' }}</td><td class="p-3">{{ $period->applications_count }}</td><td class="p-3"><a href="{{ route('admin.periods.edit',$period) }}" class="font-bold text-primary">Edit</a>
<form action="{{ route('admin.periods.destroy',$period) }}" method="POST" onsubmit="return confirm('Hapus periode dan gelombang yang belum digunakan ini?')" class="mt-2">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form></td></tr>
@empty<tr><td colspan="6" class="p-4">Belum ada periode.</td></tr>@endforelse</tbody></table></div><div class="mt-5">{{ $periods->links() }}</div>
@endsection