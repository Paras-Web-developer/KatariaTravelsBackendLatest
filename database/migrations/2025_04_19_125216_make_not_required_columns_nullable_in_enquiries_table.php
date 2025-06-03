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
			$table->string('customer_name')->nullable()->default(null)->change();
			$table->string('phone_number')->nullable()->default(null)->change();
			$table->string('email')->nullable()->default(null)->change();
			$table->longText('follow_up_message')->nullable()->default(null)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('enquiries', function (Blueprint $table) {
			//
		});
	}
};
