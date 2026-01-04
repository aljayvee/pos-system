<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Admin
        User::create([
            'name' => 'Store Owner',
            'email' => 'admin@pos.com',
            'password' => Hash::make('Admin123456'), // Password is 'Admin123456'
            'role' => 'admin',
        ]);

        // 2. Create Cashier (REMOVED as per Out-of-box requirement)
        // User::create([
        //     'name' => 'Cashier One',
        //     'email' => 'cashier@pos.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'cashier',
        // ]);

        // 3. Create Dummy Products
        $products = []
        ;

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}