<?php
namespace Database\Seeders;
use App\Models\SocialMedia;
use Illuminate\Database\Seeder;

class SocialMediaSeeder extends Seeder
{
    public function run()
    {
        foreach (array_keys(SocialMedia::PLATFORMS) as $order => $platform) {
            SocialMedia::firstOrCreate(['platform'=>$platform], ['url'=>null, 'is_active'=>true, 'sort_order'=>$order]);
        }
    }
}