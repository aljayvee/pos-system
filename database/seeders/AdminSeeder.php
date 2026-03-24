<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Store Owner',
                'password' => 'Admin123456',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cashier@pos.com'],
            [
                'name' => 'Cashier One',
                'password' => 'password',
                'role' => 'cashier',
            ]
        );

        $products = [];
        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}
