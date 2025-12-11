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
            'password' => Hash::make('password'), // Password is 'password'
            'role' => 'admin',
        ]);

        // 2. Create Cashier
        User::create([
            'name' => 'Cashier One',
            'email' => 'cashier@pos.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);

        // 3. Create Dummy Products
        $products = [
            ['name' => 'Coffee', 'price' => 50.00, 'stock' => 100],
            ['name' => 'Sandwich', 'price' => 120.00, 'stock' => 50],
            ['name' => 'Water Bottle', 'price' => 20.00, 'stock' => 200],
            ['name' => 'Chips', 'price' => 45.00, 'stock' => 80],
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}