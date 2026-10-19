<link rel="icon" href="{{ $siteProfile?->site_favicon_url ?? asset('images/logo-miru.png') }}">
@php
$brandColors=[];
foreach (['primary_color'=>'--site-primary','accent_color'=>'--site-accent'] as $field=>$variable) {
    $hex=$siteProfile?->$field;
    if ($hex && preg_match('/^#[0-9a-fA-F]{6}$/',$hex)) {
        $rgb=[hexdec(substr($hex,1,2)),hexdec(substr($hex,3,2)),hexdec(substr($hex,5,2))];
        $brandColors[$variable]=implode(' ',$rgb);
        if ($field==='primary_color') $brandColors['--site-primary-dark']=implode(' ',array_map(fn($n)=>(int)round($n*.76),$rgb));
    }
}
@endphp
@if($brandColors)<style>:root { @foreach($brandColors as $variable=>$value) {{ $variable }}: {{ $value }}; @endforeach }</style>@endif