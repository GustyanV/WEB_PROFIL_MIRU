<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            $table->longText('about')->nullable()->after('favicon');
            $table->longText('goals')->nullable()->after('mission');
            $table->longText('education_commitment')->nullable()->after('goals');
        });

        DB::table('school_profiles')->update([
            'about' => "Madrasah Ibtidaiyah (MI) Raudlatul Ulum Jabung Wetan merupakan lembaga pendidikan Islam tingkat dasar yang berkomitmen memberikan pendidikan berkualitas dengan mengintegrasikan ilmu pengetahuan umum, pendidikan agama Islam, dan pembentukan akhlak mulia.\n\nMI Raudlatul Ulum Jabung Wetan berupaya menciptakan lingkungan belajar yang nyaman, disiplin, religius, dan menyenangkan bagi seluruh peserta didik.\n\nMelalui kegiatan pembelajaran akademik dan keagamaan, madrasah berupaya membentuk generasi yang cerdas, beriman, bertakwa, mandiri, serta memiliki karakter yang baik dalam kehidupan sehari-hari.",
            'vision' => 'Terwujudnya generasi Islami yang berakhlakul karimah, berprestasi, disiplin, mandiri, dan berwawasan luas.',
            'mission' => "1. Menyelenggarakan pendidikan yang berlandaskan nilai-nilai ajaran Islam.\n2. Meningkatkan kualitas pembelajaran untuk mengembangkan potensi akademik peserta didik.\n3. Membiasakan perilaku disiplin, jujur, bertanggung jawab, dan saling menghormati.\n4. Menanamkan kecintaan terhadap Al-Qur'an dan pengamalan ajaran Islam.\n5. Mengembangkan bakat, minat, dan kreativitas peserta didik melalui kegiatan positif.\n6. Menciptakan lingkungan madrasah yang bersih, aman, nyaman, dan kondusif.",
            'goals' => "MI Raudlatul Ulum Jabung Wetan bertujuan menghasilkan lulusan yang memiliki dasar ilmu pengetahuan yang baik, mampu membaca dan memahami Al-Qur'an sesuai tingkat pendidikannya, berakhlak mulia, serta siap melanjutkan pendidikan ke jenjang berikutnya.",
            'education_commitment' => "MI Raudlatul Ulum Jabung Wetan berkomitmen untuk terus meningkatkan kualitas pendidikan melalui kerja sama antara guru, peserta didik, orang tua, dan masyarakat.\n\nDengan semangat pendidikan Islam, madrasah berupaya menjadi tempat tumbuh dan berkembangnya generasi yang berilmu, berkarakter, serta bermanfaat bagi agama, bangsa, dan masyarakat.\n\nMI Raudlatul Ulum Jabung Wetan — Berilmu, Berakhlak, dan Berprestasi.",
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            $table->dropColumn(['about', 'goals', 'education_commitment']);
        });
    }
};
