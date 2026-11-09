@extends('layouts.app')
@section('title', $page === 'curriculum' ? 'Kurikulum' : ($page === 'activities' ? 'Kegiatan Siswa' : 'Kontak'))
@section('content')
<section class="page-shell">
@if($page === 'curriculum')
    <div class="max-w-3xl"><p class="text-sm font-bold tracking-wider text-primary">AKADEMIK</p><h1 class="mt-2 text-4xl">Kurikulum Madrasah</h1><p class="mt-4 leading-8 text-slate-600">{{ $siteProfile?->name ?: 'Madrasah' }} — Informasi kurikulum berikut perlu dikonfirmasi dan disesuaikan dengan dokumen resmi madrasah.</p></div>
    <div class="mt-9 grid gap-5 md:grid-cols-2 lg:grid-cols-3">@foreach([['Pembelajaran Umum','Penguatan literasi, numerasi, sains, bahasa, dan keterampilan berpikir kritis.'],['Pembelajaran Agama','Pembelajaran akidah akhlak, fikih, sejarah kebudayaan Islam, serta Al-Qur’an hadis.'],['Tahfidz dan Tahsin','Pembinaan bacaan dan hafalan Al-Qur’an sesuai kemampuan setiap siswa.'],['Literasi','Kebiasaan membaca, menceritakan kembali, dan menulis untuk menumbuhkan rasa ingin tahu.'],['Pembiasaan Ibadah','Salat dhuha, doa bersama, dan pembiasaan akhlak mulia dalam kehidupan sehari-hari.'],['Evaluasi Berkala','Pendampingan perkembangan akademik dan karakter melalui evaluasi yang positif.']] as [$heading,$description])<article class="surface-card"><h2 class="text-xl">{{ $heading }}</h2><p class="mt-3 text-sm leading-6 text-slate-600">{{ $description }}</p></article>@endforeach</div>
@elseif($page === 'activities')
    <div class="max-w-3xl"><p class="text-sm font-bold tracking-wider text-primary">KESISWAAN</p><h1 class="mt-2 text-4xl">Kegiatan Siswa</h1><p class="mt-4 leading-8 text-slate-600">Kegiatan siswa dirancang untuk mengembangkan karakter, kebersamaan, kreativitas, dan kepedulian sosial di luar pembelajaran kelas.</p></div>
    <div class="mt-9 grid gap-5 md:grid-cols-2 lg:grid-cols-3">@foreach([['Upacara Bendera','Melatih disiplin, rasa cinta tanah air, dan kesiapan memulai pekan belajar.'],['Kegiatan Keagamaan','Pembiasaan ibadah, peringatan hari besar Islam, dan kegiatan pesantren Ramadan.'],['Pramuka','Latihan kepemimpinan, keterampilan hidup, serta kerja sama dalam kelompok.'],['Class Meeting','Kegiatan olahraga dan kreativitas yang menjunjung sportivitas.'],['Kegiatan Sosial','Belajar berbagi dan peduli melalui kegiatan sosial bersama warga madrasah.'],['Peringatan Hari Besar','Ruang belajar yang meriah untuk memahami nilai sejarah dan kebudayaan.']] as [$heading,$description])<article class="surface-card"><h2 class="text-xl">{{ $heading }}</h2><p class="mt-3 text-sm leading-6 text-slate-600">{{ $description }}</p></article>@endforeach</div>
@else

    <div class="max-w-3xl"><p class="text-sm font-bold tracking-wider text-primary">HUBUNGI KAMI</p><h1 class="mt-2 text-4xl">Kontak Madrasah</h1><p class="mt-4 leading-8 text-slate-600">Kami dengan senang hati membantu informasi seputar madrasah dan Sistem Penerimaan Murid Baru.</p></div>
    <div class="mt-9 grid gap-6 lg:grid-cols-[1fr_.9fr]">
        <div class="surface-card space-y-5">
            <h2 class="text-xl">{{ $publicContact?->name ?? 'MI Raudlatul Ulum Jabung Wetan' }}</h2>
            <x-contact-details />
            <x-social-links />
        </div>
        <div class="rounded-2xl bg-primary p-7 text-white">
            <h2 class="text-2xl text-white">Lokasi Madrasah</h2>
            @if($publicMapsUrl)
                <iframe src="{{ $publicMapsUrl }}" title="Lokasi Madrasah di Google Maps" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen class="mt-4 h-80 w-full rounded-xl border-0"></iframe>
            @else
                <p class="mt-4 leading-7 text-teal-50">Peta lokasi belum tersedia. Silakan hubungi madrasah untuk petunjuk lokasi.</p>
            @endif
            @if($publicWhatsappUrl)<a href="{{ $publicWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex rounded-xl bg-accent px-4 py-3 text-sm font-bold text-slate-900">Hubungi via WhatsApp</a>@endif
        </div>
    </div>
@endif
</section>
@endsection
