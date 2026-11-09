# Audit Persiapan Deployment — 9 November 2026

Branch: deploy-vercel. Status: perubahan lokal disiapkan dan diuji; belum deploy ke Supabase/Vercel. Tidak ada merge ke main, push, reset database, atau credential production yang dibuat/disimpan dalam source.

## Perubahan kompatibilitas

- Migration memakai Schema Builder. Tidak ditemukan ENGINE=, FIND_IN_SET, DATE_FORMAT, GROUP_CONCAT, IFNULL, JSON_EXTRACT, enum MySQL atau SQL raw backtick pada kode aplikasi/migration.
- Raw SQL existing LOWER(title) menggunakan parameter binding dan kompatibel PostgreSQL.
- Modifier after() hanya mengatur posisi kolom MySQL; grammar PostgreSQL mengabaikannya. Tidak perlu menulis ulang migration existing.
- UUID public_token membutuhkan validasi token sebelum query; token tidak valid sekarang dikembalikan sebagai 404.
- Pencarian admin memakai ILIKE pada PostgreSQL dan tetap LIKE pada MySQL/SQLite.
- DB_SSLMODE dan DB_EMULATE_PREPARES configurable.
- Migration sessions/cache/cache_locks baru disediakan tanpa mengubah tabel/data existing.
- Disk public dan spmb existing bisa memakai S3 dengan bucket terpisah. Object key database tidak diubah.
- Hero/logo/favicon/gambar CMS menggunakan URL disk, bukan asumsi /storage lokal.
- Logo PDF dibaca melalui Storage; QR menggunakan route helper dan root APP_URL HTTPS production.
- Entry Vercel memakai temporary storage untuk Blade/font/manifest, memaksa debug off, serta memakai build Vite walaupun public/hot development ada.
- Header proxy dipercaya hanya pada loopback runtime Vercel. Konfigurasi lokal tetap sama.
- Vite publicDir ditetapkan eksplisit dengan copyPublicDir=false; warning hero-pattern.png terselesaikan.
- Node 22 dipin. Binary esbuild Linux ternyata sudah ada sebagai dependency nested; tidak perlu mengganti versi package npm.
- Adapter S3 menambah empat package baru. Perbandingan composer.lock memastikan tidak ada versi package existing yang berubah.

## Hasil pemeriksaan

| Pemeriksaan | Hasil |
| --- | --- |
| php artisan test | 62 passed |
| DeploymentCompatibilityTest | 8 passed, termasuk kompilasi seluruh migration PostgreSQL |
| PostgresDeploymentTest | 4 skipped; belum ada pdo_pgsql/koneksi PostgreSQL uji di mesin ini |
| php artisan migrate:status | MySQL dapat diakses; 15 migration existing Ran, 2 migration baru Pending |
| Homepage MySQL existing | HTTP 200; session file, upload local tetap digunakan |
| php artisan route:list | 108 routes |
| composer validate | Valid; warning existing constraint QRCode 4.2 tetap dipertahankan |
| npm run build | Berhasil, manifest dan asset tersedia; warning hero-pattern hilang |
| Bootstrap serverless | Temporary storage, views/font writable, public path benar, debug false, disk S3 dan session/cache database |
| Entry API, SQLite terpisah | Login HTTP 200, logo dan URL aset build tampil; tidak bergantung pada Vite dev server |
| git diff --check | Bersih |
| Pemeriksaan ignore | .env/.env.deploy, private upload, vendor, node_modules diabaikan |
| Koneksi/upload Supabase nyata | Belum diuji; credential belum ada |
| Build/deploy Vercel nyata | Belum dijalankan; project/akses belum tersedia |
| GitHub PostgreSQL workflow | Disiapkan, belum dijalankan remote |

Tes SQLite serta kompilasi grammar tidak membuktikan migration/query PostgreSQL hidup sudah berhasil. Workflow PostgreSQL test terpisah disediakan agar pemilik akun bisa membuktikannya setelah push.

## Runtime

Paket runtime nyata vercel-php@0.6.2 dan library PHP native diperiksa dari registry npm. Runtime menjalankan PHP 8.2, memakai Node hasil deteksi Vercel, serta menginstal Composer tanpa dev/scripts dan dengan ignore-platform-reqs. Hook Composer vercel ditambahkan untuk memeriksa platform, extension dan konfigurasi asli sebelum deploy. Library memiliki modul PDO PostgreSQL dan simbol fungsi GD pada binary PHP; pemeriksaan aktual extension tetap dijalankan oleh guard build.

Runtime adalah community-maintained; kompatibilitas final pada image Vercel tidak boleh diklaim sebelum deployment nyata berhasil.

## Risiko tersisa

- Composer audit: 32 entri advisory pada 10 package, termasuk dua entri SDK AWS yang terkunci pada versi kompatibel PHP 8.0 lokal. Tidak melakukan upgrade major atau menyembunyikan advisory.
- Pemeriksaan npm pada lockfile juga masih melaporkan lima advisory. Tidak menjalankan npm audit fix/force atau mengganti versi dependency existing.
- Vercel membatasi payload/durasi/function size. Batch galeri, PDF besar dan export perlu uji volume nyata; streamed response Laravel belum membuktikan streaming tanpa buffering pada runtime.
- SMTP, session antar-instance, akses bucket privat, dan persistensi file setelah redeploy harus diuji dengan credential sebenarnya.
- Migration tidak mengimpor data MySQL atau memindahkan file ke cloud. Sumber lokal dipertahankan.
- Migration production tidak dijalankan dari request atau build.

## Langkah pemilik akun

1. Buat Supabase project, ambil credential SQL, dan buat bucket media public/dokumen private.
2. Buat credential S3 server; isi .env.deploy dan Environment Variables Vercel secara privat.
3. Aktifkan pdo_pgsql pada CLI, verifikasi tujuan, lalu migration dan ProductionSeeder sesuai panduan. Jangan mengganti .env lokal.
4. Review diff, commit/push deploy-vercel dan jalankan workflow PostgreSQL.
5. Import repo GitHub ke Vercel, pilih branch deploy-vercel/Node22/Other, isi APP_KEY, APP_URL dan semua env asli.
6. Deploy, periksa preflight/build, kemudian lakukan smoke test lengkap dan siapkan rollback.

Panduan: [Supabase SQL](SUPABASE_SETUP.md), [Storage](SUPABASE_STORAGE_SETUP.md), [deployment lengkap](VERCEL_SUPABASE_DEPLOYMENT.md), [checklist](DEPLOYMENT_CHECKLIST.md).

## Inventaris perubahan

18 file baru:

- .env.production.example
- .github/workflows/deployment-compatibility.yml
- .vercelignore
- api/index.php
- api/php.ini
- app/Support/DatabaseSearch.php
- app/Support/VercelRuntime.php
- database/migrations/2026_11_09_000001_create_sessions_table.php
- database/migrations/2026_11_09_000002_create_cache_tables.php
- docs/DEPLOYMENT_AUDIT_2026-11-09.md
- docs/DEPLOYMENT_CHECKLIST.md
- docs/SUPABASE_SETUP.md
- docs/SUPABASE_STORAGE_SETUP.md
- docs/VERCEL_SUPABASE_DEPLOYMENT.md
- scripts/vercel-check.php
- tests/Feature/DeploymentCompatibilityTest.php
- tests/Integration/PostgresDeploymentTest.php
- vercel.json

21 file diubah:

- .env.example
- .gitignore
- README.md
- app/Http/Controllers/Admin/AdminActivityController.php
- app/Http/Controllers/Admin/AdminUserController.php
- app/Http/Controllers/Admin/ApplicationController.php
- app/Http/Controllers/Admin/ContentController.php
- app/Http/Controllers/ApplicationPrintController.php
- app/Http/Middleware/TrustProxies.php
- app/Models/HeroBanner.php
- app/Models/SchoolProfile.php
- app/Providers/AppServiceProvider.php
- app/Support/SchoolContent.php
- composer.json
- composer.lock
- config/app.php
- config/database.php
- config/filesystems.php
- package-lock.json
- package.json
- vite.config.js

Ringkasan diff file tracked: 21 files changed, 391 insertions(+), 91 deletions(-). Statistik git diff ini belum menghitung isi 18 file baru. Perubahan belum di-commit atau di-push.
