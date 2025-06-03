<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioController extends Controller
{
    // private $twilio;

    // public function __construct()
    // {
    //     $this->twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    // }

    // public function sendMessage(Request $request)
    // {
    //     $request->validate([
    //         'message' => 'required|string',
    //         'phone_numbers' => 'required|array',
    //         'phone_numbers.*' => 'required|string',
    //     ]);

    //     $message = $request->message;
    //     $phoneNumbers = $request->phone_numbers;

    //     $results = [];
    //     foreach ($phoneNumbers as $number) {
    //         try {
    //             $response = $this->twilio->messages->create(
    //                 $number,
    //                 [
    //                     'from' => env('TWILIO_NUMBER'),
    //                     'body' => $message,
    //                 ]
    //             );
    //             $results[] = ['number' => $number, 'sid' => $response->sid];
    //         } catch (\Exception $e) {
    //             $results[] = ['number' => $number, 'error' => $e->getMessage()];
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $results,
    //     ]);
    // }

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
