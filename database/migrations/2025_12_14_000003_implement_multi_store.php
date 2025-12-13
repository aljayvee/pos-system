<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Create Stores Table
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Create Default "Main Store"
        DB::table('stores')->insert([
            'id' => 1,
            'name' => 'Main Store',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Inventories Table (Product Stock per Store)
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->integer('stock')->default(0);
            $table->integer('reorder_point')->default(10);
            $table->timestamps();
            
            $table->unique(['product_id', 'store_id']); // One record per product per store
        });

        // 4. Migrate Existing Stock to Main Store Inventory
        // Copy data from 'products' table to 'inventories' table for Store ID 1
        $products = DB::table('products')->get();
        foreach ($products as $product) {
            DB::table('inventories')->insert([
                'product_id' => $product->id,
                'store_id' => 1, // Main Store
                'stock' => $product->stock,
                'reorder_point' => $product->reorder_point ?? 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Add store_id to Transaction Tables
        $tables = ['sales', 'purchases', 'users', 'stock_adjustments'];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('store_id')->nullable()->after('id')->default(1)->constrained('stores');
                });
            }
        }
        
        // 6. Add Multi-Store Toggle to Settings if not exists
        $exists = DB::table('settings')->where('key', 'enable_multi_store')->exists();
        if (!$exists) {
            DB::table('settings')->insert([
                'key' => 'enable_multi_store',
                'value' => '0', // Off by default
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down()
    {
        // Reversing this is complex, but generally we drop the new tables and columns
        Schema::dropIfExists('inventories');
        Schema::table('sales', function (Blueprint $table) { $table->dropColumn('store_id'); });
        Schema::table('purchases', function (Blueprint $table) { $table->dropColumn('store_id'); });
        Schema::table('users', function (Blueprint $table) { $table->dropColumn('store_id'); });
        Schema::table('stock_adjustments', function (Blueprint $table) { $table->dropColumn('store_id'); });
        Schema::dropIfExists('stores');
    }
};