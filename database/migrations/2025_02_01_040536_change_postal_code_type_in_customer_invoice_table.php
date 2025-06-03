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
        Schema::table('flight_details', function (Blueprint $table) {
            $table->string('airLine')->nullable()->after('id');
            $table->string('dep_time')->change();
            $table->string('arr_time')->change();
            $table->string('date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flight_details', function (Blueprint $table) {
            $table->dropColumn('airLine');
            $table->time('dep_time')->change();
            $table->time('arr_time')->change();
            $table->time('date')->change();

        });
    }
};
