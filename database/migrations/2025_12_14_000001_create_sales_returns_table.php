<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Who processed the return
            $table->integer('quantity');
            $table->decimal('refund_amount', 10, 2); // Amount returned to customer
            $table->string('reason')->nullable(); // e.g., Damaged, Expired, Change of Mind
            $table->enum('condition', ['good', 'damaged'])->default('good'); // 'good' = return to stock, 'damaged' = dispose
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_returns');
    }
};