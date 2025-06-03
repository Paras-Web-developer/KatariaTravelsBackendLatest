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
        Schema::create('credit_card_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airLine_id')->nullable()->constrained('air_lines')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('holder_name');
            $table->string('type')->nullable();
            $table->string('card_number', 16)->nullable();
            $table->date('expire_date');
            $table->integer('cvv');
            $table->double('amount')->nullable();
            $table->date('travel_date');
            $table->string('transportation')->nullable();
            $table->string('country');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('signature')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_card_forms');
    }
};
