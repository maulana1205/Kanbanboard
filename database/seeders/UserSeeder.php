<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'Division' => 'IT',
            'Team' => 'Management',
            'NIK' => '000000001',
            'Job_Function_KPI' => 'Administrator',
            'status' => 'active',
            'region' => 'HQ'
        ]);

        // Manager
        User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'Division' => 'IT',
            'Team' => 'management',
            'NIK' => '000000002',
            'Job_Function_KPI' => 'Manager',
            'status' => 'active',
            'region' => 'HQ'
        ]);

        // Leader
        User::create([
            'name' => 'Leader',
            'email' => 'leader@example.com',
            'password' => Hash::make('password'),
            'role' => 'leader',
            'Division' => 'IT',
            'Team' => 'Team A',
            'NIK' => '000000003',
            'Job_Function_KPI' => 'Leader',
            'status' => 'active',
            'region' => 'HQ'
        ]);

        // Beberapa User biasa
        foreach (range(1, 5) as $i) {
            User::create([
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'user',
                'Division' => 'IT',
                'Team' => 'Team A',
                'NIK' => '00000000' . (3 + $i),
                'Job_Function_KPI' => 'Drawing',
                'status' => 'active',
                'region' => 'HQ'
            ]);
        }
    }
}
