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
            $table->foreignId('created_by_user_id')->nullable()->after('id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_enquires', function (Blueprint $table) {
            $table->dropColumn('created_by_user_id');
        });
    }
};
