<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('sales', function (Blueprint $table) {
        // We check if the column exists first, just to be safe
        if (!Schema::hasColumn('sales', 'reference_number')) {
            $table->string('reference_number')->nullable()->after('payment_method');
        }
        if (!Schema::hasColumn('sales', 'points_used')) {
            $table->integer('points_used')->default(0)->after('reference_number');
        }
        if (!Schema::hasColumn('sales', 'points_discount')) {
            $table->decimal('points_discount', 10, 2)->default(0.00)->after('points_used');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            //
        });
    }
};
