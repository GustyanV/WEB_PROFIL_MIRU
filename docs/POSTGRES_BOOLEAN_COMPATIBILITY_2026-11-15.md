# Kompatibilitas Boolean PostgreSQL — 15 November 2026

Branch: deploy-vercel. Laravel locked v9.52.22. Target Vercel PHP 8.2.20. PHP CLI yang benar-benar dipakai pengujian lokal: 8.0.30. Tidak ada commit, push atau deployment.

## Akar masalah

Illuminate\Database\PostgresConnection pada versi ini mewarisi Connection::prepareBindings(). Method tersebut mengubah true/false menjadi integer 1/0; bindValues() lalu memilih PDO::PARAM_INT. Dengan prepared statement emulated, integer masuk ke SQL sebagai literal numerik. PostgreSQL tidak menyediakan operator boolean = integer, sehingga menghasilkan SQLSTATE 42883. Penulisan literal integer ke kolom Boolean juga dapat menghasilkan 42804.

Nuansa penting: native parameter preparation dapat menginfer tipe parameter dari konteks SQL. Tidak tepat menyimpulkan bahwa setiap PDO parameter integer selalu gagal pada setiap query native. Reproduksi legacy yang diuji memakai emulated prepares; solusi string Boolean telah diuji untuk emulated maupun native unnamed parameters.

## Audit binding dan penulisan

Sembilan kolom Boolean aplikasi:

| Tabel | Kolom/model |
|---|---|
| users | is_active / User |
| admission_periods | is_active / AdmissionPeriod |
| admission_waves | is_active / AdmissionWave |
| teachers | is_active / Teacher |
| programs | is_active / Program |
| extracurriculars | is_active / Extracurricular |
| hero_banners | is_active / HeroBanner |
| social_media | is_active / SocialMedia |
| activity_images | is_cover / ActivityImage |

Pembacaan Boolean terdapat pada PublicContentController, ApplicantController, SocialMedia::active(), listing guru/program/ekstrakurikuler, serta penonaktifan periode/cover. Penulisan terdapat pada CMS, admin user, periode/gelombang, hero dan cover kegiatan.

Tidak ditemukan predicate Boolean integer mentah pada query aplikasi yang perlu diganti satu per satu. Beberapa input form tervalidasi dapat berupa integer/string 0/1. Primitive Boolean cast Eloquent berlaku pada pembacaan, bukan otomatis pada assignment, sehingga diperlukan normalisasi terpusat saat model disimpan. Model yang memiliki status string (berita/pengumuman/kegiatan/pendaftaran) tidak dipaksa menjadi Boolean.

## Implementasi aplikasi-level

1. app/Database/PostgresBooleanConnection.php memperluas PostgresConnection, hanya mengubah binding bertipe PHP bool menjadi string true atau false, kemudian memanggil parent::prepareBindings().
2. PDO tetap menggunakan binding parameter normal, dengan PDO::PARAM_STR untuk representasi tersebut. Tidak ada interpolasi atau rewrite SQL. PostgreSQL menerima representasi string ini dalam konteks Boolean.
3. Integer biasa, string, tanggal, null, resource dan key binding tetap ditangani Laravel seperti semula. Tidak ada konversi semua angka 0/1 di connection layer.
4. PostgresBooleanServiceProvider mendaftarkan Connection::resolverFor('pgsql', ...) pada register(), sebelum AppServiceProvider/boot/controllers. Factory tetap lazy dan semua grammar/processor/transaction handling PostgreSQL diwarisi dari Laravel.
5. Listener eloquent.saving terpusat hanya berlaku bagi model berkoneksi pgsql. Hanya atribut yang dideklarasikan cast bool/boolean dan mempunyai nilai 0, 1, '0', '1' yang diubah menjadi PHP bool. ID, kuota, null, atribut lain dan model MySQL/SQLite tidak diubah.
6. config/app.php hanya ditambah satu nama provider. Koneksi dan credential di config/database.php/.env tidak diubah.

Tidak ada patch vendor, perubahan schema/tipe kolom, dependency update, atau perubahan controller/business logic.

## Pengujian lokal

PostgresBooleanBindingTest: **5 tests, 14 assertions lulus**, mencakup resolver pgsql, kelas MySQL tetap asli, true/false menjadi string, PDO::PARAM_STR vs integer, named keys, tanggal, null/resource, integer biasa tetap integer, normalisasi cast hanya pgsql, dan MySQL tetap 1/0.

PHPUnit keseluruhan: **135 tests, 1.038 assertions lulus**. Suite biasa menggunakan SQLite fixture lokal terisolasi; MySQL behavior diuji pada kelas MySqlConnection dan model tanpa membuka koneksi MySQL nyata. Tidak ada klaim tes CRUD MySQL live.

Syntax kedua kelas production lulus; git diff --check bersih. Review source independen tidak menemukan masalah penting; reviewer tidak mengakses credential atau menjalankan tes live.

## Pengujian Supabase PostgreSQL yang benar-benar dijalankan

PostgresBooleanIntegrationTest: **2 tests, 139 assertions lulus**, waktu 2 menit 21,892 detik.

- Legacy Laravel stock: true dan false menghasilkan 42883 pada perbandingan Boolean ketika bind integer/emulated, dan 42804 pada INSERT integer. Error yang diharapkan dibatasi SAVEPOINT dan di-rollback ke savepoint.
- Custom connection: SELECT where true dan false, INSERT model true/false, update dua arah, bulk UPDATE, DELETE dengan predicate Boolean, serta named Boolean parameters berhasil.
- Matriks tersebut diuji pada emulated prepares dan native unnamed parameters. PDO_PGSQL disable-server-prepares digunakan pada tes native agar tidak bergantung pada named prepared statement transaction-pooler. Atribut PDO dikembalikan setelah tes; konfigurasi deployment tidak diubah.
- Sembilan model Boolean di atas diuji create true/false, fresh/cast, filter true/false, update dua arah, dan DELETE predicate Boolean.
- SocialMedia::active() berhasil.
- PublicContentController homepage, guru dan ekstrakurikuler menghasilkan HTTP 200.
- AdminAdmissionPeriodController diuji create dengan input 1, menonaktifkan periode lama memakai bulk update false, update 0 lalu 1, dan memastikan hanya satu periode aktif.
- ApplicantController dashboard dan submit berhasil memakai periode/gelombang aktif; status dan periode pendaftaran terverifikasi sesuai flow existing.

### Isolasi dan rollback

Tes tidak menjalankan migration production. Koneksi memakai .env.supabase existing tanpa mengubah isinya. Semua record tes ditulis hanya ke TEMP TABLE dalam transaksi.

Untuk regresi aplikasi, struktur kolom/default disalin memakai LIKE ke temporary tables; tidak ada row production yang disalin. search_path dibatasi hanya ke schema pg_temp milik koneksi sehingga tabel aplikasi yang tidak disiapkan tidak dapat fallback ke public. Setiap tabel diverifikasi benar-benar resolve ke namespace temporary.

Default serial ID yang tersalin dilepas sebelum INSERT dan diganti identity milik tabel temporary; sequence production tidak dipakai/di-advance. Default serial non-ID yang tidak diharapkan membuat tes menolak penulisan. FK/index/trigger/RLS production tidak disalin; tes ini menguji binding dan flow PHP, bukan seluruh constraint/security policy production.

Semua transaksi di-rollback. Hilangnya tabel probe setelah rollback diverifikasi pada tearDown, lalu koneksi diputus. Model/delete/lock dalam tes berlaku hanya untuk objek temporary. Storage difake ke lokal selama regresi controller; tidak ada upload/delete S3 atau perubahan record public production. Statement dan lock timeout dibatasi untuk tes.

## Mengulang tes

```powershell
php -d sys_temp_dir=storage/app/testing/pdf-logo-audit/temp vendor/phpunit/phpunit/phpunit tests/Unit/PostgresBooleanBindingTest.php
php -d sys_temp_dir=storage/app/testing/pdf-logo-audit/temp vendor/phpunit/phpunit/phpunit

# Opt-in khusus temporary PostgreSQL + rollback, tanpa migration.
$env:RUN_PGSQL_BOOLEAN_TESTS = '1'
php -d sys_temp_dir=storage/app/testing/pdf-logo-audit/temp vendor/phpunit/phpunit/phpunit tests/Integration/PostgresBooleanIntegrationTest.php
Remove-Item Env:RUN_PGSQL_BOOLEAN_TESTS
```

Direktori temporary harus tersedia. Jangan memakai PostgresDeploymentTest existing sebagai pengganti tes ini: suite tersebut ditujukan ke database *_test terpisah dan mempunyai langkah migration. Tes Boolean baru tidak memanggil migrate.

## Apakah query perlu diubah satu per satu?

Tidak untuk query aplikasi existing yang memakai PHP bool dan penulisan model dengan cast Boolean. SELECT/INSERT/UPDATE/DELETE melalui connection pgsql mendapatkan penanganan yang sama, termasuk query builder dan Eloquent.

Batas yang disengaja: raw SQL berisi literal is_active = 1, binding integer pada kolom Boolean melalui query builder tanpa model cast, atau pemanggilan PDO langsung tidak dapat ditebak aman oleh layer ini. Gunakan PHP bool untuk API Boolean; jangan mengonversi semua integer secara global karena akan merusak ID/kuota. Audit tidak menemukan kebutuhan rewrite query existing untuk kasus tersebut.

## Risiko dan gerbang production

- Kode telah diuji terhadap PostgreSQL Supabase nyata tetapi belum dieksekusi pada deployment Vercel baru. Jangan menyatakan HTTP 500 production sudah selesai sebelum deployment yang nanti disetujui dan smoke test homepage/login/dashboard/admin dilakukan.
- CLI lokal masih PHP 8.0.30; runtime target 8.2.20 perlu diverifikasi saat deployment. Implementasi mengikuti API Laravel 9.52.22 yang diperiksa dan diuji.
- Sesudah upgrade Laravel atau penambahan provider/koneksi database awal, jalankan ulang regresi resolver agar registration tetap mendahului koneksi.
- Pengaturan pooler/SSL/emulated-prepares existing tidak diubah. Native test memakai unnamed parameters; ini bukan rekomendasi mengubah mode pooler production.
- Eloquent normalization bergantung pada deklarasi cast bool/boolean model. Model baru dengan field Boolean harus tetap mendeklarasikan cast yang benar untuk normalisasi input numeric 0/1.

## File perubahan

- app/Database/PostgresBooleanConnection.php — baru.
- app/Providers/PostgresBooleanServiceProvider.php — baru.
- config/app.php — tambah registrasi provider saja.
- tests/Unit/PostgresBooleanBindingTest.php — baru.
- tests/Integration/PostgresBooleanIntegrationTest.php — baru.
- docs/POSTGRES_BOOLEAN_COMPATIBILITY_2026-11-15.md — laporan ini.

Tidak mengubah APP_KEY, credential, DB_HOST/DB_PASSWORD, routing, autentikasi, Storage, schema, migration, controller, model atau flow SPMB. Tidak menghapus data production, tidak push/deploy.

Referensi: https://www.postgresql.org/docs/17/datatype-boolean.html dan source Laravel 9.52.22 Connection::prepareBindings(), Connection::bindValues(), ConnectionFactory::createConnection() yang diperiksa lokal.