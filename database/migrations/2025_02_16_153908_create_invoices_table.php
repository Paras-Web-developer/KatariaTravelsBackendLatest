<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('invoices_mains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airLine_id')->nullable()->constrained('air_lines')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('invoice_number')->nullable();
            $table->string('sales_agent')->nullable();
            $table->string('itinerary')->nullable();
            $table->string('gds_type')->nullable();
            $table->string('gds_locator')->nullable();
            $table->date('booking_date')->nullable();
            $table->date('departure_date')->nullable();
            $table->string('ticket_number')->nullable();
            $table->string('travel_from')->nullable();
            $table->string('travel_to')->nullable();
            $table->json('airticket')->nullable();
            $table->json('insurance')->nullable();
            $table->json('misc')->nullable();
            $table->json('land_package')->nullable();
            $table->json('hotel')->nullable();
            $table->json('cruise')->nullable();


            $table->json('customer_details')->nullable();
           // $table->json('flight_details')->nullable();
           // $table->json('flight_from_pax')->nullable();
          //  $table->json('flight_to_supplier')->nullable();
            $table->json('passenger_details')->nullable();
          //  $table->json('airticket_from_pax')->nullable();
            //$table->json('airticket_to_supplier')->nullable();
         
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices_mains');
    }
};
