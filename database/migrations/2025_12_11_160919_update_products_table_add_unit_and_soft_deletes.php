<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit')->default('pc')->after('name'); // e.g., pc, kg, pack
            $table->softDeletes(); // Adds 'deleted_at' column for archiving
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->dropSoftDeletes();
        });
    }
};