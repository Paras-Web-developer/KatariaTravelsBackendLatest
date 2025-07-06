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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('whatsapp_conversations')->onDelete('cascade');
            $table->string('message_id')->unique(); // Twilio message SID
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->enum('type', ['text', 'image', 'video', 'audio', 'document', 'location', 'contact'])->default('text');
            $table->text('content'); // Message content or file path
            $table->string('file_url')->nullable(); // For media files
            $table->string('file_name')->nullable(); // Original file name
            $table->string('file_type')->nullable(); // MIME type
            $table->integer('file_size')->nullable(); // File size in bytes
            $table->json('metadata')->nullable(); // Additional message metadata
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id']);
            $table->index(['message_id']);
            $table->index(['direction']);
            $table->index(['status']);
            $table->index(['sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};