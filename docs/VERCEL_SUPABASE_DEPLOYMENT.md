# Deployment GitHub → Vercel + Supabase

Branch kerja: deploy-vercel. Jangan merge atau mengubah main untuk deployment ini.

Status: persiapan kode, konfigurasi, dan pemeriksaan lokal tersedia. Belum ada project Supabase/Vercel yang dibuat, credential cloud yang diisi, migration Supabase yang dijalankan, atau deployment remote yang diverifikasi.

## 1. Supabase database

Ikuti [SUPABASE_SETUP.md](SUPABASE_SETUP.md): buat project, simpan password, salin parameter SQL dari Connect, aktifkan SSL, dan tentukan session pooler (5432) atau transaction pooler (6543, emulated prepares).

## 2. Supabase Storage

Ikuti [SUPABASE_STORAGE_SETUP.md](SUPABASE_STORAGE_SETUP.md): buat bucket media public dan dokumen private, buat credential S3 server, lalu isi endpoint, region, bucket serta URL REST publik.

## 3. Environment production

Gunakan .env.production.example sebagai daftar lengkap. .env.example tetap ramah development MySQL; .env lokal tidak diubah.

Wajib diisi sendiri:

- APP_KEY: key production stabil, dibuat sekali melalui file env deployment terpisah.
- APP_URL: domain HTTPS production yang benar.
- DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, DB_SSLMODE.
- DB_EMULATE_PREPARES=true bila memakai transaction pooler.
- AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_ENDPOINT.
- AWS_BUCKET, AWS_PRIVATE_BUCKET, AWS_URL.
- MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION, MAIL_FROM_ADDRESS untuk email reset password.
- SCHOOL_NAME serta INITIAL_SUPER_ADMIN_* saat bootstrap pertama saja.

Gunakan APP_ENV=production, APP_DEBUG=false, SESSION_DRIVER=database, CACHE_DRIVER=database, QUEUE_CONNECTION=sync, SESSION_SECURE_COOKIE=true, LOG_CHANNEL=stderr, FILESYSTEM_DISK=s3, PUBLIC_FILESYSTEM_DRIVER=s3, SPMB_FILESYSTEM_DRIVER=s3.

Jangan isi secret dalam vercel.json atau VITE_*. Jangan memakai email/password demo development. Jika environment Vercel memiliki DATABASE_URL lama, hapus supaya tidak menimpa DB_*.

## 4. Migration dan akun awal

Dengan credential asli pada .env.deploy yang diabaikan Git, jalankan langkah migration dan ProductionSeeder di SUPABASE_SETUP.md. Gunakan session/direct connection untuk migration, bukan transaction pooler.

Jangan menjalankan migrate saat build Vercel atau melalui route web. MySQL lokal tidak di-reset atau otomatis disalin ke production. Pemindahan data dan file existing adalah tahap terpisah setelah backup.

## 5. Pemeriksaan dan push branch ke GitHub

```powershell
git switch deploy-vercel
git status
composer validate
php artisan test
php artisan migrate:status
php artisan route:list
npm ci
npm run build
git diff --check
git diff --stat
```

Periksa daftar file; .env, dump, private uploads, vendor, dan node_modules tidak boleh ikut commit. Setelah review, commit file deployment pada branch ini dan push:

```powershell
git add .env.example .env.production.example .gitignore .vercelignore vercel.json api app config database/migrations scripts tests/Feature/DeploymentCompatibilityTest.php tests/Integration .github/workflows/deployment-compatibility.yml composer.json composer.lock package.json package-lock.json vite.config.js docs
git diff --cached --stat
git commit -m "deploy: prepare Laravel for Vercel and Supabase"
git push -u origin deploy-vercel
```

Jangan push sebelum meninjau staged diff. Perintah di atas adalah langkah manual, bukan bukti commit/push sudah dilakukan. Jalankan workflow Deployment compatibility dan periksa job PostgreSQL benar-benar lulus.

## 6. Import GitHub ke Vercel

1. Masuk Vercel → Add New Project → import repository GitHub project ini.
2. Root Directory: root repository yang berisi composer.json dan vercel.json.
3. Framework Preset: Other. Node.js: 22.x.
4. Build Command: npm run build; Install Command: npm ci; Output Directory: public (sudah pada vercel.json).
5. Settings → Git: Production Branch harus deploy-vercel. Jika import pertama memilih main, jangan anggap hasilnya deployment branch yang benar; ubah branch dan deploy ulang deploy-vercel.
6. Masukkan Environment Variables dari template, dengan nilai asli. Scope Production untuk production. Gunakan database/bucket terpisah untuk Preview jika preview dapat menulis data.
7. Pastikan APP_URL sesuai deployment/domain tujuan. Setelah perubahan env, redeploy.
8. Deploy commit dari deploy-vercel. Tidak ada token Vercel yang diperlukan di repository untuk Git integration. Token hanya diperlukan jika memilih CLI/automation dari mesin lain.

## 7. Runtime dan routing

vercel.json memakai runtime komunitas nyata vercel-php@0.6.2, yang didokumentasikan menyediakan PHP 8.2. Laravel tetap seri 9 dan requirement lokal ^8.0.2. Versi runtime dipin; tidak memakai nama runtime rekaan atau latest.

Runtime diinspeksi dari paket npm: menggunakan Node runtime hasil deteksi Vercel, menginstal Composer --no-dev, dan mendukung script Composer vercel. Paket upstream melakukan install dengan --ignore-platform-reqs; project menambahkan pemeriksaan platform/extension eksplisit saat build, sehingga hasil install tersebut tidak dianggap bukti kompatibilitas.

Sumber: [runtime PHP komunitas](https://github.com/vercel-community/php), [daftar runtime Vercel](https://vercel.com/docs/functions/runtimes).

- api/index.php menjalankan Laravel kernel.
- public adalah output statis: /build/* dan /images/* dilayani langsung.
- Routing fallback mengirim URL aplikasi ke function, bukan public/index.php sebagai file statis.
- /storage dan jalur .env/.git ditolak; upload production berasal dari cloud.
- Path .php diteruskan ke Laravel sehingga source PHP tidak disajikan sebagai static asset.
- Cache manifest, Blade, font dan file sementara memakai direktori temporary runtime.
- Session/cache aplikasi memakai database, tidak bergantung pada /tmp.
- Proxy loopback runtime dipercaya khusus Vercel untuk protokol HTTPS dan IP klien; forwarded host tidak dipercaya. Konfigurasi lokal tidak berubah.
- Tidak perlu storage:link di Vercel.
- public/index.php lokal tetap dipertahankan untuk php artisan serve/server tradisional.

Runtime komunitas bukan PHP runtime resmi Vercel. Build dan request pada deployment nyata belum dapat diuji tanpa akun/project. Jangan menyatakan siap production hanya karena konfigurasi atau build frontend lolos. Guard juga menolak env wajib yang kosong, bucket publik/privat yang sama, dan konfigurasi session/cache/upload yang tidak sesuai serverless. Jika versi runtime tidak berjalan pada image Node Vercel saat ini, hentikan rollout dan tinjau runtime/image berdasarkan log; jangan menonaktifkan pemeriksaan platform.

## 8. QR, PDF, dan export

APP_URL HTTPS menjadi root URL production sehingga QR dan reset password tidak memakai localhost. Pratinjau dan PDF tetap memakai controller existing. Logo cloud dibaca lewat Storage dan dikonversi ke data URI; font/temp cache writable pada /tmp.

CSV memakai streamed response tanpa file persisten. Runtime/platform tetap dapat membatasi ukuran dan durasi response; export besar perlu diuji dengan volume nyata dan filter periode/gelombang. PDF juga harus diuji pada batas memory/durasi function.

## 9. Smoke test setelah deploy

- Homepage, profil, daftar guru/program, berita, kegiatan, galeri, kontak dan media sosial.
- Login admin dan pendaftar, logout, reset password melalui email nyata.
- Uji CSRF dan session: refresh, berpindah halaman, dan request ke instance lain tetap mempertahankan login.
- CRUD CMS dan upload hero, logo/favicon, guru, kegiatan/galeri.
- Upload dokumen pendaftar: private dan hanya pemilik/admin yang bisa membuka.
- Alur SPMB draft → submit → verifikasi/revisi → diterima/ditolak.
- PDF, logo, QR dari HP, serta export CSV.
- Redeploy lalu pastikan data, login, dan upload tetap tersedia.
- Pastikan tidak ada localhost, URL Windows, mixed content, dan error 500.

## 10. Troubleshooting

| Gejala | Pemeriksaan |
| --- | --- |
| Could not find driver | pdo_pgsql pada runtime/CLI; build guard harus lulus |
| Invalid SSL/connection | DB_SSLMODE, host/port/username pooler, password, dan jaringan IPv4/IPv6 |
| Prepared statement error | Untuk transaction pooler gunakan DB_EMULATE_PREPARES=true; migration gunakan session/direct |
| Login hilang/419 | Tabel sessions sudah dimigrate, APP_KEY stabil, HTTPS/secure cookie dan domain APP_URL benar |
| Permission denied/read-only | Gunakan api/index.php; cek path temporary dan manifest cache |
| Gambar tidak tampil | AWS_URL REST publik, nama bucket, public status, object key, dan file benar-benar telah ditransfer |
| Upload 403 | Credential S3 server, bucket/prefix, serta pengaturan Storage; jangan jadikan dokumen public |
| Upload 413 | Total multipart melewati batas function; gunakan batch kecil |
| Missing manifest | Build Vite/public/build belum ikut artefak; jangan mengarahkan output ke folder lain |
| PDO UUID syntax | Token QR harus UUID; validasi aplikasi menolak token tidak valid sebelum query |
| PHP runtime build gagal | Periksa versi runtime, image Node, extension dan composer platform guard |
| Function timeout/PDF besar | Uji volume, gambar, cold start, koneksi pooler dan batas deployment |

## 11. Rollback

Simpan deployment sebelumnya di Vercel. Jika rollout bermasalah, gunakan rollback/redeploy commit terakhir yang terbukti berjalan. Jangan membalik migration otomatis, mengganti APP_KEY, menghapus bucket, atau mengembalikan dump database tanpa prosedur pemulihan terpisah. Rollback kode tidak membalik perubahan data.

## 12. Risiko dan hal yang masih manual

- Credential, pembuatan project/bucket, migration Supabase, import data, transfer file, push GitHub, import Vercel, dan smoke test remote masih harus diselesaikan pemilik akun.
- PDO PostgreSQL lokal belum aktif saat audit, sehingga belum ada uji PostgreSQL hidup di mesin ini. Kompilasi SQL bukan uji koneksi/database nyata; workflow CI disediakan.
- Runtime komunitas dan dependency lama masih memiliki risiko kompatibilitas/keamanan. Composer audit masih menemukan advisory; tidak melakukan upgrade major atau menutup temuan dengan ignore.
- Batas payload/durasi, streaming runtime, email SMTP dan akses private bucket harus diuji setelah credential tersedia.

## Hasil audit lokal

Lihat [audit deployment](DEPLOYMENT_AUDIT_2026-11-09.md) untuk hasil tes, migration pending, dependency, dan batas verifikasi.
