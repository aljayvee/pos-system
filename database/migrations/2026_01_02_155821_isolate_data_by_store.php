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
        $tables = ['categories', 'products', 'customers', 'sales', 'suppliers'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'store_id')) {
                Schema::table($table, function (Blueprint $table) {
                    // Default to 1 (Master Store) for existing data
                    $table->foreignId('store_id')->default(1)->after('id')->constrained()->onDelete('cascade');

                    // Add index for performance if not auto-added by foreignId
                    // $table->index('store_id'); 
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['categories', 'products', 'customers', 'sales', 'suppliers'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'store_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['store_id']);
                    $table->dropColumn('store_id');
                });
            }
        }
    }
};
