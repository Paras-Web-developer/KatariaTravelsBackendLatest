<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Notifications\CallReminderNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;

class NotificationController extends BaseController
{

    public function list(Request $request){
        $user = auth()->user();
        // $notifications = Notification::get();
       
        $notifications = $user->notifications()->latest()->get();
       // dd($notifications);
        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }
    public function sendNotification(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
        ]);

        $user = User::findOrFail($user->id);
        $enquiry = \App\Models\Enquiry::findOrFail($request->enquiry_id);

        $user->notify(new CallReminderNotification($enquiry));

        return response()->json([
            'message' => 'Notification sent successfully!',
        ]);
    }

    public function sendAutomaticNotifications()
    {
        $enquiries = \App\Models\Enquiry::whereNull('followed_up_at')->get();

        foreach ($enquiries as $enquiry) {
            $user = $enquiry->assignedToUser;

            if ($user) {
                $user->notify(new CallReminderNotification($enquiry));
            }
        }

        return response()->json([
            'message' => 'Automatic notifications sent successfully!',
        ]);
    }
}
