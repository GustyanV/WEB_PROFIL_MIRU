# Implementasi logo PDF tanpa GD untuk Vercel

Selesai diverifikasi: 15 November 2026 (Asia/Jakarta). Branch deploy-vercel. Hosting target tetap Vercel, runtime vercel-php 0.6.2 / PHP 8.2.20. Tidak ada push, commit atau deployment otomatis.

## Hasil implementasi

- Logo website tetap file PNG/JPEG/WebP asli. Upload file asli tidak dikonversi/ditimpa dan logo lama tidak dihapus.
- Profil Madrasah **dan** Pengaturan Website sekarang menyiapkan JPEG PDF di browser menggunakan Canvas. Transparansi disatukan dengan latar putih khusus untuk halaman PDF yang memang putih. Dimensi asli dipertahankan, quality JPEG 1.0, tanpa resize atau penurunan kualitas otomatis untuk memenuhi batas upload. JPEG input dipakai verbatim, tanpa recompression.
- Admin wajib memeriksa preview dan mencentang persetujuan sebelum mengirim hasil konversi. Jika konversi gagal, kualitas dianggap tidak sesuai oleh admin, atau file terlalu besar, jangan simpan. Website tetap mempunyai sumber asli.
- JPEG PDF disimpan pada disk public yang sama di school/pdf-logos/<sha256-bytes-asli>.jpg. Tidak ada kolom atau migration baru. Kunci ini mengikat turunan dengan isi sumber, bukan nama file; file lama tidak ditimpa berdasarkan nama.
- Server memeriksa tipe MIME JPEG, ukuran, dimensi yang sama dengan sumber, hash sumber dan persetujuan. Pemeriksaan metadata/hash tidak membuktikan kesamaan visual secara kriptografis; preview admin menjadi pemeriksaan visual. Akses endpoint pembacaan sumber tetap melalui middleware admin existing, tanpa menerima URL/path arbitrary dari browser.
- JPEG turunan yang dipakai lintas request tidak dihapus pada rollback DB, untuk mencegah race upload bersamaan. Kegagalan penyimpanan profil dapat menyisakan turunan orphan yang aman; tidak ada garbage collection otomatis atau penghapusan data lama.
- PdfLogo membaca sumber/derivatif melalui Storage API. Dompdf menerima data URI JPEG langsung, tanpa imagecreatefromstring/imagepng/imagedestroy.
- Logo bawaan mempunyai JPEG paket yang dibuat menggunakan Canvas. Hash sumber PNG bawaan diikat dalam PdfLogo agar mengganti PNG statis nanti tidak diam-diam memakai JPEG stale. Jika aset bawaan diganti, regenerate JPEG dan perbarui hash setelah QA.
- PDF menggunakan kotak logo existing 82 x 82 px dalam kolom 105 px. Ukuran width/height dihitung proporsional agar logo nonpersegi tidak dipaksa menjadi persegi; tinggi area kop tetap dipertahankan. Tidak bergantung pada object-fit yang tidak didukung Dompdf terpasang.
- QrSvg memakai BaconQrCode 2.0.8 langsung: SVG, ukuran 150, margin 0, foreground/background RGB dan EyeFill yang sama, encoding/error correction default existing. Payload route spmb.verify/public_token dan alur SPMB tidak berubah.

## Dependency dan pemeriksaan platform

composer.json/lock konsisten: simplesoftwareio/simple-qrcode 4.2.0 dihapus; bacon/bacon-qr-code ^2.0 menjadi direct requirement, versi locked tetap 2.0.8. Perbandingan lock: **0 package baru, 0 perubahan versi, 1 removal**. XMLWriter menjadi requirement eksplisit untuk renderer SVG. Guard Vercel mengganti GD dengan XMLWriter; pdo_pgsql, mbstring, fileinfo, dom dan openssl tetap diperiksa. Pemeriksaan platform Composer tetap aktif.

Tidak ada command ignore-platform-reqs dan tidak ada config.platform yang memalsukan extension. Runtime komunitas upstream memiliki proses install sendiri; project tetap memverifikasi persyaratan asli melalui hook Composer dan guard. Tidak dilakukan upgrade massal atau pengubahan runtime/Node/database/Storage.

Composer saat pembaruan lock melaporkan **33 advisory pada 10 package existing**. Tidak diperbaiki dengan upgrade di luar lingkup ini. Menghapus GD tidak berarti semua risiko dependency production selesai.

## Pengujian yang benar-benar dijalankan

| Pemeriksaan | Hasil |
|---|---|
| Canvas PNG transparan 240 x 120 | Lulus; JPEG 2.807 bytes; PSNR tampilan 54,17 dB |
| Canvas PNG logo MIRU 4252 x 2835 | Lulus; JPEG 1.036.356 bytes; PSNR 71,54 dB |
| JPEG | Lulus; bytes asli dipakai; PSNR 100 sebagai penanda identik |
| WebP 240 x 120 | Lulus; JPEG 4.033 bytes; PSNR 50,80 dB |
| Gambar besar 3000 x 2000 | Lulus; JPEG 86.442 bytes; PSNR 54,19 dB |
| Batas 20 juta piksel | Ditolak sebagaimana diharapkan |
| UI persetujuan preview | Submit sebelum persetujuan diblokir; setelah persetujuan diizinkan pada DOM form terisolasi |
| Lima hasil konversi → controller preview/PDF tanpa GD | Semuanya lulus; PDF pendaftar dan admin mempunyai JPEG embedded, QR SVG dan proporsi logo sesuai |
| PHPUnit keseluruhan | **78 tests, 905 assertions lulus** |
| Tes proses PDF tanpa GD setelah penyesuaian pemuatan modul Linux/Windows | 6 tests, 40 assertions lulus |
| Live Supabase public/private + pasangan logo | **3 tests, 33 assertions lulus** |
| Composer validate --no-check-publish | Valid tanpa warning QR lama |
| Composer check-platform-reqs --no-dev dengan GD tidak dimuat | Lulus pada PHP lokal 8.0.30; modul non-GD yang diperlukan dimuat eksplisit |
| Vite production build | Lulus, 64 modules |
| spmb:check-pdf-logo --env=supabase | Logo aktif siap; pemeriksaan read-only, tidak membuat/mengubah record/file |
| Diff whitespace | Bersih |

PHPUnit menggunakan database SQLite tes terisolasi, bukan Supabase. Tes live Storage hanya membuat object dalam prefix acak _integration-checks, membaca hasil, lalu menghapus object tes itu; file production dan record profil tidak diubah. Tes mencakup website memakai path/bytes logo asli, kedua CMS, sumber admin-only, validasi pasangan, persetujuan, migrasi logo lama, rollback, proof, URL verifikasi, PDF admin/pendaftar dan logo cloud.

PSNR dihitung pada tampilan 82 px dibandingkan komposisi sumber di atas putih, bukan ukuran asli seluruh piksel. Ini bukti pada fixture yang diuji, bukan jaminan semua logo. JPEG bersifat lossy dan PDF tidak menyimpan alpha; website tetap memakai sumber asli dengan transparansinya. Preview admin tetap diperlukan. JPEG paket juga telah diperiksa visual, tetapi inspeksi visual PDF production online belum dilakukan.

Hanya satu template PDF ditemukan pada aplikasi: resources/views/pdf/proof.blade.php; dipakai PDF pendaftar dan admin. Keduanya diuji. Template preview pendaftar tetap memakai QR SVG.

## Batas lingkungan dan verifikasi

Shell agent masih memakai PHP XAMPP 8.0.30. Pembuktian tanpa GD memakai proses PHP -n dengan extension non-GD yang diperlukan, termasuk mbstring/fileinfo/PDO SQLite. Target Linux PHP 8.2.20 dan build Vercel sebenarnya belum dijalankan; deployment tidak diklaim berhasil.

Drive C: sempat penuh sehingga beberapa pengulangan Chrome gagal decode/encode/fetch dan satu harness macet. Tidak ada file pengguna dihapus. Hasil konversi browser yang berhasil disimpan dan kelima pasangan itu kemudian diuji terpisah pada controller/PDF tanpa GD. Ini tidak disamakan dengan full end-to-end CMS online di Vercel. Harness browser mempunyai timeout dan profile/temporary directory tes di D:. Suite PHP/build final lulus memakai temp proses tes di D:.

## Migrasi logo lama sebelum deployment

Tidak ada konversi/penghapusan otomatis terhadap logo production. Pemeriksaan logo aktif sekarang lulus (logo bawaan mempunyai pasangan paket). Untuk logo lama lain yang dipakai kemudian:

1. Buka /admin/profil atau /admin/pengaturan-website pada versi kode ini.
2. Klik SIAPKAN JPEG PDF LOGO SAAT INI. Sumber diambil melalui endpoint admin same-origin; browser tidak bergantung pada CORS bucket.
3. Periksa bentuk, ketajaman, warna, dan hasil latar putih. Jika tidak sesuai, jangan setujui atau simpan.
4. Centang persetujuan lalu simpan. File asli dan path profil dipertahankan; hanya JPEG turunan ditambahkan pada disk public.
5. Jalankan read-only preflight dan uji PDF pendaftar/admin.

```powershell
php artisan --env=supabase spmb:check-pdf-logo
```

Jika PNG/WebP aktif belum mempunyai pasangan JPEG, proses PDF memberikan pesan perlu penyiapan logo oleh admin, alih-alih mencetak logo yang salah. Karena itu preflight harus lulus sebelum deployment. Perubahan sumber logo di antara pengambilan dan penyimpanan akan ditolak karena hash tidak cocok; ambil ulang sumber.

Batas: sumber dan JPEG masing-masing maksimal 2 MB; maksimal 20 juta piksel; total seluruh file dalam satu request maksimal 3,5 MB agar mempunyai ruang terhadap batas POST/runtime. Jika total terlalu besar, simpan foto lain terpisah. Tidak ada resize/recompress otomatis yang menurunkan kualitas untuk memaksa lolos.

## Mengulang pemeriksaan

```powershell
# Browser Chrome/Edge + Node 22. Menulis fixture/hasil hanya di storage/app/testing.
node tests/Browser/pdf-logo.cjs
node tests/Browser/pdf-logo-proofs.cjs

# Seluruh suite (buat direktori temp terlebih dahulu bila belum tersedia).
php -d sys_temp_dir=storage/app/testing/pdf-logo-audit/temp vendor/phpunit/phpunit/phpunit
composer validate --no-check-publish
npm run build

# Live Storage: hanya bila sengaja hendak membuat dan membersihkan object tes.
$env:RUN_SUPABASE_STORAGE_TESTS = '1'
php -d sys_temp_dir=storage/app/testing/pdf-logo-audit/temp vendor/phpunit/phpunit/phpunit tests/Integration/SupabaseStorageTest.php
Remove-Item Env:RUN_SUPABASE_STORAGE_TESTS
```

Live logo test memerlukan fixture hasil browser di atas. Tidak mengubah .env atau menyertakan secret. Jangan menjalankan test suite aplikasi terhadap database produksi.

## Persetujuan dan langkah berikutnya

Perubahan tersedia untuk review lokal. Belum ada commit, push atau deployment. Sebelum mengizinkan commit/push, tinjau preview JPEG bawaan dan PDF hasil tes, pastikan logo resmi yang aktif sesuai, serta periksa variabel/extension pada build Vercel nanti. PHP GD tidak lagi diperlukan oleh jalur production yang diaudit, tetapi build dan smoke test online Vercel tetap merupakan gerbang verifikasi terpisah.
## Inventaris working tree untuk review

File tracked yang diubah:

- `app/Http/Controllers/Admin/AdminWebsiteSettingController.php`
- `app/Http/Controllers/Admin/SchoolProfileController.php`
- `app/Http/Controllers/ApplicationPrintController.php`
- `app/Http/Requests/WebsiteSettingRequest.php`
- `composer.json`
- `composer.lock`
- `resources/js/app.js`
- `resources/views/admin/profile.blade.php`
- `resources/views/admin/settings/edit.blade.php`
- `resources/views/pdf/proof.blade.php`
- `routes/web.php`
- `scripts/vercel-check.php`
- `tests/Feature/CmsStageTwoTest.php`
- `tests/Feature/DeploymentCompatibilityTest.php`
- `tests/Feature/SpmbRegressionTest.php`
- `tests/Integration/SupabaseStorageTest.php`
- `tests/Unit/VercelBuildDiagnosticsTest.php`

File baru/untracked (termasuk dua dokumen audit dan tes audit yang sudah dibawa dari pekerjaan sebelumnya):

- `app/Console/Commands/CheckPdfLogo.php`
- `app/Support/PdfLogo.php`
- `app/Support/QrSvg.php`
- `docs/AUDIT_PDF_LOGO_TANPA_GD_2026-11-14.md`
- `docs/AUDIT_QR_GD_2026-11-14.md`
- `docs/GDLESS_LOGO_QA_2026-11-15.json`
- `docs/VERCEL_GDLESS_LOGO_2026-11-15.md`
- `public/images/logo-miru-pdf.jpg`
- `resources/js/pdf-logo.js`
- `resources/views/admin/partials/pdf-logo.blade.php`
- `tests/Browser/pdf-logo-proofs.cjs`
- `tests/Browser/pdf-logo.cjs`
- `tests/Feature/PdfLogoUploadTest.php`
- `tests/Support/gdless-application-proof.php`
- `tests/Support/pdf-logo-probe.php`
- `tests/Unit/GdlessApplicationProofTest.php`
- `tests/Unit/PdfLogoCompatibilityTest.php`

## Review independen

Review menemukan race pada rollback turunan berhash bersama; sudah diperbaiki dengan tidak menghapus turunan shared saat rollback, disertai tes kegagalan penyimpanan DB. Review juga menandai fetch sumber lama yang dapat menimpa pilihan baru; sudah ditutup dengan generation token sebelum fetch dan pemeriksaan token sebelum memakai respons. Reviewer tidak memverifikasi ulang file revisi akhir karena tool terkendala disk penuh. Pengujian akhir yang dilaporkan di atas dijalankan oleh implementer; tidak dinyatakan sebagai verifikasi independen penuh.