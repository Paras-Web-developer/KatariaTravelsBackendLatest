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
        Schema::create('insurance_details', function (Blueprint $table) {
            $table->id();  
            $table->foreignId('customer_invoice_id')->nullable()->constrained('customer_invoices')->cascadeOnUpdate()->cascadeOnDelete();
            $table->json('first_name')->nullable();
            $table->json('last_name')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('policy_number')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->double('amount_insured',10,2)->nullable();
            $table->string('insurance_plan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_details');
    }
};
