<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Announcement;
use App\Models\Extracurricular;
use App\Models\News;
use App\Models\Program;
use App\Models\SchoolProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublicDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local','testing')) return;
        $school = SchoolProfile::query()->first();
        if ($school) {
            SchoolProfile::firstOrCreate([], [
                'name' => 'MI Raudlatul Ulum Jabung Wetan',
                'address' => 'JL. PP. NURUL QODIM JABUNG WETAN',
                'history' => "Konten berikut merupakan data demo dan dapat diperbarui melalui dashboard admin.\n\nMI Raudlatul Ulum Jabung Wetan berawal dari semangat masyarakat untuk menghadirkan pendidikan dasar Islam yang dekat, terjangkau, dan bermutu bagi putra-putri di lingkungan Jabung Wetan. Pada masa awal, kegiatan belajar dilaksanakan dengan fasilitas sederhana serta dukungan guru dan tokoh masyarakat.\n\nSeiring bertambahnya kepercayaan masyarakat, jumlah peserta didik dan tenaga pendidik terus berkembang. Madrasah mulai melengkapi ruang belajar, bahan ajar, serta kegiatan pembiasaan keagamaan yang menjadi ciri kehidupan sehari-hari siswa.\n\nSaat ini, MI Raudlatul Ulum Jabung Wetan terus memperkuat pembelajaran akademik, pendidikan karakter, literasi, dan keterampilan abad ke-21. Setiap langkah pengembangan diarahkan untuk menciptakan lingkungan belajar yang aman, religius, dan menyenangkan.",
                'headmaster_name' => 'Siti Aminah, S.Pd.I.',
                'headmaster_message' => "Assalamu'alaikum warahmatullahi wabarakatuh.\n\nSelamat datang di MI Raudlatul Ulum Jabung Wetan. Madrasah adalah rumah kedua bagi anak-anak untuk belajar, bertumbuh, dan membiasakan akhlak yang baik. Kami berkomitmen mendampingi peserta didik agar memiliki ilmu, iman, kemandirian, serta rasa percaya diri.\n\nDengan dukungan orang tua dan masyarakat, kami terus berusaha menghadirkan pembelajaran yang aktif, hangat, dan bermakna. Mari bersama-sama menyiapkan generasi yang berilmu, berakhlakul karimah, dan bermanfaat bagi sesama.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.",
            ]);
        }

        foreach ([
            ['Tahfidz Al-Qur’an', 'Program hafalan bertahap yang membimbing siswa mencintai, menghafal, dan mengamalkan Al-Qur’an dalam keseharian.'],
            ['Tahsin dan Tilawah', 'Pendampingan bacaan Al-Qur’an melalui latihan makhraj, tajwid, dan tilawah yang menyenangkan.'],
            ['Pembiasaan Salat Dhuha', 'Pembiasaan ibadah pagi untuk menumbuhkan kedisiplinan, ketenangan, dan rasa syukur siswa.'],
            ['Literasi Pagi', 'Kegiatan membaca, bercerita, dan menulis singkat untuk membangun budaya literasi sejak dini.'],
            ['Pramuka', 'Kegiatan kepanduan yang melatih kepemimpinan, kerja sama, kemandirian, serta kepedulian sosial.'],
            ['Pembelajaran Digital', 'Pemanfaatan teknologi secara bijak untuk mendukung proses belajar yang kreatif dan relevan.'],
        ] as $row) {
            Program::firstOrCreate(['title' => $row[0]], ['description' => $row[1], 'is_active' => true]);
        }

        foreach ([
            ['Juara 1 Lomba Tahfidz Tingkat Kecamatan', 'Alya Nuraini', 'Kecamatan', '2026'],
            ['Juara 2 Kaligrafi Tingkat Kecamatan', 'Muhammad Rafi', 'Kecamatan', '2026'],
            ['Juara 3 Olimpiade Matematika Madrasah', 'Nabila Putri', 'Kabupaten', '2026'],
            ['Juara Harapan Pidato Bahasa Indonesia', 'Fikri Ramadhan', 'Kabupaten', '2025'],
            ['Juara 2 Pramuka Siaga', 'Regu Elang', 'Kecamatan', '2025'],
            ['Juara 1 Lomba Adzan', 'Ahmad Faris', 'Kecamatan', '2025'],
        ] as $row) {
            Achievement::firstOrCreate(['title' => $row[0]], ['student_name' => $row[1], 'level' => $row[2], 'year' => $row[3]]);
        }

        foreach ([
            ['Pramuka', 'Latihan kepanduan untuk membangun disiplin, kerja sama, dan kepemimpinan.'],
            ['Hadrah', 'Seni musik Islami yang melatih kekompakan, kepekaan irama, dan percaya diri.'],
            ['Tahfidz', 'Kegiatan penguatan hafalan Al-Qur’an bersama pembina.'],
            ['Muhadharah', 'Latihan pidato untuk membangun keberanian berbicara di depan umum.'],
            ['Kaligrafi', 'Ruang kreatif untuk mengenal keindahan tulisan Arab.'],
            ['Futsal', 'Olahraga tim yang melatih kebugaran, sportivitas, dan kerja sama.'],
        ] as $row) {
            Extracurricular::firstOrCreate(['name' => $row[0]], ['description' => $row[1], 'is_active' => true]);
        }

        $news = [
            'Peringatan Maulid Nabi di MI Raudlatul Ulum Jabung Wetan',
            'Siswa MI Raudlatul Ulum Raih Prestasi Tahfidz',
            'Kegiatan Pramuka Membentuk Karakter Disiplin',
            'Madrasah Gelar Kerja Bakti Lingkungan',
            'Literasi Pagi Menumbuhkan Gemar Membaca',
            'Class Meeting Menjadi Ruang Sportivitas Siswa',
        ];
        foreach ($news as $index => $title) {
            News::firstOrCreate(['slug' => Str::slug($title)], [
                'title' => $title,
                'excerpt' => 'Kabar kegiatan dan pembelajaran terbaru dari MI Raudlatul Ulum Jabung Wetan.',
                'content' => "Kegiatan ini menjadi bagian dari upaya MI Raudlatul Ulum Jabung Wetan untuk menghadirkan pengalaman belajar yang bermakna bagi peserta didik. Siswa mengikuti kegiatan dengan antusias dan pendampingan guru.\n\nSelain memperkuat kemampuan akademik, kegiatan ini mengajak siswa membiasakan sikap disiplin, tanggung jawab, serta kerja sama. Nilai-nilai tersebut diharapkan dapat diterapkan dalam kehidupan sehari-hari.\n\nMadrasah menyampaikan terima kasih kepada seluruh siswa, guru, dan orang tua yang mendukung kegiatan. Dokumentasi dan informasi berikutnya akan terus diperbarui melalui website madrasah.",
                'status' => 'published',
                'published_at' => now()->subDays($index + 1),
            ]);
        }

        foreach (['Jadwal SPMB Tahun Pelajaran 2026/2027', 'Informasi Libur Madrasah', 'Jadwal Pembagian Rapor', 'Pertemuan Wali Murid'] as $index => $title) {
            Announcement::firstOrCreate(['title' => $title], ['content' => 'Informasi ini merupakan pengumuman demo yang dapat diperbarui oleh admin madrasah.', 'status' => 'published', 'published_at' => now()->subDays($index)]);
        }
    }
}
