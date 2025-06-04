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
        Schema::create('hotel_enquires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_source_id')->nullable()->constrained('enquiry_sources')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('enquiry_type', ['hotel', 'car', 'flight'])->default('hotel');
            $table->string('title')->nullable();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number');
            $table->string('destination');
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->integer('guest');
            $table->integer('room');
            $table->string('special_requests')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_enquires');
    }
};
