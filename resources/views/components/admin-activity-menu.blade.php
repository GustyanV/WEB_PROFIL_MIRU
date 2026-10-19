<nav aria-label="Konten website" class="my-4 rounded-xl bg-primary p-4 text-white">
<p class="mb-2 text-xs font-bold tracking-wider">KONTEN WEBSITE</p>
<a href="{{ route('admin.activities.index') }}" @if(request()->routeIs('admin.activities.*')) aria-current="page" @endif class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.activities.*') ? 'bg-white/20 font-bold' : 'hover:bg-white/10' }}">Kegiatan Siswa</a>
</nav>