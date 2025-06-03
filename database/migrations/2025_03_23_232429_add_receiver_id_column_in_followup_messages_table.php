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
		Schema::table('followup_messages', function (Blueprint $table) {
			$table->unsignedBigInteger('receiver_id')->nullable()->after('follow_up_message');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('followup_messages', function (Blueprint $table) {
			//
		});
	}
};
