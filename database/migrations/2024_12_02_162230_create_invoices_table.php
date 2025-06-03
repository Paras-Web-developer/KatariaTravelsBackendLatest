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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->date('date');
            $table->string('supplier');
            $table->string('pnr')->nullable();
            $table->double('cost');
            $table->double('sold_fare');
            $table->double('amex_card')->nullable();
            $table->double('cibc_card')->nullable();
            $table->double('td_busness_visa_card')->nullable();
            $table->double('bmo_master_card')->nullable();
            $table->double('rajni_mam')->nullable();
            $table->double('td_fc_visa')->nullable();
            $table->double('ch_eq_ue')->nullable();
            $table->string('ticket_number')->nullable();
            $table->string('fnu')->nullable();
            $table->foreignId('airLine_id')->nullable()->constrained('air_lines')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('ticket_status')->nullable();
            $table->string('reference_number_of_et')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
