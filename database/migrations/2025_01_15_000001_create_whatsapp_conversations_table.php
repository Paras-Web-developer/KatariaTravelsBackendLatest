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
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number'); // WhatsApp phone number
            $table->string('name')->nullable(); // Contact name
            $table->string('profile_picture')->nullable(); // Profile picture URL
            $table->enum('type', ['individual', 'group'])->default('individual');
            $table->string('group_id')->nullable(); // For group conversations
            $table->string('group_name')->nullable(); // For group conversations
            $table->json('participants')->nullable(); // For group conversations
            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->timestamps();

            $table->index(['phone_number']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};