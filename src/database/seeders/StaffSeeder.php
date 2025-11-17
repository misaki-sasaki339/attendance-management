<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        Staff::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);

        Staff::create([
            'name' => 'テストスタッフ',
            'email' => 'staff@example.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff'
        ]);

        Staff::factory()->count(5)->create([
            'role' => 'staff',
        ]);
    }
}
