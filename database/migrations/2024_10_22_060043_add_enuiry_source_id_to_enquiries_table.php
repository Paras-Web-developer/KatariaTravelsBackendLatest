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
        Schema::table('enquiries', function (Blueprint $table) {
            $table->foreignId('enquiry_source_id')->nullable()->after('assigned_to_user_id')->constrained('enquiry_sources')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enquiry_payment_status_id')->nullable()->after('enquiry_source_id')->constrained('enquiry_payment_statuses')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn(['enquiry_source_id', 'enquiry_payment_status_id']);
        });
    }
};
