@props(['activity'])
<article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-soft">
<a href="{{ route('activities.show',$activity->slug) }}"><img src="{{ $activity->image_url }}" alt="{{ $activity->title }}" loading="lazy" class="h-48 w-full object-cover"></a>
<div class="p-5"><span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-primary">{{ $activity->category }}</span>
<time datetime="{{ $activity->activity_date->format('Y-m-d') }}" class="mt-3 block text-xs text-slate-500">{{ $activity->activity_date->format('d/m/Y') }}</time>
<h3 class="mt-2 text-xl"><a href="{{ route('activities.show',$activity->slug) }}">{{ $activity->title }}</a></h3>
<p class="mt-3 text-sm leading-6 text-slate-600">{{ Str::limit($activity->excerpt ?: $activity->content,150) }}</p>
<a href="{{ route('activities.show',$activity->slug) }}" class="mt-4 inline-block text-sm font-bold text-primary">Baca Selengkapnya →</a></div>
</article>