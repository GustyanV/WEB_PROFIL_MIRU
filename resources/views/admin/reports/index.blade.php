@extends('layouts.admin')
@section('title','Laporan SPMB')
@section('content')
<h1 class="text-3xl">Laporan SPMB</h1><p class="mt-2 text-slate-500">Total hasil filter: {{ $total }} pendaftar.</p>
<form method="GET" class="my-6 flex flex-wrap gap-3">
<select name="period" aria-label="Periode" class="max-w-full rounded-xl border-slate-200"><option value="">Semua periode</option>@foreach($periods as $p)<option value="{{ $p->id }}" @selected((string)request('period')===(string)$p->id)>{{ $p->name }}</option>@endforeach</select>
<select name="wave" aria-label="Gelombang" class="max-w-full rounded-xl border-slate-200"><option value="">Semua gelombang</option>@foreach($waves as $w)<option value="{{ $w->id }}" @selected((string)request('wave')===(string)$w->id)>{{ $w->name }}</option>@endforeach</select>
<select name="status" aria-label="Status" class="rounded-xl border-slate-200"><option value="">Semua status</option>@foreach(['draft','submitted','under_review','revision','verified','accepted','rejected'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select>
<button class="btn-primary">Filter</button><a href="{{ route('admin.reports.export',request()->only('period','wave','status')) }}" class="btn-secondary">Export CSV</a>
</form>
<div class="surface-card overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr>@foreach(['Nomor','Nama Siswa','Wali','Periode','Gelombang','Status'] as $label)<th class="p-3">{{ $label }}</th>@endforeach</tr></thead><tbody>
@forelse($items as $item)<tr class="border-t"><td class="p-3"><a href="{{ route('admin.applications.show',$item) }}" class="text-primary">{{ $item->registration_number ?? 'Draft' }}</a></td><td class="p-3">{{ $item->studentProfile?->full_name }}</td><td class="p-3">{{ $item->user?->name }}</td><td class="p-3">{{ $item->period?->name }}</td><td class="p-3">{{ $item->wave?->name }}</td><td class="p-3">{{ $item->status }}</td></tr>@empty<tr><td colspan="6" class="p-4">Tidak ada data.</td></tr>@endforelse
</tbody></table></div><div class="mt-5">{{ $items->links() }}</div>
@endsection