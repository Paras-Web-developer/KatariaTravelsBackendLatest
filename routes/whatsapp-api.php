<?php

use App\Http\Controllers\Api\WhatsAppController;

Route::prefix('whatsapp')->group(function () {
    // Webhook (no authentication required)
    Route::post('/webhook', [WhatsAppController::class, 'webhook'])->middleware('webhook');

    // Test webhook endpoint (for debugging)
    Route::post('/webhook-test', [WhatsAppController::class, 'testWebhook'])->middleware('webhook');

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Conversations
        Route::get('/conversations', [WhatsAppController::class, 'getConversations']);
        Route::get('/conversations/{conversationId}/messages', [WhatsAppController::class, 'getMessages']);
        Route::post('/conversations/{conversationId}/mark-read', [WhatsAppController::class, 'markAsRead']);
        Route::post('/conversations/{conversationId}/toggle-archive', [WhatsAppController::class, 'toggleArchive']);
        Route::post('/conversations/{conversationId}/toggle-mute', [WhatsAppController::class, 'toggleMute']);
        Route::delete('/conversations/{conversationId}', [WhatsAppController::class, 'deleteConversation']);

        // Messages
        Route::post('/messages/text', [WhatsAppController::class, 'sendTextMessage']);
        Route::post('/messages/media', [WhatsAppController::class, 'sendMediaMessage']);
        Route::post('/messages/bulk', [WhatsAppController::class, 'sendBulkMessage']);
        Route::get('/messages/{messageSid}/status', [WhatsAppController::class, 'getMessageStatus']);

        // Statistics
        Route::get('/statistics', [WhatsAppController::class, 'getStatistics']);
    });
});