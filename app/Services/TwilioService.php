<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        //dd($sid , $token);

        // Debugging: Log credentials
        if (!$sid || !$token) {
            Log::error('Twilio Credentials Missing: TWILIO_SID or TWILIO_AUTH_TOKEN not found.');
            throw new \Exception("Twilio Credentials Missing");
        }

        $this->twilio = new Client($sid, $token);
    }

    public function sendWhatsAppMessage($to, $body)
    {
        try {
            $message = $this->twilio->messages->create(
                "whatsapp:" . $to,
                [
                    "from" => env('TWILIO_WHATSAPP_NUMBER'),
                    "body" => $body
                ]
            );

            return $message->sid;
        } catch (\Exception $e) {
            Log::error('Twilio API Error: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function sendWhatsAppMedia($to, $mediaUrl, $body = null)
    {
        try {
            $params = [
                "from" => env('TWILIO_WHATSAPP_NUMBER'),
                "mediaUrl" => [$mediaUrl]
            ];

            if ($body) {
                $params["body"] = $body;
            }

            $message = $this->twilio->messages->create(
                "whatsapp:" . $to,
                $params
            );

            return $message->sid;
        } catch (\Exception $e) {
            Log::error('Twilio Media API Error: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function sendWhatsAppDocument($to, $documentUrl, $body = null)
    {
        try {
            $params = [
                "from" => env('TWILIO_WHATSAPP_NUMBER'),
                "mediaUrl" => [$documentUrl]
            ];

            if ($body) {
                $params["body"] = $body;
            }

            $message = $this->twilio->messages->create(
                "whatsapp:" . $to,
                $params
            );

            return $message->sid;
        } catch (\Exception $e) {
            Log::error('Twilio Document API Error: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function sendWhatsAppLocation($to, $latitude, $longitude, $label = null, $address = null)
    {
        try {
            $params = [
                "from" => env('TWILIO_WHATSAPP_NUMBER'),
                "persistentAction" => [
                    "location" => [
                        "latitude" => $latitude,
                        "longitude" => $longitude,
                        "label" => $label,
                        "address" => $address
                    ]
                ]
            ];

            $message = $this->twilio->messages->create(
                "whatsapp:" . $to,
                $params
            );

            return $message->sid;
        } catch (\Exception $e) {
            Log::error('Twilio Location API Error: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function getMessageStatus($messageSid)
    {
        try {
            $message = $this->twilio->messages($messageSid)->fetch();
            return [
                'sid' => $message->sid,
                'status' => $message->status,
                'direction' => $message->direction,
                'from' => $message->from,
                'to' => $message->to,
                'body' => $message->body,
                'dateCreated' => $message->dateCreated,
                'dateSent' => $message->dateSent,
                'dateUpdated' => $message->dateUpdated,
            ];
        } catch (\Exception $e) {
            Log::error('Twilio Get Message Error: ' . $e->getMessage());
            return null;
        }
    }

    public function handleWebhook($request)
    {
        try {
            $data = $request->all();
            Log::info('WhatsApp Webhook received:', $data);

            // Dispatch job to process webhook asynchronously
            \App\Jobs\ProcessWhatsAppWebhook::dispatch($data);

            return [
                'success' => true,
                'message' => 'Webhook queued for processing'
            ];

        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
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

    public function uploadMedia($file)
    {
        try {
            $path = $file->store('whatsapp-media', 'public');
            return asset('storage/' . $path);
        } catch (\Exception $e) {
            Log::error('Media upload error: ' . $e->getMessage());
            throw $e;
        }
    }
}
