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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('enquiry_type', ['hotel', 'car', 'flight', 'ticket'])->default('flight');
            $table->string('customer_name');
            $table->string('title')->nullable();
            $table->string('phone_number');
            $table->string('email');
            //package
            $table->enum('package_type', ['one_way', 'return_way' ,'multi_city'])->default('one_way');
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->enum('class_of_travel', ['economy', 'premium_economy' ,'business'])->default('economy');
            $table->integer('adult')->nullable();
            $table->integer('child')->nullable();
            $table->integer('infant')->nullable();

            $table->foreignId('air_line_id')->nullable()->constrained('air_lines')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enquiry_status_id')->nullable()->constrained('enquiry_statuses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('booking_reference')->nullable();
            $table->double('budget')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('remark')->nullable();
            $table->double('paid_amount')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
