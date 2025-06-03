<?php

namespace App\Jobs;

use App\Models\Enquiry;
use App\Events\FollowUpMessageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Pusher\Pusher;

class SendFollowUpNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $enquiry;

    public function __construct(Enquiry $enquiry)
    {
    /// dd($enquiry);
        $this->enquiry = $enquiry;
    }

    public function handle()
    {
        // Pusher setup
        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );

        $pusher = new Pusher(
            '91c8a0fa751bddeef247',
            '371e7ab4c09707bb4d9c',
            '1919831',
            $options
        );

        // Prepare the data for Pusher
        $data['message'] = $this->enquiry->follow_up_message;
        $data['sender_id'] = $this->enquiry->created_by_user_id;
        $data['receiver_id'] = (int) $this->enquiry->assigned_to_user_id;
        $data['enquiry_id'] = $this->enquiry->id;
        $data['enquiry_title'] = $this->enquiry->title;
        $data['remark'] = $this->enquiry->remark;
        $data['timestamp'] = $this->enquiry->followed_up_at;

        // Trigger the Pusher event
        $pusher->trigger('my-channel', 'my-event', $data);

        // Optionally, broadcast the Laravel event as well
        broadcast(new FollowUpMessageEvent($this->enquiry))->toOthers();
    }
}
