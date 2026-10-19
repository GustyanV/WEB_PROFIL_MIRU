# Checklist data sekolah sebelum produksi

Status 19 Oktober 2026: pihak sekolah belum memberikan sumber resmi. Tidak ada data existing yang dinyatakan sudah terverifikasi hanya karena tampil di website. Baca juga [audit data](AUDIT_DATA_SEKOLAH.md).

## Berkas dan penanggung jawab

- [ ] Tentukan petugas sekolah yang menyetujui isi final dan tanggal persetujuannya.
- [ ] Siapkan dokumen identitas madrasah untuk memverifikasi nama, NSM, NPSN, dan alamat.
- [ ] Siapkan logo yang disahkan (JPG/JPEG/PNG/WEBP, maksimal 2 MB).
- [ ] Siapkan favicon PNG/ICO, maksimal 1 MB; upload ICO melalui Pengaturan Website.
- [ ] Pastikan izin publikasi foto siswa, guru, kepala madrasah, serta dokumentasi kegiatan sudah tersedia.
- [ ] Simpan backup database dan storage sebelum penggantian data. Jangan gunakan reset database atau migrate:fresh.

## Identitas, profil dan kontak

| Data yang disiapkan | Lokasi CMS | Pemeriksaan |
| --- | --- | --- |
| Nama sekolah, NSM, NPSN | Profil Madrasah | Cocokkan dengan dokumen resmi; jangan mengambil angka dari kop PDF lama |
| Alamat, telepon, WhatsApp, email, website | Profil Madrasah / Kontak | Pastikan nomor/alamat benar-benar milik sekolah |
| Tagline, footer, copyright | Pengaturan Website | Hapus klaim/pesan contoh; sepakati teks final |
| Logo dan favicon | Pengaturan Website | Periksa header, footer dan tab browser |
| Tentang sekolah, sejarah, visi, misi, tujuan, komitmen | Profil Madrasah | Gunakan naskah yang disahkan sekolah |
| Nama kepala, sambutan dan foto | Profil Madrasah | Cocokkan dengan kepala madrasah yang menjabat |
| Jam layanan dan URL Google Maps embed | Kontak | Simpan URL embed saja, bukan iframe mentah |
| Instagram, Facebook, YouTube, TikTok | Media Sosial | Isi akun resmi saja; URL kosong tidak memunculkan ikon |

- [ ] Semua identitas dan profil di atas sudah disetujui.
- [ ] Nomor contoh 0335-000000 / 081200000000 dan domain .test sudah diganti di CMS. Nilai contoh disembunyikan dari publik, tetapi record asal tidak dihapus.
- [ ] Buka halaman profil, sejarah, visi/misi, sambutan, kontak, dan homepage untuk mengecek hasil.
- [ ] Periksa identitas pada kop PDF; nama, alamat, NSM/NPSN dan logo memakai database. Yayasan/akreditasi tidak dikarang dari template lama.

## Hero

- [ ] Foto siswa dan background asli siap.
- [ ] Judul, subjudul, small text, tahun pelajaran sudah benar.
- [ ] URL tombol daftar dan informasi sudah benar.
- [ ] Hapus/ubah foto demo lewat CMS; aktifkan banner final setelah disetujui.
- [ ] Tahun hero dan periode SPMB konsisten. Hero adalah konten editorial dan tidak otomatis ditimpa periode.
- [ ] Jika belum ada foto, fallback netral berarti “belum tersedia”, bukan dokumentasi asli.

## Guru, program, prestasi, ekstrakurikuler

- [ ] Guru: nama, jabatan, mata pelajaran, NIP/NUPTK bila dipublikasikan, foto dan bio singkat.
- [ ] Nonaktifkan guru/program/ekstrakurikuler demo yang belum diganti; jangan menghapus massal.
- [ ] Program unggulan benar-benar diselenggarakan. Jangan mempertahankan program hanya karena ada pada seeder.
- [ ] Prestasi: verifikasi nama siswa, lomba, tingkat, tahun dan bukti/foto. Modul ini belum memiliki flag aktif; semua record prestasi tampil. Edit/ganti record yang benar; hapus individual hanya setelah audit dan backup.
- [ ] Ekstrakurikuler: nama, pembina, jadwal, deskripsi, gambar, status aktif.
- [ ] Buka halaman publik masing-masing untuk memeriksa hasil.

## Berita, pengumuman, kegiatan dan galeri

- [ ] Berita/pengumuman memakai judul dan isi yang disahkan; tidak ada Test, Dummy, Example, Lorem Ipsum.
- [ ] Record bertanda dummy/demo masih tersedia di admin untuk diedit; penyaringan publik bukan penghapusan database.
- [ ] Periksa berita realistis dari seeder juga: judul yang terlihat wajar belum berarti kejadian sungguhan.
- [ ] Kegiatan: judul, tanggal, kategori, ringkasan, isi dan foto yang benar.
- [ ] Simpan kegiatan/berita/pengumuman sebagai draft sebelum siap dipublikasikan.
- [ ] Periksa published_at; tanggal masa depan belum tampil di publik.
- [ ] Galeri kegiatan: upload foto asli, caption, urutan dan satu cover; jangan merusak relasi kegiatan/foto.
- [ ] Album galeri existing: upload foto asli, ganti cover, hapus foto placeholder satu per satu setelah peninjauan. Album existing belum memiliki status draft.
- [ ] Buka semua foto dan cek tidak ada broken image. Fallback netral hanya sementara.

## Periode SPMB

- [ ] Tahun pelajaran dan nama periode benar.
- [ ] Tanggal buka/tutup, kuota dan status periode sudah disetujui.
- [ ] Hanya satu periode aktif.
- [ ] Gelombang punya periode yang benar, tanggal berada dalam periode, kuota dan status benar.
- [ ] Jangan menghapus periode/gelombang yang dipakai pendaftar; gunakan nonaktif.
- [ ] Homepage tidak mengklaim pendaftaran dibuka jika tidak ada gelombang aktif dalam tanggal berjalan.
- [ ] Tahun pelajaran bukti PDF dan verifikasi QR cocok dengan periode milik pendaftaran.

## Seeder produksi minimal

Default DatabaseSeeder kini memanggil ProductionSeeder, bukan data demo.

Pada database baru, siapkan variabel lingkungan berikut dengan nilai resmi/rahasia:

    SCHOOL_NAME=
    INITIAL_SUPER_ADMIN_NAME=
    INITIAL_SUPER_ADMIN_EMAIL=
    INITIAL_SUPER_ADMIN_PASSWORD=

Password pertama minimal 12 karakter. Jangan masukkan password ke Git, dokumen ini, atau percakapan. Jalankan hanya setelah konfigurasi dan backup siap:

    php artisan db:seed --class=ProductionSeeder

Jika sudah ada super_admin, seeder tidak mereset password/role/statusnya. Profil existing tidak ditimpa. Bootstrap tidak otomatis membuka periode, membuat hero, berita, guru, atau data demo. Hapus kredensial bootstrap dari konfigurasi setelah akun pertama dibuat dan disimpan dengan aman.

Sampel development hanya melalui perintah eksplisit:

    php artisan db:seed --class=DevelopmentSeeder

DevelopmentSeeder, PublicDemoSeeder, ActivitySeeder dan HeroBannerSeeder tidak berjalan pada environment production. Seeder development menggunakan firstOrCreate untuk mempertahankan perubahan admin. Menjalankan ulang seeder development tetap dapat menambahkan sampel yang belum ada; jangan gunakan untuk membersihkan data produksi.

## Pemeriksaan sebelum menyatakan data final

- [ ] Semua butir data resmi sudah dipenuhi dan disetujui pihak sekolah.
- [ ] Homepage, profil, konten, kontak, galeri diperiksa pada mobile dan desktop.
- [ ] Login, dashboard, submit/revisi/verifikasi/diterima/ditolak tetap bekerja.
- [ ] PDF, QR, export CSV dan PDF admin diperiksa.
- [ ] Jalankan php artisan test, php artisan migrate:status, npm run build.
- [ ] Periksa console browser, Laravel log, broken link/image.
- [ ] Catat data yang belum tersedia; jangan menggantinya dengan data rekaan.
