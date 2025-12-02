<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建测试用户
        \App\Models\User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'company_name' => 'Test Company',
            'phone' => '+81-3-1234-5678',
            'address' => '日本东京都港区测试地址1-2-3',
        ]);
    }
}