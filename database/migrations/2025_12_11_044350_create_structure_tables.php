<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    // Modify Users Table for Roles
    Schema::table('users', function (Blueprint $table) {
        $table->enum('role', ['admin', 'cashier'])->default('cashier');
        $table->boolean('is_active')->default(true);
    });

    // Products Table
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('sku')->unique()->nullable(); // For Barcode
        $table->decimal('price', 10, 2);
        $table->decimal('cost', 10, 2)->nullable(); // For Profit Reports
        $table->integer('stock')->default(0);
        $table->integer('alert_stock')->default(10); // Low stock alert
        $table->timestamps();
    });

    // Customers (For Utang)
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('contact')->nullable();
        $table->timestamps();
    });

    // Sales (Transactions)
    Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained(); // Who sold it
        $table->foreignId('customer_id')->nullable()->constrained(); // Who bought it
        $table->decimal('total_amount', 10, 2);
        $table->decimal('amount_paid', 10, 2);
        $table->enum('payment_method', ['cash', 'digital', 'credit']); // Credit = Utang
        $table->timestamps();
    });
    
    // Sale Items (Pivot table for Sale-Product)
    Schema::create('sale_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sale_id')->constrained()->onDelete('cascade');
        $table->foreignId('product_id')->constrained();
        $table->integer('quantity');
        $table->decimal('price', 10, 2); // Capture price at moment of sale
        $table->timestamps();
    });
}
};
