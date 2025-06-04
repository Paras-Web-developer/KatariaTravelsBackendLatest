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
            $table->unsignedBigInteger('parent_id')->index()->nullable()->after('id');
            // $table->foreignId('created_by_user_id')->nullable()->after('id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->after('updated_by_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enquiry_payment_status_id')->nullable()->after('enquiry_source_id')->constrained('enquiry_payment_statuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->double('budget')->nullable()->after('destination');
            $table->string('invoice_number')->nullable()->after('budget');
            $table->double('paid_amount')->nullable()->after('invoice_number');
            $table->enum('status', ['pending', 'accept', 'reject'])->default('pending')->after('special_requests');
            $table->softDeletes()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_enquires', function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('updated_by_user_id');
            $table->dropColumn('assigned_to_user_id');
            $table->dropColumn('enquiry_payment_status_id');
            $table->dropColumn('budget');
            $table->dropColumn('invoice_number');
            $table->dropColumn('paid_amount');
            $table->dropColumn('status');
            $table->dropColumn('soft_delete');

        });
    }
};
