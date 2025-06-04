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
        Schema::create('other_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_source_id')->nullable()->constrained('enquiry_sources')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enquiry_status_id')->nullable()->constrained('enquiry_statuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone_number');
            $table->double('price_quote', 10, 2);
            $table->double('paid_amount', 10, 2);
            $table->string('invoice_number');
            $table->string('booking_reference_no')->nullable();
            $table->string('special_requests')->nullable();
            $table->string('service_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_services');
    }
};
