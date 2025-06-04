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
        Schema::create('car_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_source_id')->nullable()->constrained('enquiry_sources')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('car_type_id')->nullable()->constrained('car_types')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('enquiry_type', ['hotel', 'car', 'flight'])->default('car');
            $table->string('pick_up_location')->nullable();
            $table->string('drop_off_location');
            $table->date('pick_up_date')->nullable();
            $table->date('drop_off_date')->nullable();
            $table->string('title')->nullable();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number');
            $table->string('special_requests')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_enquiries');
    }
};
