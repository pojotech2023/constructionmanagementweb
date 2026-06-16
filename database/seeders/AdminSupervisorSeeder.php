<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class AdminSupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
     DB::table('users')->delete();// clears old data

    User::create([
        'id' => 1,
        'name' => 'Admin User',
        'mobile_no' => '9999999999',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);

    User::create([
        'id' => 2,
        'name' => 'Supervisor User',
        'mobile_no' => '8888888888',
        'password' => Hash::make('super123'),
        'role' => 'supervisor',
    ]);
}    
}
