<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'enable_tithes'
            $table->text('value')->nullable(); // e.g., '1'
            $table->timestamps();
        });

        // Insert Default Values
        DB::table('settings')->insert([
            ['key' => 'store_name', 'value' => 'My Sari-Sari Store', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'enable_tithes', 'value' => '1', 'created_at' => now(), 'updated_at' => now()], // Default ON
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};