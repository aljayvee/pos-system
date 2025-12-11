<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_credit_id')->constrained('customer_credits', 'credit_id')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->timestamp('payment_date');
            $table->foreignId('user_id')->constrained(); // Who processed the payment
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_payments');
    }
};