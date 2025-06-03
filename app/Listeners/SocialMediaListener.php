<?php

namespace App\Listeners;

use App\Events\SocialMediaEvent;
use App\Models\User;
use App\Notifications\SocialMediaEnquiryNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;

class SocialMediaListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */

    public function handle(SocialMediaEvent $event): void
    {
        $SocialMediaEnquiry = $event->socialMediaResponse;

        // Send notification to the email
        Notification::route('mail', $SocialMediaEnquiry->email)
            ->notify(new SocialMediaEnquiryNotification($SocialMediaEnquiry));
    }
}
