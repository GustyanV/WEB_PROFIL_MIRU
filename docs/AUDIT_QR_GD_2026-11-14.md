> Catatan historis: keputusan dalam audit ini mendahului implementasi Vercel tanpa GD. Lihat [laporan terbaru](VERCEL_GDLESS_LOGO_2026-11-15.md).

# Audit QR SVG dan kebutuhan GD untuk deployment

Tanggal: 14 November 2026. Branch: deploy-vercel.

## Keputusan

Migrasi QR ke Bacon SVG langsung memungkinkan, tetapi **penghapusan GD untuk keseluruhan aplikasi belum aman**. Tidak ada perubahan dependency, controller, guard extension, environment, database, atau konfigurasi Supabase. Tidak ada push/deployment.

Penyebab kegagalan build sudah dibuktikan oleh log pengguna: PHP 8.2.20, pdo_pgsql loaded, gd MISSING; simplesoftwareio/simple-qrcode membutuhkan ext-gd dan check-platform-reqs berhenti dengan exit 2.

## Hasil pencarian penggunaan

- Satu pemanggilan pembuat QR production: ApplicationPrintController::proofData(), memakai facade SimpleSoftwareIO QrCode, format SVG, size 150.
- Payload: route spmb.verify dengan public_token existing. Tidak ada perubahan token, isi QR, URL tujuan, otorisasi, atau alur bisnis.
- Preview dashboard.proof dan PDF pdf.proof memakai data:image/svg+xml;base64. Ukuran CSS existing dashboard h-36/w-36; PDF 115px. Tidak ada QR PNG atau generator QR JavaScript lain ditemukan dalam app/resources/routes.
- Controller yang sama memakai imagecreatefromstring(), imagepng(), imagedestroy() untuk mengonversi logo menjadi data URI PNG. Fungsi ini memerlukan GD, terlepas dari pilihan generator QR.
- SchoolProfileController menerima logo JPG/JPEG/PNG/WebP. Mengganti konversi dengan menempelkan bytes asli akan mengubah dukungan logo WebP dan belum mengatasi kebutuhan konversi gambar tertentu oleh Dompdf.
- Dompdf CPDF juga mempunyai konversi bitmap yang memakai GD; renderer vendor tertentu memerlukan GD untuk PNG alpha dan format tertentu. Tidak semua path itu selalu dieksekusi, tetapi menghapus GD tanpa audit seluruh format logo tidak menjamin fitur tetap utuh.

## API dependency terkunci

- simplesoftwareio/simple-qrcode 4.2.0: require bacon/bacon-qr-code ^2.0 dan ext-gd.
- bacon/bacon-qr-code 2.0.8: require PHP ^7.1 || ^8.0, ext-iconv dan dasprid/enum ^1.0.3.
- API yang tersedia: Writer, ImageRenderer, RendererStyle, SvgImageBackEnd; SVG backend memerlukan XMLWriter. RendererStyle default margin 4, sedangkan wrapper Simple QR menggunakan margin 0. Migrasi naif yang mengabaikan perbedaan ini mengubah QR.
- Writer default encoding mengikuti Encoder::DEFAULT_BYTE_MODE_ECODING dan error correction L, sama dengan wrapper existing yang tidak mengubahnya.
- Wrapper memakai putih/hitam RGB dan EyeFill inherit. Default Fill Bacon memakai Gray; untuk kesetaraan output byte, isi Fill harus dibuat dengan RGB yang sama.

## Pengujian yang benar-benar dijalankan

1. Probe lokal memakai PHP -n (GD tidak dimuat), autoload vendor, Generator existing dan Writer Bacon langsung. Payload contoh berbentuk URL verifikasi dengan UUID, size 150, margin 0, warna RGB sama. Hasil: gd_loaded=false, svg_byte_identical=true, width=150, height=150, fungsi imagecreatefromstring tidak tersedia. Probe disimpan hanya di storage/app/testing/qr-svg-audit.php yang diabaikan Git. Ini membuktikan kesetaraan QR pada payload yang diuji, bukan keberhasilan PDF tanpa GD atau pengujian semua kemungkinan URL.
2. PHPUnit penuh pada dependency existing: **66 tests, 840 assertions lulus**. Termasuk alur QR/PDF dan logo cloud existing. Database suite terisolasi SQLite; tidak mengubah database Supabase.
3. Composer validate --no-check-publish: valid dengan warning existing constraint exact Simple QR 4.2.
4. Composer check-platform-reqs --no-dev: lulus pada PHP lokal XAMPP 8.0.30 dengan GD tersedia. Ini bukan bukti platform Vercel tanpa GD lulus.
5. Tidak menjalankan composer update/remove/install, tidak memakai ignore-platform-reqs atau config.platform untuk memalsukan extension.

## Alternatif mempertahankan fitur: Docker PHP 8.2 dengan GD

Gunakan lingkungan container yang benar-benar menyediakan GD beserta PNG, JPEG dan WebP. Contoh fondasi image extension (bukan Dockerfile deployment lengkap):

```dockerfile
FROM php:8.2-apache-bookworm
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libpq-dev libpng-dev libjpeg62-turbo-dev libwebp-dev \
       libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" gd pdo_pgsql mbstring dom xmlwriter \
    && rm -rf /var/lib/apt/lists/*
RUN php -r 'foreach (["gd","pdo_pgsql","mbstring","fileinfo","dom","openssl","xmlwriter","iconv"] as $e) { if (!extension_loaded($e)) { fwrite(STDERR, "Missing: ".$e.PHP_EOL); exit(1); } }'
```

Untuk deployment container penuh, selanjutnya perlu:

- Build Vite dengan Node 22 pada stage terpisah; salin public/build ke image aplikasi.
- Instal dependency terkunci memakai Composer di PHP container yang sama: composer install --no-dev --prefer-dist --no-interaction --no-scripts, kemudian composer check-platform-reqs --no-dev. Jangan mengabaikan requirement platform.
- Arahkan Apache DocumentRoot ke public/ Laravel dan aktifkan mod_rewrite; jangan mengekspos root project.
- Pastikan storage dan bootstrap/cache writable, jalankan package discovery tanpa menjalankan migration atau menghasilkan APP_KEY baru.
- Gunakan .dockerignore untuk .env, .env.*, .git, vendor lokal, node_modules, dump database, dan file privat; inject secret hanya saat runtime dari secret manager/hosting.
- Pertahankan APP_KEY existing serta koneksi PostgreSQL dan disk Supabase S3 existing.
- Jalankan pada host container yang mendukung image tersebut. File Docker saja tidak menambahkan GD ke vercel-php@0.6.2; jika tetap memakai Vercel, diperlukan runtime PHP dengan GD yang benar-benar diverifikasi terpisah.
- Uji logo PNG/JPEG/WebP, preview/cetak PDF admin dan pendaftar, scan QR ke URL verifikasi, serta upload/download private sebelum production.

Docker tidak tersedia pada lingkungan agent ini; contoh image belum dibuild dan belum diklaim production-ready. Pin digest image dan versi tool pada konfigurasi deployment final setelah image diuji.

Referensi resmi: https://hub.docker.com/_/php dan https://www.php.net/manual/en/image.installation.php.

## File yang berubah

Hanya dokumen audit ini. File aplikasi/dependency tidak berubah. Fitur tidak dihapus atau dilonggarkan. Tahap berikutnya memerlukan keputusan terpisah: lingkungan PHP dengan GD untuk mempertahankan fitur saat ini, atau perubahan dukungan/pemrosesan logo PDF yang harus disetujui dan diuji sebelum GD dapat dihapus.