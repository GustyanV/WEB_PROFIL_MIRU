> Catatan historis: keputusan dalam audit ini mendahului implementasi Vercel tanpa GD. Lihat [laporan terbaru](VERCEL_GDLESS_LOGO_2026-11-15.md).

# Audit detail logo bukti pendaftaran tanpa GD

Tanggal: 14 November 2026. Branch: deploy-vercel.

## Keputusan

**Pertahankan kode production saat ini.** Mengganti hasil konversi GD dengan bytes logo asli belum aman untuk semua format CMS existing. Penolakan PNG bukan sekadar masalah transparansi: Dompdf yang terpasang secara eksplisit mewajibkan GD pada awal Cpdf::addPngFromFile(). Tidak dilakukan perubahan controller, template, composer.json/lock, autentikasi, struktur tabel, database Supabase, Storage atau alur bisnis. Tidak ada push atau deployment.

## Audit penggunaan GD

Dalam source aplikasi, fungsi GD ditemukan pada ApplicationPrintController::proofData(): imagecreatefromstring, imagepng dan imagedestroy. Prosesnya membaca logo dari packaged public/images/logo-miru.png atau disk public existing, mendekode format raster asli, lalu mengekspor PNG sebagai data URI. Ini menormalisasi logo JPEG/PNG/WebP yang diterima CMS agar bisa dimasukkan ke template bukti. Sumber file/cloud, fallback dan logika otorisasi tidak diubah.

Preview pendaftar, PDF pendaftar dan PDF admin menggunakan proofData yang sama. Jadi meskipun QR sudah SVG, penghapusan konversi logo berdampak pada ketiga jalur tersebut. QR tidak mempunyai pemanggilan PNG dalam aplikasi.

Source vendor Dompdf CPDF mempunyai pemanggilan GD untuk PNG dan konversi bitmap termasuk WebP. Simple QR mensyaratkan ext-gd pada metadata Composer. Fungsi GD tambahan dalam tests hanya dipakai untuk menghasilkan fixture gambar dan tidak dipanggil pada proses child tanpa GD.

## Hasil eksperimen pada template asli

Tes tidak mengubah controller: bytes asli kandidat dipasang sebagai data URI pada resources/views/pdf/proof.blade.php yang dikompilasi oleh Blade, dengan data pendaftaran sintetis dan QR SVG Bacon. PDF dirender dengan Dompdf terkunci pada proses PHP terpisah memakai -n, dan tes memastikan extension_loaded('gd') false. Bila tersedia, mbstring dimuat sendiri agar pengujian mengisolasi GD.

| Logo asli | PDF tanpa GD | Bukti |
|---|---|---|
| PNG opaque | Gagal | The PHP GD extension is required, but is not installed |
| PNG transparan alpha | Gagal | Exception GD yang sama |
| JPEG | Berhasil pada fixture | PDF 1 halaman; logo tertanam dengan filter DCTDecode |
| SVG vektor murni | Berhasil pada fixture | PDF 1 halaman dengan QR SVG dan logo di HTML sumber |
| WebP | Gagal | imagecreatefromwebp tidak tersedia untuk konversi Dompdf |

PNG dan WebP adalah format yang diterima CMS sekarang. SVG tidak termasuk format upload logo CMS; mendukung SVG eksperimen tidak berarti boleh mengganti kebijakan upload atau logo pengguna menjadi SVG. SVG dengan bitmap tertanam atau resource eksternal juga belum diuji, sehingga hasil vektor sederhana tidak digeneralisasi.

Logo JPEG dapat dimasukkan tanpa recompression sehingga kualitas bytes aslinya dipertahankan pada fixture. SVG vektor sederhana mempertahankan jalur vektor. Namun dukungan transparansi PNG/WebP tidak dapat dipertahankan pada stack tanpa GD saat ini. Mengganti semua logo menjadi JPEG akan menghilangkan alpha atau memerlukan proses flattening baru; tidak dilakukan diam-diam.

Template menggunakan kotak logo 82 x 82 px di kolom 105 px serta object-fit: contain. Dompdf terpasang tidak mengimplementasikan properti object-fit (pencarian source); proporsi logo nonpersegi tidak boleh dinyatakan terjamin hanya karena HTML mempunyai properti tersebut. Tidak ada perubahan CSS/layout atau klaim verifikasi visual semua format. Ukuran, layout, dan hasil PDF production tidak diubah pada pekerjaan ini.

## Pengujian

- Tes kompatibilitas baru: **5 tests, 31 assertions lulus**. Assertion membedakan keberhasilan JPEG/SVG dan kegagalan PNG/WebP yang memang diharapkan. Kegagalan kandidat tanpa GD ini adalah temuan, bukan klaim kandidat aman.
- Seluruh PHPUnit: **71 tests, 871 assertions lulus** setelah temporary directory proses tes diarahkan ke drive D.
- Suite existing mencakup preview, PDF pendaftar/admin, logo upload/cloud, URL QR, A4 dan bukti dengan data panjang. Pengujian tersebut tetap memakai GD existing dan SQLite terisolasi; tidak menyentuh Supabase.
- Percobaan suite pertama gagal satu assertion logo embedded saat C: memiliki 0 byte kosong. Tes PDF yang sama lulus kembali dengan sys_temp_dir khusus drive D; suite penuh kemudian lulus. Tidak ada file pengguna yang dihapus atau konfigurasi environment aplikasi yang diubah.
- PHP yang tersedia pada shell agent: XAMPP 8.0.30, bukan runtime Vercel. Child probe menonaktifkan GD secara nyata, tetapi ini belum menggantikan pengujian runtime Linux production.

Ulangi tes di PowerShell:

```powershell
php vendor/phpunit/phpunit/phpunit tests/Unit/PdfLogoCompatibilityTest.php
# Jika drive sistem penuh, gunakan temp proses tes di folder workspace yang writable:
php -d sys_temp_dir=storage/app/testing/pdf-logo-audit/temp vendor/phpunit/phpunit/phpunit
```

Direktori temporary harus sudah tersedia. Gunakan path absolut bila versi PHP/lingkungan memerlukannya. Fixture dan PDF hasil probe berada di storage/app/testing/pdf-logo-audit (diabaikan Git), untuk inspeksi manual. Tes parent memerlukan GD hanya untuk membuat fixture raster; child rendering tidak memuat GD. Pada mesin parent tanpa GD tes ditandai skipped, bukan diklaim lulus.

## File pekerjaan ini

- tests/Unit/PdfLogoCompatibilityTest.php — lima kasus format logo.
- tests/Support/pdf-logo-probe.php — render template PDF asli dalam proses terisolasi tanpa boot Laravel, database atau S3.
- docs/AUDIT_PDF_LOGO_TANPA_GD_2026-11-14.md — laporan ini.

Dokumen AUDIT_QR_GD_2026-11-14.md sudah ada sebagai file untracked sebelum pekerjaan ini dan dipertahankan. Tidak ada perubahan file production.

## Rekomendasi deployment

Untuk mempertahankan semua format logo dan fitur bukti, gunakan host container/VPS dengan Docker PHP 8.2 plus GD yang dikompilasi dengan PNG, JPEG, WebP dan FreeType. Gunakan pdo_pgsql untuk PostgreSQL, mbstring, fileinfo, dom, openssl, iconv dan XMLWriter untuk stack PDF/QR. Jalankan Apache atau Nginx/PHP-FPM dengan document root public/, HTTPS, serta storage/bootstrap/cache writable. Supabase PostgreSQL dan S3 existing tetap dapat dipakai tanpa mengubah flow aplikasi.

Contoh fondasi Docker dan checklist tersedia pada docs/AUDIT_QR_GD_2026-11-14.md. Image tersebut belum dibuild/dites di lingkungan ini; tidak dinyatakan production-ready. File Docker tidak mengubah extension dalam vercel-php@0.6.2. Jika tetap menggunakan Vercel, runtime alternatif dengan GD harus dibuktikan terlebih dahulu, atau diperlukan pekerjaan terpisah untuk mengubah stack PDF/pemrosesan format gambar, dengan pengujian semua format dan persetujuan lingkupnya.