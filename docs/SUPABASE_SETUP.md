# Setup Supabase PostgreSQL

Branch: deploy-vercel. Database MySQL lokal tetap digunakan oleh .env lokal.
Belum ada koneksi atau migration ke Supabase yang dijalankan tanpa credential.

## Membuat project dan mengambil koneksi

1. Masuk ke Supabase, pilih New project, tentukan organisasi, nama, region, dan password database yang kuat. Simpan password di password manager.
2. Tunggu database siap, lalu buka tombol Connect.
3. Untuk migration dari komputer yang hanya mendukung IPv4, pilih Session pooler. Ambil host, port 5432, database, dan username persis dari dashboard. Username pooler dapat berbeda dari username koneksi direct.
4. Koneksi Direct juga memakai port 5432, tetapi perlu konektivitas IPv6 atau opsi IPv4 yang sesuai.
5. Untuk Vercel/serverless, transaction pooler dapat dipakai pada port 6543 dengan DB_EMULATE_PREPARES=true. Session pooler tetap menjadi pilihan awal yang lebih sederhana; perhatikan batas koneksi.
6. Selalu gunakan DB_SSLMODE=require untuk koneksi hosted Supabase.

Sumber: [panduan Laravel Supabase](https://supabase.com/docs/guides/getting-started/quickstarts/laravel), [pooling](https://supabase.com/docs/guides/database/connecting-to-postgres/pooling-and-limits).

## Variabel server

Isi sendiri dari dashboard; tidak ada username/password/host contoh yang harus disalin:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_SSLMODE=require
DB_EMULATE_PREPARES=false
```

Jika memilih transaction pooler untuk Vercel, gunakan port dari dashboard (umumnya 6543) dan DB_EMULATE_PREPARES=true. Jangan menyalin DATABASE_URL MySQL lokal ke production. Field DB_* terpisah menghindari masalah encoding password dalam URI; hapus DATABASE_URL lama bila pernah diset.

## Migration tanpa menyentuh database lokal

Pastikan PHP CLI memiliki pdo_pgsql. PHP lokal saat persiapan ini belum mengaktifkannya. Aktifkan extension PostgreSQL sesuai distribusi PHP, lalu cek php -m.

Buat file konfigurasi deployment terpisah:

```powershell
Copy-Item .env.production.example .env.deploy
```

Isi .env.deploy dengan credential asli dan APP_URL domain tujuan. File ini diabaikan Git. Jangan menimpa .env lokal. Jika terminal memiliki variabel DB_* yang diekspor, buka terminal baru agar tidak menimpa konfigurasi file.

```powershell
php artisan config:clear
php artisan --env=deploy migrate:status
php artisan --env=deploy migrate --pretend
php artisan --env=deploy migrate --force
php artisan --env=deploy migrate:status
```

Periksa host/database tujuan sebelum perintah migrate. Migration hanya menambah skema; tidak memakai migrate:fresh, db:wipe, atau reset. Jangan otomatis menjalankan migration pada Vercel build atau request HTTP.

Untuk database production kosong, buat APP_KEY khusus production satu kali:

```powershell
php artisan --env=deploy key:generate --force
```

Salin APP_KEY yang sama ke Vercel. Jangan mengganti APP_KEY production existing. Isi INITIAL_SUPER_ADMIN_NAME, INITIAL_SUPER_ADMIN_EMAIL dan INITIAL_SUPER_ADMIN_PASSWORD minimal 12 karakter secara privat, kemudian:

```powershell
php artisan --env=deploy db:seed --class=ProductionSeeder --force
```

Seeder production tidak memasukkan akun/konten demo dan tidak mereset super admin existing. Hapus credential bootstrap setelah selesai. Jangan jalankan DevelopmentSeeder pada production.

## Data existing dan keamanan

Migration tidak menyalin isi MySQL ke PostgreSQL. MySQL lokal dan unggahan existing tetap dipertahankan. Jika data itu akan digunakan di production, lakukan pemetaan/import data sebagai pekerjaan terpisah setelah backup dan verifikasi, atau isi data resmi melalui CMS. Jangan mengunggah dump database ke GitHub.

Aplikasi tetap memakai autentikasi Laravel; tidak dipindahkan ke Supabase Auth. Jangan membuka tabel users, pendaftaran, session, dan cache melalui Data API publik. Tinjau exposure schema dan RLS sesuai kebutuhan Supabase; koneksi SQL Laravel tidak memerlukan anon/service-role key di frontend.

## Verifikasi yang tersedia

Seluruh migration sudah diuji kompilasinya menggunakan grammar PostgreSQL, termasuk UUID, foreign key, boolean, date, index dan tipe teks. Ini bukan pengganti menjalankan migration pada PostgreSQL sungguhan.

Workflow .github/workflows/deployment-compatibility.yml menyediakan PostgreSQL test terpisah dan menjalankan regresi SPMB pada PostgreSQL setelah branch didorong ke GitHub. Hasil workflow belum dapat diklaim sebelum benar-benar dijalankan.
