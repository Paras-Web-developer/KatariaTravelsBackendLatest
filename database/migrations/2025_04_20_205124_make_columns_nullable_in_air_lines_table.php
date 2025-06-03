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
		Schema::table('air_lines', function (Blueprint $table) {
			$table->string('airline_code')->nullable()->default(null)->change();
			$table->string('country')->nullable()->default(null)->change();
			$table->double('price')->nullable()->default(null)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('air_lines', function (Blueprint $table) {
			//
		});
	}
};
