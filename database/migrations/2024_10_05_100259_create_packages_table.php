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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->nullable()->constrained('enquiries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('package_type', ['one_way', 'return_way' ,'multi_city'])->default('one_way');
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->enum('class_of_travel', ['economy', 'premium_economy' ,'business'])->default('economy');
            $table->integer('adult')->nullable();
            $table->integer('child')->nullable();
            $table->integer('infant')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
