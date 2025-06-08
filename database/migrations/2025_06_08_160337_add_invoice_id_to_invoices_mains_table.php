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
            $table->foreignId('invoice_id')->nullable()->after('id')->constrained('invoices_mains');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices_mains', function (Blueprint $table) {
             $table->dropForeign(index: ['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};
