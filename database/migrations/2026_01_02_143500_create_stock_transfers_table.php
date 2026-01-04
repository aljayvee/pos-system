<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('from_store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('to_store_id')->constrained('stores')->onDelete('cascade');
            $table->integer('quantity');
            $table->foreignId('user_id')->constrained('users'); // Who performed the transfer
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfers');
    }
};
