<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'input_vat')) {
                $table->decimal('input_vat', 10, 2)->default(0)->after('total_cost');
            }
            if (!Schema::hasColumn('purchases', 'is_vat_registered_supplier')) {
                $table->boolean('is_vat_registered_supplier')->default(false)->after('input_vat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['input_vat', 'is_vat_registered_supplier']);
        });
    }
};
