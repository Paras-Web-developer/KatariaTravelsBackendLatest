<?php

namespace App\Listeners;

use App\Events\EmployeeEvent;
use App\Models\User;
use App\Notifications\SendEmployeeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;

class EmployeeListener
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
   
    public function handle(EmployeeEvent $event): void
    {
        $employee = $event->employee;
        $receiverUser = User::find($employee->assigned_to_user_id);
        
        // Use null coalescing to handle null title
        $title = $receiverUser->title ?? 'User';
        $message = "{$title} has been assigned a new Flight Enquiry";

        if ($receiverUser) {
            Notification::send($receiverUser, new SendEmployeeNotification($employee, $message));
        }
    }
}
