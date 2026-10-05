@props(['variant' => 'primary', 'href' => '#'])
@php($classes = $variant === 'outline' ? 'border-2 border-white bg-white/10 text-white hover:bg-white hover:text-primary' : 'bg-accent text-slate-900 hover:bg-[#ffd16b]')
<a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-full px-6 py-3 text-sm font-extrabold tracking-wide transition {$classes}"]) }}>{{ $slot }}</a>
