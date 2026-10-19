<?php

namespace Database\Seeders;

use App\Models\HeroBanner;
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    public function run()
    {
        if (!app()->environment('local','testing')) return;
        HeroBanner::firstOrCreate([], [
            'small_text' => 'SPMB',
            'title' => 'Sistem Penerimaan Murid Baru',
            'subtitle' => 'Bersama MI Raudlatul Ulum Jabung Wetan, tumbuhkan ilmu, akhlak, dan potensi terbaik putra-putri Anda.',
            'academic_year' => '2026 / 2027',
            'primary_button_label' => 'DAFTAR SEKARANG',
            'primary_button_url' => '/register',
            'secondary_button_label' => 'INFORMASI SPMB',
            'secondary_button_url' => '#informasi-spmb',
            'is_active' => true,
        ]);
    }
}