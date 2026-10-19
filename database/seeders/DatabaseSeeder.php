<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Development samples are now explicit, never the default production bootstrap.
        $this->call(ProductionSeeder::class);
    }
}
