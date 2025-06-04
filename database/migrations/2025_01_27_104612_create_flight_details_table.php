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
        Schema::create('flight_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airLine_id')->nullable()->constrained('air_lines')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->date('date')->nullable();
            $table->string('flight_no')->nullable(); // Fixed the column name
            $table->time('dep_time')->nullable();
            $table->time('arr_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_details');
    }
};
