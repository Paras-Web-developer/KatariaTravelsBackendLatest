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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('date')->default(now());
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();
            $table->time('total_time')->nullable();
            $table->enum('status', ['present', 'leave', 'absent'])->default('present');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
