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
		Schema::table('world_airports', function (Blueprint $table) {
			$table->decimal('lat', 10, 7)->nullable()->default(null)->change();
			$table->decimal('lon', 10, 7)->nullable()->default(null)->change();
			$table->string('tz')->nullable()->default(null)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('world_airports', function (Blueprint $table) {});
	}
};
