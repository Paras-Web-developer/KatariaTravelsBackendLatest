<?php

namespace App\Listeners;

use App\Events\EmployeeMultiCityEvent;
use App\Notifications\SendEmployeeNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class EmployeeMultiCityListeners
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
    public function handle(EmployeeMultiCityEvent $event): void
    {
         $employee = $event->employee;
    
       
        $receiverUser = User::find($employee->assigned_to_user_id);
        $title = $receiverUser->title ?? 'User';
        $message = "{$title} has been assigned a new task of multiple cities Flight Enquiries";

        if ($receiverUser) {
            Notification::send($receiverUser, new SendEmployeeNotification($employee, $message));
        }
    }
}
