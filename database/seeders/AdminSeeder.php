<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Admin
        User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Store Owner',
                'password' => 'Admin123456',
                'role' => 'admin',
            ]
        );

        // 2. Create Cashier
        User::firstOrCreate(
            ['email' => 'cashier@pos.com'],
            [
                'name' => 'Cashier One',
                'password' => 'password',
                'role' => 'cashier',
            ]
        );
    }
}
