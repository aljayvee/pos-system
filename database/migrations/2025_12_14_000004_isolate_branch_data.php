<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Isolate Customers & Suppliers
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->default(1)->after('id')->constrained('stores');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->default(1)->after('id')->constrained('stores');
        });

        // 2. Isolate Audit Logs
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->default(1)->after('id')->constrained('stores');
        });

        // 3. Isolate Settings (Make them per-branch)
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->default(1)->after('id')->constrained('stores');
            // Drop unique key on 'key' if it exists, because now we need unique(['key', 'store_id'])
            $table->dropUnique(['key']); 
            $table->unique(['key', 'store_id']);
        });
        
        // 4. Global Toggle Exception
        // The "enable_multi_store" setting should remain Global (Store ID 1 or NULL). 
        // We will handle this in the Controller logic.
    }

    public function down()
    {
        // Drop columns if rolling back
        $tables = ['customers', 'suppliers', 'activity_logs', 'settings'];
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) { $table->dropColumn('store_id'); });
        }
    }
};