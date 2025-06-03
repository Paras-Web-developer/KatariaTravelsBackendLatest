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
        Schema::table('other_services', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accept', 'reject'])->default('pending')->after('service_name');
            $table->enum('enquiry_payment_status', ['pending', 'paid', 'over_paid', 'not_paid'])->default('pending')->after('status');
            $table->enum('admin_payment_status', ['approved', 'pending', 'no_action'])->default('pending')->after('enquiry_payment_status');
            $table->string('note')->after('admin_payment_status')->nullable();

            $table->foreignId('created_by_user_id')->nullable()->after('enquiry_code')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('other_services', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('enquiry_payment_status');
            $table->dropColumn('admin_payment_status');
            $table->dropColumn('note');
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn('created_by_user_id');
            $table->dropForeign(['updated_by_user_id']);
            $table->dropColumn('updated_by_user_id');
        });
    }
};
