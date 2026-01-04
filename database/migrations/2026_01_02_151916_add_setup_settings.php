<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            ['key' => 'system_mode', 'value' => 'single', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'setup_complete', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['system_mode', 'setup_complete'])->delete();
    }
};
