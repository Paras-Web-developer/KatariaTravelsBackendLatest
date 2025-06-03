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
            $table->json('insurance_passenger_details')->nullable()->after('cruise');
            $table->enum('valid_canadian_passport', ['true', 'false'])->default('false')->after('insurance_passenger_details');
            $table->enum('valid_travel_visa', ['true', 'false'])->default('false')->after('valid_canadian_passport');
            $table->enum('tourist_card', ['true', 'false'])->default('false')->after('valid_travel_visa');
            $table->enum('canadian_citizenship_or_prCard', ['true', 'false'])->default('false')->after('tourist_card');
            $table->text('special_remarks')->after('canadian_citizenship_or_prCard')->nullable();
            $table->text('other_remarks')->after('special_remarks')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices_mains', function (Blueprint $table) {
            $table->dropColumn('insurance_passenger_details');
            $table->dropColumn('valid_canadian_passport');
            $table->dropColumn('valid_travel_visa');
            $table->dropColumn('tourist_card');
            $table->dropColumn('canadian_citizenship_or_prCard');
            $table->dropColumn('special_remarks');
            $table->dropColumn('other_remarks');
        });
    }
};
