<?php

namespace App\Events;

use App\Models\Enquiry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class FollowUpMessageEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $enquiry;

    public function __construct(Enquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('enquiry.' . $this->enquiry->assigned_to_user_id);
    }
   
    public function broadcastWith()
    {
        
        return [
            'message' => $this->enquiry->follow_up_message,
            'sender_id' => $this->enquiry->created_by_user_id,
            'receiver_id' => $this->enquiry->assigned_to_user_id,
            'enquiry_id' => $this->enquiry->id,
            'remark' => $this->enquiry->remark,
            'title' => $this->enquiry->title,
            'timestamp' => $this->enquiry->followed_up_at,      
        ];
    }

}
