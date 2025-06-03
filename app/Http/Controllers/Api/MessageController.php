<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Message;
use App\Models\PhoneNumber;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class MessageController extends BaseController
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function sendMessages(Request $request)
    {
        // Validate the input
        $request->validate([
            'message' => 'required|string',
            'phone_numbers' => 'required|array|min:1',
            'phone_numbers.*' => 'required|string',
            'image' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg,jpg,webp|max:2048',
        ]);

        // Save the message to the database
        $messageData = [
            'message' => $request->message,
            'sender' => env('TWILIO_NUMBER'),
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->image->store('upload', 'public');
            $messageData['image'] = $imagePath;
        }

        $message = Message::create($messageData);

        $results = [];

        foreach ($request->phone_numbers as $phoneNumber) {
            // Save phone number to the database
            PhoneNumber::updateOrCreate(
                [
                    'phone_number' => $phoneNumber,
                    'message_id' => $message->id,
                ],
                [
                    'phone_number' => $phoneNumber,
                    'message_id' => $message->id,
                ]
            );

            try {
                // Validate the number using Twilio Lookup API
                $lookup = $this->twilio->lookups->v1->phoneNumbers($phoneNumber)->fetch();
                if (!$lookup) {
                    throw new \Exception("Invalid phone number: $phoneNumber");
                }

                // Prepare Twilio message options
                $twilioMessageOptions = [
                    'from' => env('TWILIO_NUMBER'),
                    'body' => $request->message,
                ];

                // Add media URL if image exists
                if (isset($imagePath)) {
                    $twilioMessageOptions['mediaUrl'] = [url('storage/' . $imagePath)];
                }

                // Send the message using Twilio API
                $twilioResponse = $this->twilio->messages->create($phoneNumber, $twilioMessageOptions);

                $results[] = [
                    'phone_number' => $phoneNumber,
                    'message' => $message,
                    'status' => 'success',
                    'sid' => $twilioResponse->sid,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'phone_number' => $phoneNumber,
                    'message' => $message,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->success($results, 'Messages processed');
    }
}
