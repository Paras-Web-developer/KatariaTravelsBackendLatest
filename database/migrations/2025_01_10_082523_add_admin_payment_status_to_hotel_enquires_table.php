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
            $table->enum('admin_payment_status', ['approved', 'pending', 'no_action'])->default('pending')->after('status');
            $table->string('note')->after('admin_payment_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_enquires', function (Blueprint $table) {
            $table->dropColumn('admin_payment_status');
            $table->dropColumn('note');
        });
    }
};
