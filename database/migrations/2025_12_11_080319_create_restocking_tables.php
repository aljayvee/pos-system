<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Suppliers Table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id(); // supplier_id
            $table->string('name');
            $table->string('contact_info')->nullable();
            $table->timestamps();
        });

        // 2. Purchases Table (Header)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id(); // purchase_id
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->date('purchase_date');
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->timestamps();
        });

        // 3. Purchase Items Table (Details)
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2); // Cost per item at time of purchase
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
    }
};