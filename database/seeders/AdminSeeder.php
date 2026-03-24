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
User::firstOrCreate(
    ['email' => 'admin@pos.com'],
    [
        'name' => 'Store Owner',
        'password' => Hash::make('Admin123456'),
        'role' => 'admin',
    ]
);

// 2. Create Cashier
User::firstOrCreate(
    ['email' => 'cashier@pos.com'],
    [
        'name' => 'Cashier One',
        'password' => Hash::make('password'),
        'role' => 'cashier',
    ]
);


        // 3. Create Dummy Products
        $products = []
        ;

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}
