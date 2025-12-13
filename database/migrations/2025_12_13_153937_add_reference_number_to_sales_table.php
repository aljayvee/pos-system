<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('sales', function (Blueprint $table) {
        // You can adjust 'after' to place it where you want, 
        // and 'nullable' if it's not always required.
        $table->string('reference_number')->nullable()->after('payment_method');
    });
}

public function down()
{
    Schema::table('sales', function (Blueprint $table) {
        $table->dropColumn('reference_number');
    });
}
};
