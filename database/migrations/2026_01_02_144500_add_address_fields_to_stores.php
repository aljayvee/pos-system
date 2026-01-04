<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('country')->nullable()->default('Philippines')->after('name');
            $table->string('region')->nullable()->after('country');
            $table->string('city')->nullable()->after('region');
            $table->string('barangay')->nullable()->after('city');
            $table->string('street')->nullable()->after('barangay');
            // We keep 'address' column as a calculated or full text fallback, or we can drop it later.
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['country', 'region', 'city', 'barangay', 'street']);
        });
    }
};
