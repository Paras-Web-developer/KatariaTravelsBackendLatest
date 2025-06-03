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
        Schema::table('air_lines', function (Blueprint $table) {
            $table->string('airline_code')->nullable()->after('airline_name');
            $table->string('country')->nullable()->after('airline_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_lines', function (Blueprint $table) {
            $table->dropColumn(['airline_code', 'country']);
        });
    }
};
