<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Add the column (if it doesn't exist yet, to be safe)
    if (!Schema::hasColumn('sale_items', 'cost')) {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('cost', 10, 2)->after('price')->default(0);
        });
    }

        // OPTIONAL: Retroactively fill existing sales with the CURRENT product cost
        // to prevent reports from showing 100% profit (0 cost) for old data.
        // 2. Run the update with a NULL check (COALESCE defaults to 0 if null)
    $updateSql = "UPDATE sale_items
              SET cost = COALESCE(products.cost, 0)
              FROM products
              WHERE sale_items.product_id = products.id";
        DB::statement($updateSql);
    }

    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
};
