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
        Schema::table('invoices_mains', function (Blueprint $table) {
            $table->foreignId('flight_enquiry_id')->nullable()->after('id')->constrained('enquiries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('hotel_enquire_id')->nullable()->after('flight_enquiry_id')->constrained('hotel_enquires')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices_mains', function (Blueprint $table) {
            $table->dropForeign(['flight_enquiry_id']);
            $table->dropColumn('flight_enquiry_id');
            $table->dropForeign(['hotel_enquire_id']);
            $table->dropColumn('hotel_enquire_id');
        });
    }
};
