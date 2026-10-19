# Audit data sekolah — 19 Oktober 2026

Pihak sekolah menyatakan belum tersedia sumber resmi. Audit bersifat read-only; record existing dipertahankan. **Tidak ada data yang dinyatakan sebagai data asli terverifikasi.** Nama, alamat dan logo existing adalah kandidat identitas, bukan bukti keabsahan. Checklist pengisian tersedia di [DATA_SEKOLAH_CHECKLIST.md](DATA_SEKOLAH_CHECKLIST.md).

## Klasifikasi identitas/profil

| Bagian | Temuan existing | Klasifikasi / tindakan |
| --- | --- | --- |
| Nama madrasah | MI Raudlatul Ulum Jabung Wetan | Existing; cocok seeder, wajib konfirmasi |
| Alamat | JL. PP. NURUL QODIM JABUNG WETAN | Existing; cocok seeder, wajib konfirmasi; tidak diganti secara rekaan |
| NSM / NPSN | kosong / kosong | Belum tersedia; angka hardcode pada kop PDF lama bukan sumber resmi |
| Telepon / WhatsApp | 0335-000000 / 081200000000 | Placeholder seeder; disembunyikan dari publik, record tidak dihapus |
| Email / website | info@mi-raudlatululum.test / https://mi-raudlatululum.test | Domain .test; bukan kontak produksi |
| Tagline / favicon | kosong / kosong | Belum tersedia; gunakan CMS Pengaturan Website |
| Logo | images/logo-miru.png | Asset existing; perlu konfirmasi logo dan izin penggunaannya |
| Kepala madrasah | Siti Aminah, S.Pd.I. | Nama cocok seeder; belum terverifikasi |
| Sejarah | Memuat penanda eksplisit data demo | Teks contoh; public menampilkan empty state sampai diganti |
| Visi/misi/tujuan/tentang/komitmen | Naskah dari seeder/migration existing | Belum disahkan sekolah; dapat diedit seluruhnya dari CMS |
| Sambutan / foto kepala | Naskah contoh; foto kosong | Ganti naskah dan foto dari kepala yang menjabat |
| Jam layanan / Google Maps | kosong / kosong | Belum tersedia; map menampilkan empty state |

## Konten yang perlu ditinjau

| Modul | Jumlah | Temuan / status |
| --- | ---: | --- |
| Guru | 8 | Cocok dengan data seeder development; belum diverifikasi, record dipertahankan. |
| Program | 10 | Cocok dengan data seeder development; belum diverifikasi, record dipertahankan. |
| Prestasi | 10 | Cocok dengan data seeder development; belum diverifikasi, record dipertahankan. |
| Ekstrakurikuler | 6 | Cocok dengan data seeder development; belum diverifikasi, record dipertahankan. |
| Berita | 12 | 6 isi berpenanda konten dummy, 6 artikel realistis dari PublicDemoSeeder; semua perlu pemeriksaan sekolah. |
| Pengumuman | 7 | 4 pengumuman berpenanda demo dan 3 pengumuman contoh; perlu diganti dengan naskah/jadwal resmi. |
| Album galeri | 3 | Cocok dengan data seeder development; belum diverifikasi, record dipertahankan. |
| Foto galeri | 3 | 3 path images/gallery-placeholder.jpg tidak memiliki file fisik; kini memakai fallback netral. |
| Kegiatan | 5 | Cocok dengan data seeder development; belum diverifikasi, record dipertahankan. |
| Hero | 1 | Judul dan tahun 2026 / 2027 dari seeder; foto/background custom kosong. Tidak ditimpa otomatis. |
| Media sosial | 4 | Empat platform, URL kosong; tidak tampil ikon/link kosong. |
| Periode | 1 | Periode 2027/2028, kuota 120, Januari–Juni 2027; data contoh, belum disahkan. |
| Gelombang | 2 | Dua gelombang contoh kuota 60 masing-masing; perlu tanggal/status final dari sekolah. |

### Record Guru

- ID 1: Siti Aminah, S.Pd.I..
- ID 2: Muhammad Arif, S.Pd..
- ID 3: Nur Kholifah, S.Pd.I..
- ID 4: Fathur Rahman, S.Pd..
- ID 5: Luluk Hamidah, S.Pd.I..
- ID 6: Budi Santoso, S.Pd..
- ID 7: Hana Safitri, S.Pd..
- ID 8: Zainal Abidin, S.Pd.I..

### Record Program

- ID 1: Tahfidz Al-Quran.
- ID 2: Bahasa Arab Dasar.
- ID 3: Literasi Digital.
- ID 4: Sains dan Lingkungan.
- ID 5: Tahfidz Al-Qur’an.
- ID 6: Tahsin dan Tilawah.
- ID 7: Pembiasaan Salat Dhuha.
- ID 8: Literasi Pagi.
- ID 9: Pramuka.
- ID 10: Pembelajaran Digital.

### Record Prestasi

- ID 1: Juara 1 Olimpiade Matematika.
- ID 2: Juara 2 Tahfidz Juz Amma.
- ID 3: Juara 1 Pidato Bahasa Arab.
- ID 4: Juara Harapan Lomba Pramuka.
- ID 5: Juara 1 Lomba Tahfidz Tingkat Kecamatan.
- ID 6: Juara 2 Kaligrafi Tingkat Kecamatan.
- ID 7: Juara 3 Olimpiade Matematika Madrasah.
- ID 8: Juara Harapan Pidato Bahasa Indonesia.
- ID 9: Juara 2 Pramuka Siaga.
- ID 10: Juara 1 Lomba Adzan.

### Record Ekstrakurikuler

- ID 1: Pramuka.
- ID 2: Hadrah.
- ID 3: Futsal.
- ID 4: Tahfidz.
- ID 5: Muhadharah.
- ID 6: Kaligrafi.

### Record Berita

- ID 1: Semangat Baru Tahun Pelajaran 2026/2027 — published.
- ID 2: Siswa MI Raudlatul Ulum Raih Prestasi — published.
- ID 3: Kegiatan Pesantren Ramadan — published.
- ID 4: Belajar Menyenangkan di Kelas — published.
- ID 5: Kerja Bakti Lingkungan Madrasah — published.
- ID 6: Peringatan Maulid Nabi — published.
- ID 7: Peringatan Maulid Nabi di MI Raudlatul Ulum Jabung Wetan — published.
- ID 8: Siswa MI Raudlatul Ulum Raih Prestasi Tahfidz — published.
- ID 9: Kegiatan Pramuka Membentuk Karakter Disiplin — published.
- ID 10: Madrasah Gelar Kerja Bakti Lingkungan — published.
- ID 11: Literasi Pagi Menumbuhkan Gemar Membaca — published.
- ID 12: Class Meeting Menjadi Ruang Sportivitas Siswa — published.

### Record Pengumuman

- ID 1: Libur Semester Ganjil — published.
- ID 2: Jadwal Pengambilan Raport — published.
- ID 3: Pendaftaran SPMB Dibuka — published.
- ID 4: Jadwal SPMB Tahun Pelajaran 2026/2027 — published.
- ID 5: Informasi Libur Madrasah — published.
- ID 6: Jadwal Pembagian Rapor — published.
- ID 7: Pertemuan Wali Murid — published.

### Record Album galeri

- ID 1: Kegiatan Pembelajaran.
- ID 2: Prestasi Siswa.
- ID 3: Kegiatan Keagamaan.

### Record Kegiatan

- ID 1: Peringatan Maulid Nabi — published.
- ID 2: Kegiatan Pramuka — published.
- ID 3: Kerja Bakti Lingkungan Madrasah — published.
- ID 4: Lomba Tahfidz Siswa — published.
- ID 5: Peringatan Hari Santri — published.

## Placeholder dan sisa hardcode

- Nomor contoh, domain .test, teks konten dummy/pengumuman demo, serta judul eksplisit Test/Dummy/Example/Placeholder/Lorem Ipsum disaring dari tampilan publik. Penyaringan bukan verifikasi keaslian; data realistis dari seeder tetap harus ditinjau manual.
- Teks demo tetap dapat ditemukan dan diedit di CMS. Tidak ada mass delete, pengubahan status massal, atau pengosongan kontak di database.
- Tahun fallback hero tidak lagi hardcode; jika belum ada hero, mengikuti periode database. Tahun hero yang sengaja diisi admin tetap milik konten hero dan perlu diselaraskan oleh admin.
- Klaim pendaftaran dibuka pada homepage mengikuti gelombang aktif dalam tanggal berjalan; tidak mengubah validasi/flow submit SPMB.
- Kop PDF memakai nama, NSM/NPSN, alamat, logo dari database. Klaim akreditasi/yayasan dan angka identitas hardcode yang tidak didukung sumber resmi dihapus dari template, bukan diganti dengan angka rekaan.
- Asset hero-siswa.png/webp dan hero-pattern.png tetap disimpan untuk development/riwayat, tetapi konten tanpa foto tidak lagi menganggap foto siswa itu sebagai dokumentasi asli. Background dekoratif bukan foto sekolah terverifikasi.
- Kata Test/Example pada tests, fixture, dokumentasi dan template input adalah kebutuhan development; tidak dihapus massal.
- Halaman kurikulum masih berisi daftar informasi statis contoh dan diberi keterangan harus disesuaikan dengan dokumen sekolah. Tidak dibuat modul kurikulum baru pada tahap ini.
- Halaman struktur organisasi masih empty state; sekolah perlu menyiapkan struktur resmi. Tidak dibuat modul baru.

## File dan integrasi yang dibersihkan

- SchoolContent: resolver foto existing/storage, fallback netral per jenis, penanganan teks demo eksplisit dan sanitasi kontak contoh tanpa menyimpan perubahan database.
- PublicContentController, provider data publik, homepage, profil, daftar/detail berita, galeri, navbar/auth header dan PDF: memakai data CMS dan empty state/fallback yang sesuai.
- CMS Profil: seluruh naskah about/history/vision/mission/goals/education_commitment dan kepala madrasah dapat disimpan; upload mempertahankan gambar jika input kosong.
- CMS konten: pembina/jadwal ekstrakurikuler; bio dan NIP/NUPTK guru tampil publik; slug otomatis dan unique saat edit benar; foto album dapat ditinjau/dihapus individual melalui route existing.
- Migration additive menambah coach_name dan schedule nullable; tidak menyentuh record pendaftar, periode, gelombang, atau autentikasi.

## Seeder safety

DatabaseSeeder default memanggil ProductionSeeder minimal. Bootstrap produksi membutuhkan kredensial super_admin eksplisit hanya bila belum ada akun super_admin; tidak menimpa profil, password, role, status atau data existing. Tidak dijalankan pada database existing pada tahap ini.

DevelopmentSeeder merupakan pemindahan seeder demo lama dengan firstOrCreate. PublicDemoSeeder tidak lagi menimpa profil dan konten yang sudah diedit. DevelopmentSeeder/PublicDemoSeeder/HeroBannerSeeder/ActivitySeeder dibatasi local/testing. Periode/gelombang demo yang baru dibuat oleh development seeder default nonaktif; record existing tidak diubah. Sampel tetap tersedia melalui perintah eksplisit development, bukan seeding produksi default.

## Data resmi yang masih diperlukan

Seluruh butir pada checklist masih perlu persetujuan sekolah: identitas/nomor registrasi, kontak dan Maps, logo/favicon dan izin foto, naskah profil, kepala/guru, program yang benar-benar dijalankan, bukti prestasi, pembina/jadwal ekstrakurikuler, berita/pengumuman/kegiatan/galeri nyata, akun sosial resmi, periode/gelombang/tanggal/kuota final. Jangan menyatakan data produksi final sebelum semua butir tersebut diverifikasi.

## Hasil verifikasi tahap ini

- 45 tes Laravel lulus, termasuk 10 tes tambahan finalisasi data/seeder dan regresi PDF/QR/export.
- Seluruh migration Ran. Tambahan hanya coach_name dan schedule nullable pada ekstrakurikuler.
- Build Vite berhasil; warning hero-pattern.png existing dimuat saat runtime.
- Chrome: 45 pemeriksaan halaman/ukuran layar (390, 768, 1440), 33 tautan; tidak ada broken image, overflow horizontal, error console, atau kontak/teks dummy eksplisit yang diperiksa.
- Snapshot read-only sebelum/sesudah menunjukkan seluruh record sekolah dan jumlah pendaftar tetap sama.
- Database tidak direset; tidak ada penghapusan data demo otomatis. Authentication, flow SPMB dan dependency tidak diubah.