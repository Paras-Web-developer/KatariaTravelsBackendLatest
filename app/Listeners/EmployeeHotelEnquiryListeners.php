<?php

namespace App\Listeners;

use App\Events\EmployeeHotelEnquiryEvent;
use App\Models\User;
use App\Notifications\SendEmployeeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class EmployeeHotelEnquiryListeners
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
    public function handle(EmployeeHotelEnquiryEvent $event): void
    {
       
        $hotelEnquire = $event->hotelEnquire;
    
       
        $receiverUser = User::find($hotelEnquire->assigned_to_user_id);
       
        $message = "{$receiverUser->name} has been assigned a new Flight Enquiry";

        if ($receiverUser) {
            Notification::send($receiverUser, new SendEmployeeNotification($hotelEnquire, $message));
        }
    }
}
