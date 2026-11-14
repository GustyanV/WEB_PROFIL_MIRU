# Finalisasi Storage Supabase — 14 November 2026

Branch: `deploy-vercel`. Tidak ada deployment, commit/push, perubahan branch main, migration, reset database, atau perubahan credential aktif.

## Penyebab dan perbaikan

Reproduksi request mock dengan AWS SDK PHP 3.337.3 menunjukkan `PutObject` mengubah endpoint berpath `/storage/v1/s3` menjadi `/storage/v1/s3/storage/v1/s3/media/...`. Ini berasal dari penyusunan path pada serializer SDK, bukan bucket yang belum dibuat. Endpoint environment tidak dipotong menjadi hostname dan tidak diganti dengan URL REST publik.

`AppServiceProvider` mendaftarkan pembungkus driver S3 melalui API extension Laravel. `S3PathEndpoint` memakai adapter Laravel/Flysystem existing, lalu menghapus satu prefix base path yang terduplikasi pada tahap build, sebelum signature dihitung. Koreksi berlaku hanya bila prefix benar-benar berulang. Key, query, header dan payload tetap dipertahankan. Tidak ada vendor/dependency yang diubah. Endpoint tanpa base path tetap menggunakan perilaku normal.

Disk tetap `public` → bucket `media` dan `spmb` → bucket `documents`, sesuai environment existing. Tidak ada perubahan bucket policy atau penambahan URL publik untuk disk private.

## Bukti pengujian

- Reproduksi sebelum perbaikan: gagal pada path PutObject yang berulang.
- Regresi request mock sesudah perbaikan: **1 test, 10 assertions lulus**, meliputi PutObject, HeadObject, DeleteObject dan presigned URL pada kedua disk.
- Live `.env.supabase`: **2 tests, 24 assertions lulus**. Kedua bucket berhasil upload, exists dan authenticated get; hanya file acak di `_integration-checks/` milik pengujian yang dihapus, kemudian exists dipastikan false.
- `media`: anonymous public REST URL dan URL yang dibuat disk mengembalikan HTTP 200 dengan isi yang sesuai.
- `documents`: anonymous public REST URL ditolak (non-200 pada status error yang diizinkan tes), isi file tidak dikembalikan. Bukti memakai objek yang benar-benar ada, bukan URL file kosong.
- Seluruh suite default PHPUnit: **63 tests, 827 assertions lulus** pada PHP lokal 8.0.30.
- Menjalankan direktori Integration tanpa opt-in: **6 skipped** (4 PostgreSQL dan 2 Storage). Ini bukan hasil lulus tes live; hasil Storage live di atas dijalankan terpisah dengan opt-in. PostgreSQL integration belum dijalankan dalam pekerjaan ini.
- `composer check-platform-reqs --no-dev`: lulus pada PHP lokal.
- `composer validate --no-check-publish`: valid, dengan warning existing constraint exact QR Code 4.2.
- Constraint require/conflict PHP pada **124 paket terkunci**: tidak ada konflik dengan PHP 8.2.0. Pemeriksaan ini tidak menggantikan tes runtime 8.2 sebenarnya.

Ulangi tes live secara sengaja dari root project (PowerShell):

```powershell
$env:RUN_SUPABASE_STORAGE_TESTS = '1'
php vendor/phpunit/phpunit/phpunit tests/Integration/SupabaseStorageTest.php
Remove-Item Env:RUN_SUPABASE_STORAGE_TESTS
```

File `.env.supabase` harus tersedia secara lokal dan tidak dilacak Git. Tes tidak menjalankan query database maupun migration. Tes hanya memakai nama bucket `media` dan `documents`, menggunakan object key acak, serta membersihkan object yang dibuatnya dalam blok finally.

Suite aplikasi biasa:

```powershell
php vendor/phpunit/phpunit/phpunit
```

## Kompatibilitas dan batas verifikasi

Runtime Vercel tetap `vercel-php@0.6.2` (PHP 8.2); Laravel tidak di-upgrade major, composer.json dan composer.lock tidak berubah. PHP lokal 8.0.30 dapat menjalankan dependency terpasang, tetapi AWS SDK menyatakan PHP 8.0 deprecated; gunakan PHP 8.2 untuk verifikasi setara target deployment. Hook Composer Vercel existing tetap memeriksa platform requirements dan extension pada runtime build. Build/runtime Vercel sebenarnya belum dijalankan dan tidak diklaim berhasil. Tidak ada perubahan frontend sehingga build Vite tidak diulang dalam pekerjaan Storage ini.

## Git dan credential

- Branch aktif `deploy-vercel`; diff whitespace bersih.
- `.env` dan `.env.supabase` diabaikan Git dan tidak dilacak.
- Ditemukan nilai AWS access key/secret terisi dalam `.env.example` yang sudah dilacak Git. Kedua nilai dikosongkan pada working tree; credential aktif tidak diubah.
- Scan file teks tracked terhadap credential aktif dari environment tidak menemukan kecocokan sesudah pembersihan.
- **Rotasi credential yang pernah tercatat di `.env.example` sebelum push.** Mengosongkan file pada commit baru tidak menghapus nilai dari riwayat Git. Jika riwayat hendak dibersihkan, koordinasikan langkah terpisah; pekerjaan ini tidak melakukan history rewrite atau force-push.

File perubahan untuk review/commit:

1. `.env.example`
2. `app/Providers/AppServiceProvider.php`
3. `app/Support/S3PathEndpoint.php`
4. `tests/Feature/S3EndpointTest.php`
5. `tests/Integration/SupabaseStorageTest.php`
6. `docs/FINALISASI_SUPABASE_2026-11-14.md`

Setelah credential dirotasi dan diff direview:

```powershell
git branch --show-current
git diff --check
git add .env.example app/Providers/AppServiceProvider.php app/Support/S3PathEndpoint.php tests/Feature/S3EndpointTest.php tests/Integration/SupabaseStorageTest.php docs/FINALISASI_SUPABASE_2026-11-14.md
git diff --cached --stat
git commit -m "Fix Supabase S3 endpoint paths and verify storage access"
git push origin deploy-vercel
```

Tidak ada commit atau push yang dijalankan otomatis. Jika branch sudah terhubung dengan Vercel Git integration, push dapat memicu deployment; periksa pengaturan Vercel sebelum menjalankan push.

Referensi: [Supabase S3 authentication](https://supabase.com/docs/guides/storage/s3/authentication), [AWS SDK custom path issue](https://github.com/aws/aws-sdk-php/issues/2785). Kesimpulan penyebab pada project ini didasarkan pada request mock dan pengujian live di atas.