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
        Schema::table('world_airports', function (Blueprint $table) {
              $table->string('city')->nullable()->default(null)->change();
              $table->string('state')->nullable()->default(null)->change();
              $table->string('country')->nullable()->default(null)->change();
              $table->integer('elevation')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_airports', function (Blueprint $table) {
            
        });
    }
};
