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
            $table->unsignedBigInteger('parent_id')->index()->nullable()->after('id');
            $table->enum('airticket_include', ['true', 'false'])->default('false')->after('insurance_passenger_details');
            $table->enum('insurance_include', ['true', 'false'])->default('false')->after('airticket_include');
            $table->enum('misc_include', ['true', 'false'])->default('false')->after('insurance_include');
            $table->enum('land_package_include', ['true', 'false'])->default('false')->after('misc_include');
            $table->enum('hotel_include', ['true', 'false'])->default('false')->after('land_package_include');
            $table->enum('cruise_include', ['true', 'false'])->default('false')->after('hotel_include');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices_mains', function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('airticket_include');
            $table->dropColumn('insurance_include');
            $table->dropColumn('misc_include');
            $table->dropColumn('land_package_include');
            $table->dropColumn('hotel_include');
            $table->dropColumn('cruise_include');
        });
    }
};
