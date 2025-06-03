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
        Schema::create('hotel_cruise_land_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_invoice_id')->nullable()->constrained('customer_invoices')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('package_type', ['flight' ,'cruise', 'land','package'])->default('flight');
            $table->string('package_name')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('operator')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_cruise_land_packages');
    }
};
