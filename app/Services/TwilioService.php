<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

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
}
