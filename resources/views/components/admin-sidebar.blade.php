@php
$groups=[
'DASHBOARD'=>[['Ringkasan','admin.dashboard',null,'admin.dashboard']],
'KONTEN WEBSITE'=>[
['Hero / Banner','admin.hero.edit',null,'admin.hero.*'],['Profil Madrasah','admin.profile.edit',null,'admin.profile.*'],
['Guru & Tenaga Kependidikan','admin.content.index','guru','guru'],['Program Unggulan','admin.content.index','program','program'],
['Prestasi','admin.content.index','prestasi','prestasi'],['Ekstrakurikuler','admin.content.index','ekstrakurikuler','ekstrakurikuler'],
['Kegiatan Siswa','admin.activities.index',null,'admin.activities.*'],['Berita','admin.content.index','berita','berita'],
['Pengumuman','admin.content.index','pengumuman','pengumuman'],['Galeri','admin.content.index','galeri','galeri']],
'SPMB'=>[['Data Pendaftar','admin.applications.index',null,'admin.applications.*'],['Periode SPMB','admin.periods.index',null,'admin.periods.*'],['Gelombang SPMB','admin.waves.index',null,'admin.waves.*'],['Laporan','admin.reports.index',null,'admin.reports.*']],
'WEBSITE'=>[['Kontak','admin.contact.edit',null,'admin.contact.*'],['Media Sosial','admin.social-media.index',null,'admin.social-media.*'],['Pengaturan Website','admin.settings.edit',null,'admin.settings.*']],
];
if(auth()->user()->role==='super_admin') $groups['SISTEM']=[['Pengguna Admin','admin.users.index',null,'admin.users.*']];
@endphp
<nav aria-label="Navigasi admin" class="space-y-5">
@foreach($groups as $heading=>$links)<section><h2 class="px-3 text-[10px] font-bold tracking-[.16em] text-teal-200">{{ $heading }}</h2>
<div class="mt-2 space-y-1">@foreach($links as [$label,$name,$parameter,$pattern])
@php($active = $parameter ? request()->routeIs('admin.content.*') && request()->route('type') === $parameter : request()->routeIs($pattern))
<a href="{{ $parameter ? route($name,$parameter) : route($name) }}" @if($active) aria-current="page" @endif class="block rounded-lg px-3 py-2 text-sm {{ $active ? 'bg-white/20 font-bold text-white' : 'text-teal-50 hover:bg-white/10' }}">{{ $label }}</a>
@endforeach</div></section>@endforeach
<form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-lg border border-white/20 px-3 py-2 text-left text-sm font-bold">Logout</button></form>
</nav>