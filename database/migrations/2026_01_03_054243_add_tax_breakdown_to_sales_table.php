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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('vatable_sales', 20, 2)->default(0)->after('total_amount');
            $table->decimal('vat_exempt_sales', 20, 2)->default(0)->after('vatable_sales');
            $table->decimal('vat_zero_rated_sales', 20, 2)->default(0)->after('vat_exempt_sales');
            $table->decimal('vat_amount', 20, 2)->default(0)->after('vat_zero_rated_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['vatable_sales', 'vat_exempt_sales', 'vat_zero_rated_sales', 'vat_amount']);
        });
    }
};
