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
            Schema::table('sales', function (Blueprint $table) {
                if (!Schema::hasColumn('sales', 'discount_type')) {
                    $table->string('discount_type')->nullable()->after('payment_method'); // 'sc', 'pwd', 'na'
                }
                if (!Schema::hasColumn('sales', 'discount_card_no')) {
                    $table->string('discount_card_no')->nullable()->after('discount_type');
                }
                if (!Schema::hasColumn('sales', 'discount_name')) {
                    $table->string('discount_name')->nullable()->after('discount_card_no');
                }
                if (!Schema::hasColumn('sales', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0)->after('total_amount');
                }
            });
        });

        // Split for safety if column might exist
        if (!Schema::hasColumn('sales', 'discount_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('total_amount');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_card_no', 'discount_name', 'discount_amount']);
        });
    }
};
