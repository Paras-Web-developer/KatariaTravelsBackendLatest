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
			$table->unsignedInteger("followupable_id")->nullable()->default(null)->after("id");
			$table->string("followupable_type")->nullable()->default(null)->after('id');
			$table->index(["followupable_id", "followupable_type"], "followupable_index");
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('followup_messages', function (Blueprint $table) {
			$table->dropIndex("followupable_index");

			$table->dropColumn("followupable_id");

			$table->dropColumn("followupable_type");
		});
	}
};
