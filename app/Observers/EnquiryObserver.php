<?php

namespace App\Observers;

use App\Models\Enquiry;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class EnquiryObserver
{
    public function updated(Enquiry $enquiry)
    {
        // Check if the followed_up_at field was updated
        if ($enquiry->wasChanged('followed_up_at') && $enquiry->followed_up_at !== null) {
            // Prepare the message
            $message = "Follow-up message for enquiry ID {$enquiry->id}: {$enquiry->follow_up_message}";

            // Send the message using Pusher
            $options = [
                'cluster' => 'ap2',
                'useTLS' => true,
            ];

            $pusher = new Pusher(
                '91c8a0fa751bddeef247',
                '371e7ab4c09707bb4d9c',
                '1919831',
                $options
            );
            // $pusher = new Pusher(
            //     env('PUSHER_APP_KEY'),
            //     env('PUSHER_APP_SECRET'),
            //     env('PUSHER_APP_ID'),
            //     $options
            // );

            $data = [
                'message' => $message,
                'enquiry_id' => $enquiry->id,
            ];

            $pusher->trigger('enquiries-channel', 'follow-up-event', $data);

            // Log for debugging
            Log::info("Follow-up message sent for enquiry ID {$enquiry->id}");
        }
    }
}
