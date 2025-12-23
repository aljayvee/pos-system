<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestoreMasterStore extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure ID 1 exists
        $masterStore = DB::table('stores')->where('id', 1)->first();

        if (!$masterStore) {
            DB::table('stores')->insert([
                'id' => 1,
                'name' => 'Master Store',
                'address' => 'Main Branch',
                'contact_number' => '0000',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Master Store (ID 1) restored successfully.');
        } else {
            $this->command->info('Master Store already exists.');
        }

        // 2. Ensure Users have a valid store_id
        DB::table('users')->whereNull('store_id')->update(['store_id' => 1]);
    }
}
