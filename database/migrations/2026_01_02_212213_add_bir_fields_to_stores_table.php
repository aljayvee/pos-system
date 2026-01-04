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
        Schema::table('stores', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->comment('Date based Serial Number for BIR');
            $table->string('min_number')->nullable()->comment('Machine Identification Number');
            $table->string('ptu_number')->nullable()->comment('Permit To Use Number');

            // ACCUMULATORS (Persistent)
            $table->decimal('accumulated_grand_total', 20, 2)->default(0)->comment('Running total of ALL sales. Never reset.');
            $table->unsignedBigInteger('z_reading_counter')->default(0)->comment('Increments on every Z-Reading');
            $table->string('last_si_number')->nullable()->comment('Last used Sales Invoice Number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'serial_number',
                'min_number',
                'ptu_number',
                'accumulated_grand_total',
                'z_reading_counter',
                'last_si_number'
            ]);
        });
    }
};
