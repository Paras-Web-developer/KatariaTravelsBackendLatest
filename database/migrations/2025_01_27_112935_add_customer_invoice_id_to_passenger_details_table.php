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
        Schema::table('passenger_details', function (Blueprint $table) {
            $table->foreignId('customer_invoice_id')->nullable()->after('id')->constrained('customer_invoices')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passenger_details', function (Blueprint $table) {
            $table->dropForeign(['customer_invoice_id']);
            $table->dropColumn('customer_invoice_id');
        });
    }
};
