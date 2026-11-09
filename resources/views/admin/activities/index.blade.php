@extends('layouts.activity-admin')
@section('title','Kegiatan Siswa')
@section('activity-content')
<div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-3xl">Kegiatan Siswa</h1><a href="{{ route('admin.activities.create') }}" class="btn-primary">Tambah Kegiatan</a></div>
<form method="GET" class="my-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
<label class="text-sm">Cari judul / ringkasan<input class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari kegiatan"></label>
<label class="text-sm">Kategori<select name="category" class="form-control"><option value="">Semua kategori</option>@foreach(\App\Models\Activity::CATEGORIES as $category)<option @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select></label>
<label class="text-sm">Status<select name="status" class="form-control"><option value="">Semua status</option>@foreach(['draft','published'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></label>
<div class="flex items-end gap-3"><button class="btn-primary">Filter</button><a href="{{ route('admin.activities.index') }}" class="btn-secondary">Reset</a></div>
</form>
<div role="region" aria-label="Tabel data, geser untuk melihat kolom lainnya" tabindex="0" class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr>@foreach(['Thumbnail','Judul','Kategori','Tanggal Kegiatan','Status','Published At','Aksi'] as $label)<th class="whitespace-nowrap p-3">{{ $label }}</th>@endforeach</tr></thead>
<tbody>@forelse($activities as $activity)<tr class="border-t">
<td class="p-3"><img src="{{ $activity->image_url }}" alt="{{ $activity->title }}" class="h-14 w-20 max-w-none rounded-lg object-cover"></td>
<td class="p-3 font-bold">{{ $activity->title }}</td><td class="p-3">{{ $activity->category }}</td><td class="whitespace-nowrap p-3">{{ $activity->activity_date->format('d/m/Y') }}</td>
<td class="p-3">{{ $activity->status }}</td><td class="whitespace-nowrap p-3">{{ $activity->published_at?->format('d/m/Y H:i') ?? '—' }}</td>
<td class="p-3"><a href="{{ route('admin.activities.edit',$activity) }}" class="font-bold text-primary">Edit</a>
<form action="{{ route('admin.activities.destroy',$activity) }}" method="POST" onsubmit="return confirm('Hapus kegiatan beserta semua foto galerinya?')">@csrf @method('DELETE')<button class="mt-2 text-red-600">Hapus</button></form></td>
</tr>@empty<tr><td colspan="7" class="p-5 text-slate-500">Belum ada kegiatan yang sesuai.</td></tr>@endforelse</tbody></table></div>
<div class="mt-6">{{ $activities->links() }}</div>
@endsection