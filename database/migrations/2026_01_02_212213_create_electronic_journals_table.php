<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('electronic_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('type'); // 'INVOICE', 'VOID', 'X-READING', 'Z-READING'
            $table->string('reference_number')->nullable(); // SI Number or Report ID
            $table->dateTime('generated_at');
            $table->longText('content'); // The exact text content printed
            $table->json('data_snapshot')->nullable(); // Structured data for easier querying
            $table->timestamps();

            $table->index(['store_id', 'generated_at']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electronic_journals');
    }
};
