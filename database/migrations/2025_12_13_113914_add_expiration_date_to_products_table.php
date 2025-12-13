<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Check if column exists before adding it
            if (!Schema::hasColumn('products', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('reorder_point');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'expiration_date')) {
                $table->dropColumn('expiration_date');
            }
        });
    }
};