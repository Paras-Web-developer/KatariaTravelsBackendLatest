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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
            $table->string('supplier_code')->nullable()->after('type');
            $table->string('reservations_phone')->nullable()->after('supplier_code');
            $table->string('reservations_email')->nullable()->after('reservations_phone');
            $table->string('contact_name')->nullable()->after('reservations_email');
            $table->string('phone')->nullable()->after('contact_name');
            $table->string('fax')->nullable()->after('phone');
            $table->string('email')->nullable()->after('fax');
            $table->string('address')->nullable()->after('email');
            $table->integer('postal_code')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('supplier_code');
            $table->dropColumn('reservations_phone');
            $table->dropColumn('reservations_email');
            $table->dropColumn('contact_name');
            $table->dropColumn('phone');
            $table->dropColumn('fax');
            $table->dropColumn('email');
            $table->dropColumn('address');
            $table->dropColumn('postal_code');
        });
    }
};
