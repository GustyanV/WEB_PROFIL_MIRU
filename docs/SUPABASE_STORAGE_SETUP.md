# Setup Supabase Storage

Upload production tidak boleh mengandalkan filesystem Vercel. Project menggunakan adapter S3 Flysystem yang kompatibel dengan Laravel 9; SDK Supabase frontend tidak diperlukan.

## Bucket

Buat dua bucket melalui Storage pada project Supabase:

- site-media: public, untuk hero, logo, favicon, guru, program, berita, prestasi, ekstrakurikuler, kegiatan, serta galeri.
- spmb-documents: private, untuk KK, akta, KTP, dokumen pendaftar dan foto siswa SPMB.

Nama bucket boleh berbeda; gunakan nama sebenarnya dalam env. Status public/private adalah pengaturan bucket Supabase, bukan izin ACL per objek. Jangan menaruh berkas SPMB dalam bucket publik.

## Credential S3

Buka pengaturan Storage S3 dan buat Access Key ID serta Secret Access Key. Salin endpoint S3 dan region persis dari dashboard. Endpoint S3 berbeda dari URL publik REST.

```dotenv
FILESYSTEM_DISK=s3
PUBLIC_FILESYSTEM_DRIVER=s3
SPMB_FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_BUCKET=
AWS_PRIVATE_BUCKET=
AWS_URL=
```

AWS_BUCKET menunjuk bucket media publik. AWS_PRIVATE_BUCKET menunjuk bucket dokumen privat. AWS_URL adalah base URL publik REST yang diakhiri /storage/v1/object/public/NAMA_BUCKET_PUBLIK; tidak diakhiri /storage/v1/s3 dan tidak menunjuk bucket privat.

Bentuk endpoint S3 pada dokumentasi Supabase adalah https://PROJECT_REF.storage.supabase.co/storage/v1/s3. Ini penjelasan struktur, bukan credential atau nilai yang boleh disalin tanpa menggantinya. Ambil endpoint aktual dari dashboard.

Sumber: [S3 Authentication](https://supabase.com/docs/guides/storage/s3/authentication), [S3 Compatibility](https://supabase.com/docs/guides/storage/s3/compatibility).

S3 Access Key memberikan akses server yang luas dan dapat melewati RLS. Simpan hanya pada environment server, jangan pada VITE_*, Blade, JavaScript, repository, atau screenshot. Service-role key tidak diperlukan oleh frontend maupun konfigurasi ini. Jangan menggunakan bucket public untuk menyelesaikan error akses dokumen.

## Cara kerja pada project

- Kode CMS tetap memakai nama disk public; driver disk tersebut berubah menjadi s3 melalui PUBLIC_FILESYSTEM_DRIVER.
- Dokumen tetap memakai nama disk spmb; driver berubah melalui SPMB_FILESYSTEM_DRIVER dan bucket terpisah.
- Database menyimpan object key relatif, bukan URL atau signed URL.
- URL gambar menggunakan Storage::disk('public')->url() sehingga tidak bergantung pada /storage lokal.
- Dokumen di-stream melalui route existing yang memeriksa login, role, dan pemilik; URL bucket privat tidak ditampilkan.
- Logo PDF dibaca melalui Storage lalu dimasukkan sebagai data URI PNG. Dompdf tidak perlu mengambil URL cloud bebas.
- Lokal tetap menggunakan driver local untuk kedua disk, tanpa mengganti .env existing.

Adapter S3 menambahkan empat dependency baru; Laravel/Dompdf/dependency existing tidak di-upgrade major. Dukungan Supabase ACL tidak identik dengan AWS; otoritas akses tetap pada bucket. Verifikasi upload nyata wajib dilakukan setelah credential tersedia.

## Upload test

Sesudah env server terisi:

1. Login admin, unggah hero dan foto guru kecil (<2 MB), lalu buka homepage/halaman guru.
2. Refresh atau buat deployment baru: gambar harus tetap tersedia.
3. Periksa objek ada pada bucket media dan URL gambar memakai domain Storage Supabase.
4. Login pendaftar, unggah dokumen; periksa objek masuk bucket privat.
5. Pemilik dan admin aktif dapat melihat dokumen. Akun lain dan tamu harus ditolak.
6. URL publik REST terhadap dokumen privat harus menolak akses tanpa autentikasi.
7. Ganti gambar dan uji penghapusan melalui CMS untuk memastikan object key serta bucket benar.
8. Unduh PDF dan scan QR dari perangkat lain.

Jangan menguji dengan data pribadi sungguhan sebelum akses privat diverifikasi.

## Batas Vercel dan upload banyak gambar

Vercel Functions memiliki batas payload request/response; dokumentasi saat verifikasi menyebut 4,5 MB. Project membatasi file individual 2 MB dan api/php.ini membatasi POST 4 MB. Unggah galeri dalam batch kecil dengan total file dan multipart di bawah batas tersebut. Pilihan 20 foto pada UI existing tidak berarti 20×2 MB dapat dikirim melalui satu request Vercel.

Direct upload/signed browser upload akan memerlukan perubahan alur upload dan tidak dikerjakan pada tahap ini. Baca [batas Functions Vercel](https://vercel.com/docs/functions/limitations).

## Memindahkan file existing

Perubahan config tidak otomatis menyalin file lokal ke cloud. Jangan menghapus sumber lokal. Setelah backup, salin hanya media publik dari storage/app/public ke bucket media, kecualikan folder spmb jika ada. Salin dokumen dari storage/app/private/spmb ke prefix spmb pada bucket privat. Pertahankan object key persis seperti database.

Gunakan alat S3-compatible atau proses transfer server yang disetujui. Perintah spmb:privatize-documents existing khusus pemindahan publik-ke-privat pada instalasi; jangan menjalankannya sebagai alat migrasi cloud karena dapat menghapus salinan sumber setelah verifikasi.

Pemindahan file dan import record MySQL ke PostgreSQL harus dikoordinasikan. Mengunggah file tanpa record terkait tidak membuat konten CMS otomatis.
