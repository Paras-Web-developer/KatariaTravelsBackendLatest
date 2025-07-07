<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;

class ProcessWhatsAppWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhookData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $webhookData)
    {
        $this->webhookData = $webhookData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $data = $this->webhookData;

            // Extract phone number from the "from" field
            $from = str_replace('whatsapp:', '', $data['From'] ?? '');
            $to = str_replace('whatsapp:', '', $data['To'] ?? '');
            $messageSid = $data['MessageSid'] ?? '';
            $body = $data['Body'] ?? '';
            $numMedia = $data['NumMedia'] ?? 0;

            if (empty($from) || empty($messageSid)) {
                Log::warning('Invalid webhook data received', $data);
                return;
            }

            // Find or create conversation
            $conversation = WhatsAppConversation::firstOrCreate(
                ['phone_number' => $from],
                [
                    'name' => $data['ProfileName'] ?? null,
                    'type' => 'individual',
                    'last_message_at' => now(),
                    'last_message' => $body
                ]
            );

            // Determine message type
            $messageType = 'text';
            $fileUrl = null;
            $fileName = null;
            $fileType = null;
            $fileSize = null;

            if ($numMedia > 0) {
                $messageType = $this->determineMediaType($data['MediaContentType0'] ?? '');
                $fileUrl = $data['MediaUrl0'] ?? null;
                $fileName = $data['MediaFileName0'] ?? null;
                $fileType = $data['MediaContentType0'] ?? null;
                $fileSize = $data['MediaSize0'] ?? null;
            }

            // Create message record
            $message = WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'message_id' => $messageSid,
                'direction' => 'inbound',
                'type' => $messageType,
                'content' => $body,
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'status' => 'delivered',
                'delivered_at' => now(),
                'metadata' => $data
            ]);

            // Update conversation last message
            $conversation->update([
                'last_message_at' => now(),
                'last_message' => $body
            ]);

            Log::info('WhatsApp webhook processed successfully', [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'phone_number' => $from
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp webhook: ' . $e->getMessage(), [
                'webhook_data' => $this->webhookData,
                'exception' => $e
            ]);
        }
    }

    private function determineMediaType($contentType)
    {
        if (str_starts_with($contentType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($contentType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($contentType, 'audio/')) {
            return 'audio';
        } elseif (str_starts_with($contentType, 'application/')) {
            return 'document';
        }

        return 'text';
    }
}