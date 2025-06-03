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
		Schema::table('invoices', function (Blueprint $table) {
			$table->string('supplier')->nullable()->default(null)->change();
			$table->string('pnr')->nullable()->default(null)->change();
			$table->double('cost')->nullable()->default(null)->change();
			$table->double('sold_fare')->nullable()->default(null)->change();
			$table->date('date')->nullable()->default(null)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('invoices', function (Blueprint $table) {
			//
		});
	}
};
