<div class="space-y-2 text-sm">
@foreach(['address'=>'Alamat', 'phone'=>'Telepon', 'whatsapp'=>'WhatsApp', 'email'=>'Email', 'service_hours'=>'Jam Layanan'] as $field=>$label)
@if($publicContact?->$field)
<p class="whitespace-pre-line"><span class="font-bold">{{ $label }}:</span>
@if($field === 'email')<a href="mailto:{{ $publicContact->email }}">{{ $publicContact->email }}</a>
@elseif($field === 'whatsapp' && $publicWhatsappUrl)<a href="{{ $publicWhatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ $publicContact->whatsapp }}</a>
@else{{ $publicContact->$field }}@endif</p>
@endif
@endforeach
</div>