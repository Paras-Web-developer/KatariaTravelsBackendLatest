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
        Schema::table('hotel_enquires', function (Blueprint $table) {
            $table->enum('enquiry_payment_status', ['pending', 'paid','over_paid','not_paid'])->default('pending')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_enquires', function (Blueprint $table) {
            $table->dropColumn('enquiry_payment_status');
        });
    }
};
