# Investigasi kegagalan platform Vercel — 14 November 2026

Baseline: branch `deploy-vercel`, commit `4016a07`, working tree bersih sebelum investigasi.

## Kesimpulan yang terbukti

Log build yang diberikan menampilkan extension versi 8.2.20 dan baris `php 8.2.20`: Composer pada build tersebut benar-benar memakai PHP 8.2.20. Versi Herd 8.4.21 di komputer pengguna bukan versi Composer build tersebut. Source paket npm vercel-php 0.6.2 juga memprioritaskan binary dari @libphp/amazon-linux-2-v82 pada PATH saat menjalankan Composer install dan hook vercel. Artefak library 0.0.14 yang diperiksa memuat penanda binary PHP 8.2.20.

Audit seluruh 124 package locked (production dan development), termasuk require/conflict PHP, tidak menemukan konflik dengan PHP 8.2.20. PHP 8.4.21 ditolak nette/schema v1.2.5 (`7.1 - 8.3`); ini masalah lokal bila Composer dijalankan menggunakan Herd 8.4.21, bukan penjelasan untuk kegagalan build yang log-nya menunjukkan 8.2.20. nette/utils v4.0.10 dan league/config v1.2.0 tidak memerlukan upgrade untuk target 8.2.

**Persyaratan spesifik yang menyebabkan exit code 2 belum terbukti.** Potongan log yang diterima baru berisi baris success dan bagian cloning; baris failed/missing serta kolom status akhir php belum tersedia. Tidak dilakukan perubahan extension atau dependency berdasarkan dugaan.

## Ekstensi dan fase build

Production lock memerlukan ctype, dom, fileinfo, filter, gd, hash, iconv, json, libxml, mbstring, openssl, pcre, session, simplexml dan tokenizer. Sebagian dapat dipenuhi package polyfill menurut Composer. Guard project tetap memerlukan modul native pdo_pgsql, gd, mbstring, fileinfo, dom dan openssl; polyfill mbstring yang tampil success tidak membuktikan modul native mbstring aktif.

Library runtime menyertakan modul pdo_pgsql, dom, fileinfo, mbstring dan lainnya; tersedianya file modul tidak membuktikan modul sudah dimuat saat Composer berjalan. Runtime menambahkan build.ini (extension_dir) melalui PHP_INI_SCAN_DIR. api/php.ini baru digabung ke konfigurasi function setelah hook Composer; mengedit api/php.ini saja tidak menjamin perbaikan extension pada fase Composer.

Status keenam modul native di build gagal belum dapat dipastikan dari potongan log. Pada PHP yang tersedia bagi agent (XAMPP 8.0.30), keenamnya loaded; ini bukan pengujian runtime Linux Vercel atau Herd 8.4.21.

## Perubahan terkontrol

- composer.json: menambahkan `@php scripts/vercel-check.php --diagnostics` sebelum dua pemeriksaan existing. Tidak menghapus check-platform-reqs, tidak menambahkan ignore requirement.
- scripts/vercel-check.php: mode diagnostik eksplisit mencetak PHP_VERSION, PHP_BINARY, file INI loaded/scanned dan status enam extension. Tidak membaca .env atau mencetak credential. Mode normal tetap menolak versi/extension/environment yang tidak memenuhi syarat.
- tests/Unit/VercelBuildDiagnosticsTest.php: memverifikasi diagnostik tanpa bocor secret, urutan pemeriksaan Composer, serta penolakan jika extension hilang.
- JSON pendamping mencatat seluruh require/conflict/provide platform per package locked, termasuk scope development.

config.platform.php **tidak ditambahkan**. Lock saat ini sudah cocok dengan 8.2.20; setting itu mengarahkan dependency resolution, tidak mengganti PHP aktual, dan diabaikan check-platform-reqs. Jika kebijakan resolution target ditambahkan kelak, 8.2.20 sesuai binary yang terbukti pada log ini, tetapi itu bukan perbaikan untuk error sekarang. composer.lock dan seluruh versi dependency tetap utuh.

## Audit environment guard

Nama yang wajib terisi: APP_KEY, APP_URL, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_ENDPOINT, AWS_BUCKET, AWS_PRIVATE_BUCKET, AWS_URL.

Nilai policy yang diperiksa: APP_ENV=production, DB_CONNECTION=pgsql, SESSION_DRIVER=database, CACHE_DRIVER=database, PUBLIC_FILESYSTEM_DRIVER=s3, SPMB_FILESYSTEM_DRIVER=s3. APP_DEBUG harus false; APP_URL/AWS_ENDPOINT/AWS_URL HTTPS dan bukan localhost; bucket public/private harus berbeda; DB_SSLMODE require/verify-ca/verify-full; port 6543 memerlukan DB_EMULATE_PREPARES=true; manifest Vite harus ada. Guard existing menerima APP_DEBUG yang tidak terisi sebagai false; untuk deployment isi eksplisit false. APP_KEY/credential/database tidak diubah. Nilai environment remote Vercel belum dapat diperiksa dari workspace ini. Error terjadi pada check-platform-reqs, sebelum validasi environment tersebut dijalankan.

## Hasil verifikasi

- PHPUnit Unit saja: **4 tests, 14 assertions lulus** (3 diagnostik + 1 existing). Tidak menjalankan suite Feature karena suite tersebut menjalankan migration SQLite; tugas ini melarang migration.
- composer validate --no-check-publish: valid; warning existing constraint exact QR Code 4.2.
- composer check-platform-reqs --no-dev: lulus pada PHP XAMPP 8.0.30 yang tersedia bagi agent.
- composer run vercel pada PHP lokal tersebut: diagnostik tampil, Composer platform check lulus, lalu guard menolak PHP 8.0.30 dengan exit 1 sebagaimana diharapkan. Ini membuktikan guard tidak dilonggarkan; bukan reproduksi exit 2 Vercel.
- Guard dengan php -n: ditolak karena pdo_pgsql tidak termuat; diuji otomatis.
- Tidak ada migration, database reset, upgrade, perubahan APP_KEY/credential, push atau deployment.

## Bukti berikutnya yang diperlukan

Salin tabel lengkap check-platform-reqs mulai ext-ctype sampai php, termasuk status failed/missing dan pesan detailnya. Diagnostik baru akan memberikan versi/INI/status native pada build berikutnya jika pengguna memilih menjalankannya sendiri. Jangan menyimpulkan root cause dari exit code 2 saja. Jangan mengklaim perbaikan deployment atau deployment berhasil sebelum bukti tersebut dan tes build tersedia.

Perintah commit/push opsional untuk **diagnostik, bukan fix deployment terverifikasi**:

```powershell
git diff --check
git add composer.json scripts/vercel-check.php tests/Unit/VercelBuildDiagnosticsTest.php docs/VERCEL_PLATFORM_INVESTIGATION_2026-11-14.md docs/VERCEL_PLATFORM_REQUIREMENTS_2026-11-14.json
git diff --cached --stat
git commit -m "Add safe diagnostics for Vercel platform checks"
# Jalankan hanya bila memang ingin mengirim perubahan; Git integration dapat memicu deployment:
git push origin deploy-vercel
```

Referensi Composer: https://getcomposer.org/doc/03-cli.md#check-platform-reqs dan https://getcomposer.org/doc/06-config.md#platform. Source runtime yang dipakai investigasi adalah artefak npm versi 0.6.2 tersimpan lokal; source master upstream sudah dapat berbeda versinya.