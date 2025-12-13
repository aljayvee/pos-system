<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Track Input VAT on Purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('input_vat', 10, 2)->default(0)->after('total_cost');
            $table->boolean('is_vat_registered_supplier')->default(true)->after('supplier_id');
        });

        // 2. Track Output VAT on Sales
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('vatable_sales', 10, 2)->default(0)->after('total_amount');
            $table->decimal('output_vat', 10, 2)->default(0)->after('vatable_sales');
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['input_vat', 'is_vat_registered_supplier']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['vatable_sales', 'output_vat']);
        });
    }
};