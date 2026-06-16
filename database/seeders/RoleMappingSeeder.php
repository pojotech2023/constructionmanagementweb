<?php

namespace Database\Seeders;

use App\Models\RoleMapping;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RoleMapping::create([
            'id' => 1,
            'user_id' => 1,
            'role_id' => 1
        ]);
    }
}
