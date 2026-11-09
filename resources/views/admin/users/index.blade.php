@extends('layouts.admin')
@section('title','Pengguna Admin')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-3xl">Pengguna Admin</h1><a href="{{ route('admin.users.create') }}" class="btn-primary">Tambah Admin</a></div>
<p class="mt-3 text-sm text-slate-500">Hanya akun dengan role admin yang dikelola di sini. Akun super_admin dilindungi.</p>
<form method="GET" class="admin-toolbar my-5 flex gap-3"><input name="search" value="{{ request('search') }}" placeholder="Cari nama atau email" class="form-control max-w-lg"><button class="btn-primary">Cari</button></form>
<div role="region" aria-label="Tabel data, geser untuk melihat kolom lainnya" tabindex="0" class="surface-card overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr>@foreach(['Nama','Email','WhatsApp','Role','Status','Aksi'] as $label)<th class="p-3">{{ $label }}</th>@endforeach</tr></thead><tbody>
@forelse($users as $user)<tr class="border-t"><td class="p-3">{{ $user->name }}</td><td class="p-3">{{ $user->email }}</td><td class="p-3">{{ $user->phone }}</td><td class="p-3">{{ $user->role }}</td><td class="p-3">{{ $user->is_active?'Aktif':'Nonaktif' }}</td><td class="p-3"><a href="{{ route('admin.users.edit',$user) }}" class="font-bold text-primary">Edit / Reset Password</a></td></tr>
@empty<tr><td colspan="6" class="p-4 text-slate-500">Belum ada akun admin yang sesuai.</td></tr>@endforelse
</tbody></table></div><div class="mt-5">{{ $users->links() }}</div>
@endsection