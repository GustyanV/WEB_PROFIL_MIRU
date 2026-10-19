<?php
namespace Database\Seeders;
use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class ActivitySeeder extends Seeder
{
    public function run() {
        if (!app()->environment('local','testing')) return;
        $rows = [
            ['Peringatan Maulid Nabi','Keagamaan','2026-09-04','Siswa mengikuti pembacaan selawat dan mengenal keteladanan Nabi Muhammad SAW.'],
            ['Kegiatan Pramuka','Pramuka','2026-09-12','Latihan simpul, kerja sama regu, dan kemandirian di halaman madrasah.'],
            ['Kerja Bakti Lingkungan Madrasah','Sosial','2026-09-19','Guru dan siswa bersama-sama membersihkan kelas, taman, dan lingkungan madrasah.'],
            ['Lomba Tahfidz Siswa','Akademik','2026-09-26','Peserta didik menunjukkan hafalan Al-Quran dengan bimbingan guru.'],
            ['Peringatan Hari Santri','Peringatan Hari Besar','2025-10-22','Upacara Hari Santri dan kegiatan kebersamaan untuk meneladani semangat para santri.'],
        ];
        foreach ($rows as [$title,$category,$date,$excerpt]) Activity::firstOrCreate(['slug'=>Str::slug($title)],[
            'title'=>$title,'category'=>$category,'activity_date'=>$date,'excerpt'=>$excerpt,
            'content'=>$excerpt."\n\nKegiatan diikuti peserta didik MI Raudlatul Ulum dengan pendampingan guru. Melalui kegiatan ini, siswa belajar disiplin, tanggung jawab, dan saling menghargai.",
            'status'=>'published','published_at'=>$date.' 08:00:00',
        ]);
    }
}