<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_credits', function (Blueprint $table) {
            $table->id('credit_id');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 10, 2); // Original debt amount
            $table->decimal('amount_paid', 10, 2)->default(0); // How much they have paid back
            $table->decimal('remaining_balance', 10, 2); // Calculated balance
            $table->date('due_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_credits');
    }
};