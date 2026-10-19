<?php
namespace Database\Seeders;

use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProductionSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function() {
            if (!User::where('role','super_admin')->exists()) {
                $data=[
                    'name'=>env('INITIAL_SUPER_ADMIN_NAME'),
                    'email'=>env('INITIAL_SUPER_ADMIN_EMAIL'),
                    'password'=>env('INITIAL_SUPER_ADMIN_PASSWORD'),
                ];
                if (Validator::make($data,['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:12'])->fails()) {
                    throw new \RuntimeException('Isi INITIAL_SUPER_ADMIN_NAME, INITIAL_SUPER_ADMIN_EMAIL unik, dan INITIAL_SUPER_ADMIN_PASSWORD minimal 12 karakter sebelum bootstrap produksi.');
                }
                User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>'super_admin','is_active'=>true]);
            }
            SchoolProfile::firstOrCreate([],['name'=>env('SCHOOL_NAME') ?: 'Madrasah']);
        });
    }
}
