<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id' => 9,
            'name'=>'superadmin',
            'mobile_no' => '9123595538',
            'email'=>'superadmin@gmail.com',
            'password'=>Hash::make('Admin@123')
        ]);
        User::create([
            'id' => 13,
            'name' => 'superadmin2',
            'mobile_no' => '9876543210',
            'email' => 'superadmin2@gmail.com',
            'password' => Hash::make('Admin@123'),                 // Hashed for login
            'new_password' => Crypt::encryptString('Admin@123'),  // Encrypted for decrypt
        ]);
    }
}
