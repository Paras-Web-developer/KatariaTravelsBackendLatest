<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('world_airports', function (Blueprint $table) {
            $table->id();
            $table->string('icao')->unique();
            $table->string('iata')->nullable();
            $table->string('name');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country');
            $table->integer('elevation')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->string('tz');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_airports');
    }
};
