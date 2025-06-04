<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('invoices_mains', function (Blueprint $table) {
            $table->longText('itinerary')->change()->nullable();
        });
    }

    public function down()
    {
        Schema::table('invoices_mains', function (Blueprint $table) {
            $table->string('itinerary', 255)->nullable()->change(); // Reverting back
        });
    }
};
