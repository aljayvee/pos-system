<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TRUNCATE to ensure chain integrity from step 0
        DB::table('activity_logs')->truncate();

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('hash', 64)->after('description')->nullable();
            $table->string('previous_hash', 64)->after('hash')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['hash', 'previous_hash']);
        });
    }
};
