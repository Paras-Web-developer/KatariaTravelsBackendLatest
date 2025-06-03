<?php

namespace App\Listeners;

use App\Events\EmployeeCarEnquiryEvent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;


use App\Notifications\SendEmployeeNotification;
class EmployeeCarEnquiryListeners
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
    public function handle(EmployeeCarEnquiryEvent $event): void
    {
       
        $carEnquire = $event->carEnquire;
    
       
        $receiverUser = User::find($carEnquire->assigned_to_user_id);
       
        $message = "{$receiverUser->name} has been assigned a new Flight Enquiry";

        if ($receiverUser) {
            Notification::send($receiverUser, new SendEmployeeNotification($carEnquire, $message));
        }
    }
}
