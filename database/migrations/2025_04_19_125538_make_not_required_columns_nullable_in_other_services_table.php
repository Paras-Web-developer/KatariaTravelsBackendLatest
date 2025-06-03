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
			$table->string('title')->nullable()->default(null)->change();
			$table->string('customer_name')->nullable()->default(null)->change();
			$table->string('email')->nullable()->default(null)->change();
			$table->string('phone_number')->nullable()->default(null)->change();
			$table->double('price_quote')->nullable()->default(null)->change();
			$table->double('paid_amount')->nullable()->default(null)->change();
			$table->string('service_name')->nullable()->default(null)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('other_services', function (Blueprint $table) {
			//
		});
	}
};
