<nav aria-label="Website" class="my-4 rounded-xl bg-primary p-4 text-white">
<p class="mb-2 text-xs font-bold tracking-wider">WEBSITE</p>
@foreach([['admin.contact.edit','Kontak'], ['admin.social-media.index','Media Sosial']] as [$name,$label])
<a href="{{ route($name) }}" @if(request()->routeIs($name === 'admin.contact.edit' ? 'admin.contact.*' : 'admin.social-media.*')) aria-current="page" @endif class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs($name === 'admin.contact.edit' ? 'admin.contact.*' : 'admin.social-media.*') ? 'bg-white/20 font-bold' : 'hover:bg-white/10' }}">{{ $label }}</a>
@endforeach
</nav>