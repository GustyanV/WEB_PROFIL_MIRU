<?php

namespace Database\Seeders;

use App\Models\{Achievement,AdmissionPeriod,AdmissionWave,Announcement,Extracurricular,GalleryAlbum,GalleryImage,News,Program,SchoolProfile,Teacher,User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $password = Hash::make('spmb-dev-2026');
        User::updateOrCreate(['email' => 'superadmin@mi-ru.test'], ['name' => 'Super Admin MI Raudlatul Ulum','phone' => '081200000001','password' => $password,'role' => 'super_admin']);
        User::updateOrCreate(['email' => 'admin@mi-ru.test'], ['name' => 'Admin SPMB MI Raudlatul Ulum','phone' => '081200000002','password' => $password,'role' => 'admin']);

        SchoolProfile::updateOrCreate(['name' => 'MI Raudlatul Ulum'], ['address' => 'JL. PP. NURUL QODIM JABUNG WETAN','phone' => '0335-000000','whatsapp' => '081200000000','email' => 'info@mi-raudlatululum.test','website' => 'https://mi-raudlatululum.test','logo' => 'images/logo-miru.png','history' => 'MI Raudlatul Ulum hadir untuk memberikan pendidikan dasar Islam yang bermutu bagi masyarakat Jabung Wetan.','vision' => 'Terwujudnya peserta didik yang beriman, berilmu, berakhlak, dan berprestasi.','mission' => 'Menyelenggarakan pembelajaran yang aktif, membiasakan akhlak mulia, dan mengembangkan potensi setiap peserta didik.','headmaster_name' => 'Ahmad Fauzi, S.Pd.I.','headmaster_message' => 'Selamat datang di MI Raudlatul Ulum. Mari bertumbuh bersama dalam ilmu dan akhlak.']);

        foreach ([['Siti Aminah, S.Pd.I.','Kepala Madrasah','Pendidikan Agama Islam'],['Muhammad Arif, S.Pd.','Waka Kurikulum','Matematika'],['Nur Kholifah, S.Pd.I.','Guru Kelas I','Tematik'],['Fathur Rahman, S.Pd.','Guru Kelas II','Bahasa Indonesia'],['Luluk Hamidah, S.Pd.I.','Guru Kelas III','Akidah Akhlak'],['Budi Santoso, S.Pd.','Guru Kelas IV','IPA'],['Hana Safitri, S.Pd.','Guru Kelas V','Bahasa Inggris'],['Zainal Abidin, S.Pd.I.','Guru Kelas VI','Al-Quran Hadis']] as $i => $row) Teacher::updateOrCreate(['name'=>$row[0]],['position'=>$row[1],'subject'=>$row[2],'sort_order'=>$i+1,'is_active'=>true]);
        foreach ([['Tahfidz Al-Quran','Program pembiasaan hafalan dan perbaikan bacaan Al-Quran.'],['Bahasa Arab Dasar','Pembelajaran bahasa Arab komunikatif untuk siswa madrasah.'],['Literasi Digital','Penguatan literasi teknologi secara aman dan bertanggung jawab.'],['Sains dan Lingkungan','Eksplorasi sains sederhana dan kepedulian terhadap lingkungan.']] as $row) Program::updateOrCreate(['title'=>$row[0]],['description'=>$row[1],'is_active'=>true]);
        foreach ([['Juara 1 Olimpiade Matematika','Ahmad Rizki','Kecamatan','2026'],['Juara 2 Tahfidz Juz Amma','Siti Aisyah','Kabupaten','2026'],['Juara 1 Pidato Bahasa Arab','Muhammad Ilham','Kecamatan','2025'],['Juara Harapan Lomba Pramuka','Regu Elang','Kabupaten','2025']] as $row) Achievement::updateOrCreate(['title'=>$row[0]],['student_name'=>$row[1],'level'=>$row[2],'year'=>$row[3]]);
        foreach ([['Pramuka','Kegiatan kepanduan untuk membangun kemandirian dan kepemimpinan.'],['Hadrah','Kegiatan seni islami dan musik rebana.'],['Futsal','Latihan olahraga dan kerja sama tim.']] as $row) Extracurricular::updateOrCreate(['name'=>$row[0]],['description'=>$row[1],'is_active'=>true]);
        foreach (['Semangat Baru Tahun Pelajaran 2026/2027','Siswa MI Raudlatul Ulum Raih Prestasi','Kegiatan Pesantren Ramadan','Belajar Menyenangkan di Kelas','Kerja Bakti Lingkungan Madrasah','Peringatan Maulid Nabi'] as $i => $title) News::updateOrCreate(['slug'=>Str::slug($title)],['title'=>$title,'excerpt'=>'Informasi terbaru dari kegiatan MI Raudlatul Ulum.','content'=>'Ini adalah konten dummy berita MI Raudlatul Ulum yang dapat dikelola melalui dashboard admin pada tahap berikutnya.','status'=>'published','published_at'=>now()->subDays($i)]);
        foreach (['Libur Semester Ganjil','Jadwal Pengambilan Raport','Pendaftaran SPMB Dibuka'] as $i => $title) Announcement::updateOrCreate(['title'=>$title],['content'=>'Informasi penting untuk orang tua dan peserta didik MI Raudlatul Ulum.','status'=>'published','published_at'=>now()->subDays($i)]);
        foreach (['Kegiatan Pembelajaran','Prestasi Siswa','Kegiatan Keagamaan'] as $title) { $album=GalleryAlbum::updateOrCreate(['slug'=>Str::slug($title)],['title'=>$title,'description'=>'Dokumentasi '.$title.' MI Raudlatul Ulum.']); GalleryImage::updateOrCreate(['gallery_album_id'=>$album->id,'image'=>'images/gallery-placeholder.jpg'],['caption'=>'Dokumentasi '.$title]); }
        $period=AdmissionPeriod::updateOrCreate(['academic_year'=>'2027/2028'],['name'=>'SPMB Tahun Pelajaran 2027/2028','start_date'=>'2027-01-01','end_date'=>'2027-06-30','quota'=>120,'is_active'=>true]);
        foreach ([['Gelombang 1','2027-01-01','2027-03-31',60],['Gelombang 2','2027-04-01','2027-06-30',60]] as $i=>$row) AdmissionWave::updateOrCreate(['admission_period_id'=>$period->id,'name'=>$row[0]],['start_date'=>$row[1],'end_date'=>$row[2],'quota'=>$row[3],'is_active'=>$i===0]);
    }
}
