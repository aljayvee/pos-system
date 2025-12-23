<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Fix Role Column: Change from ENUM to String to support new roles
            // We use change() if using doctrine/dbal, or raw SQL if not.
            // Since Laravel default doesn't include dbal, we might need a raw statement for ENUM change.
            $table->string('role', 50)->change();

            // Add permissions if missing
            if (!Schema::hasColumn('users', 'permissions')) {
                $table->json('permissions')->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
            // Revert role is risky without knowing exact original enum options, skipping.
        });
    }
};
