@extends('layouts.admin')
@section('title', 'Media Sosial')
@section('content')

<div class="my-8 rounded-2xl bg-white p-6 shadow-soft"><a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-primary">Kembali ke dashboard</a>
<h1 class="mt-4 text-3xl">Media Sosial</h1>

<a href="{{ route('admin.social-media.create') }}" class="my-4 inline-block rounded-xl bg-primary px-4 py-2 font-bold text-white">Tambah media sosial</a>
<div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr>@foreach(['Platform','URL','Status','Urutan','Aksi'] as $label)<th class="p-2">{{ $label }}</th>@endforeach</tr></thead><tbody>
@forelse($items as $item)<tr class="border-t"><td class="p-2">{{ \App\Models\SocialMedia::PLATFORMS[$item->platform] ?? $item->platform }}</td><td class="max-w-xs break-all p-2">{{ $item->url ?: 'Belum diisi' }}</td><td class="p-2">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="p-2">{{ $item->sort_order }}</td><td class="p-2">
<a href="{{ route('admin.social-media.edit', $item) }}" class="font-bold text-primary">Edit</a>
<form method="POST" action="{{ route('admin.social-media.destroy', $item) }}" onsubmit="return confirm('Hapus media sosial ini?')" class="mt-2">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form>
</td></tr>@empty<tr><td colspan="5" class="p-4">Belum ada media sosial.</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $items->links() }}</div></div>
@endsection