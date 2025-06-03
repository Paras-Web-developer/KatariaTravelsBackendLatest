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
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_invoice_id')->nullable()->constrained('customer_invoices')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('payment_details_for', ['pax', 'supplier'])->default('pax');
            $table->boolean('adult')->default(1);
            $table->boolean('child')->default(0);
            $table->boolean('inf')->default(0);
            //adult 
            $table->double('a_base_fare', 10, 2)->default(0);
            $table->double('a_gst', 10, 2)->default(0);
            $table->double('a_taxes', 10, 2)->default(0);
            $table->double('a_sub_total', 10, 2)->default(0);
            //adult 
             $table->double('c_base_fare', 10, 2)->default(0);
             $table->double('c_gst', 10, 2)->default(0);
             $table->double('c_taxes', 10, 2)->default(0);
             $table->double('c_sub_total', 10, 2)->default(0);
            //adult 
            $table->double('i_base_fare', 10, 2)->default(0);
            $table->double('i_gst', 10, 2)->default(0);
            $table->double('i_taxes', 10, 2)->default(0);
            $table->double('i_sub_total', 10, 2)->default(0);

            $table->double('total_amount',10,2)->default(0);
            $table->double('paid_amount',10,2)->default(0);
            $table->double('balance',10,2)->default(0); 
            $table->string('payment_method_type')->nullable();
            $table->double('commission',10,2)->default(0);
            $table->boolean('payment_recieved_from_pax')->default(0);
            $table->boolean('payment_made_to_supplier')->default(0);
            $table->boolean('commission_recieved_from_supplier')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_details');
    }
};
