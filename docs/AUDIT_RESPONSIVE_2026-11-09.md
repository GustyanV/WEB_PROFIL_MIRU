# Audit Responsif — 9 November 2026

## Lingkup

Perbaikan tampilan pada project Laravel existing menggunakan Tailwind dan Alpine.js. Tidak ada perubahan backend, database aplikasi, autentikasi, status/alur SPMB, atau template PDF dalam pekerjaan responsif ini. Perubahan dari pekerjaan sebelumnya tetap dipertahankan.

## Temuan awal

- Tujuh kasus horizontal overflow terukur pada dashboard pendaftar dan halaman dokumen.
- Dashboard pendaftar menggunakan layout autentikasi dengan batas lebar max-w-md; grid desktop bersaing dengan panel promosi login.
- Nama berkas panjang dan kontrol unggahan memaksa halaman dokumen melebar.
- Menu publik mobile memakai lebar tetap dan daftar panjang yang belum dibatasi tinggi viewport.
- Sidebar admin masih menjadi menu yang membuka di atas konten, bukan drawer.
- Grid daftar konten mulai menjadi dua kolom pada 640 px; disesuaikan ke tablet mulai 768 px.
- Filter dan tabel admin membutuhkan penanganan scroll lokal dan target sentuh.
- Caption panjang pada lightbox perlu scroll dalam viewport. Fokus keyboard perlu menunggu panel tampil.

## Perubahan

- Layout pendaftar terpisah untuk seluruh halaman dashboard, biodata, orang tua, dokumen, review, status, dan pratinjau bukti.
- Sidebar admin dan pendaftar tetap terlihat pada desktop >= 1024 px; di bawahnya menjadi drawer dengan backdrop, tombol tutup, Escape, pengembalian fokus, pembatasan fokus keyboard, dan scroll lock.
- Navbar publik tetap horizontal pada desktop, dengan spacing ringkas pada 1024–1279 px. Mobile/tablet menggunakan hamburger dan kelompok dropdown yang dapat dibuka dengan keyboard.
- Menu mobile dibatasi tinggi viewport, dapat discroll, dan ditutup setelah klik link. Perubahan ukuran ke desktop mereset state menu.
- Header HP dipadatkan. Teks panjang, footer, grid, card, CTA, input date/select/file, dan nama berkas ditangani pada container masing-masing.
- Tabel pendaftar, konten, kegiatan, periode, gelombang, media sosial, pengguna, dan laporan memakai area scroll lokal yang dapat difokuskan.
- Filter admin stack pada mobile dan wrap pada tablet/desktop.
- Lightbox kegiatan dapat discroll dengan gambar object-contain serta caption panjang; tombol tutup tetap di bagian atas dan Escape mengembalikan fokus.
- Hero mempertahankan banner gabungan dan sumber mobile existing; proporsi teks tablet disesuaikan. Saat tidak ada gambar, ruang kosong atas dikurangi pada HP.
- Tidak menambahkan overflow-x-hidden pada body untuk menyamarkan overflow.
- Tidak menambah dependency atau library frontend.

## Halaman yang diuji

Publik: /, /profil, /profil/sejarah, /profil/visi-misi, /guru, /program-unggulan, /prestasi, /ekstrakurikuler, /kegiatan, detail kegiatan, /berita, /galeri, /kontak, /login, /register.

Pendaftar: /dashboard, /dashboard/biodata, /dashboard/orang-tua, /dashboard/dokumen, /dashboard/review, /dashboard/status, dan /dashboard/cetak-bukti/{application}.

Admin: /admin, /admin/pendaftar, /admin/hero, /admin/kontak, /admin/media-sosial, /admin/kegiatan, /admin/guru, /admin/berita, /admin/galeri, /admin/spmb/periode, /admin/spmb/gelombang, /admin/pengaturan-website, /admin/spmb/laporan, /admin/pengguna, serta form tambah guru/kegiatan.

Route publik /spmb tidak tersedia pada project existing. Navigasi SPMB menggunakan /register. Halaman bukti memerlukan ID aplikasi; /dashboard/cetak-bukti tanpa ID bukan route existing. Tidak menambahkan route baru dalam pekerjaan ini.

## Ukuran browser

| Lebar | Tinggi |
| --- | --- |
| 320 | 568 |
| 360 | 800 |
| 390 | 844 |
| 414 | 896 |
| 768 | 1024 |
| 820 | 1180 |
| 1024 | 768 |
| 1280 | 720 |
| 1440 | 900 |

Tambahan pengujian interaksi landscape: 568×320, 844×390, dan 1180×820. Uji perubahan ukuran dilakukan ketika menu terbuka, termasuk perpindahan ke desktop.

## Hasil verifikasi

- Browser Chrome/Edge headless melalui CDP, memakai SQLite dan akun fixture terpisah di storage/app/testing; database aplikasi existing tidak diubah.
- 38 halaman × 9 viewport = **342 pemeriksaan tampilan**.
- Hasil akhir: **0 horizontal overflow pada dokumen**, **0 halaman error**, dan **0 exception JavaScript** pada audit tersebut.
- **156 pemeriksaan interaksi lolos**: dropdown keyboard, fokus menu, backdrop, link-close, pergantian ukuran, scroll tabel, teks panjang, serta buka/tutup lightbox.
- **54 pengujian Laravel lolos**, termasuk regresi pendaftaran, dokumen privat, PDF, QR, dan export.
- **npm run build berhasil**. Peringatan existing /images/hero-pattern.png belum terselesaikan saat build dan tetap dicatat; tidak disembunyikan.
- git diff --check bersih. Template resources/views/pdf tidak diubah.

## Artefak lokal

Hasil rinci tersedia di storage/app/testing/responsive-final-report.json, responsive-interactions-report.json, responsive-laravel-tests.txt, dan screenshot responsive-final-*.png / responsive-menu-*.png. Direktori tersebut merupakan artefak lokal yang diabaikan Git.

## Batas pengujian

Emulasi viewport browser bukan pengujian langsung pada perangkat fisik. Safari/iOS, keyboard virtual, pembaca layar, dan perilaku browser lama belum diuji. Audit menggunakan konten fixture; variasi data resmi yang sangat panjang tetap perlu diperiksa ketika finalisasi konten.
