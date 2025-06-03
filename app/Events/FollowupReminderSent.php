<?php

namespace App\Events;

use App\Models\Enquiry;
use App\Models\FollowupMessage;
use App\Models\HotelEnquire;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FollowupReminderSent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public $followupMessage;

	/**
	 * Create a new event instance.
	 */
	public function __construct(FollowupMessage $followupMessage)
	{
		$followupMessage->load('followupable');
		$this->followupMessage = $followupMessage;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return array<int, \Illuminate\Broadcasting\Channel>
	 */
	public function broadcastOn(): array
	{

		return [
			new Channel('followup-channel.' . $this->followupMessage->receiver_id),
		];
	}

	public function broadcastWith()
	{
		return [
			'message' => "Follow-up message: {$this->followupMessage->follow_up_message}",
			'enquiry_id' => $this->followupMessage->enquiry_id,
			'receiver_id' => $this->followupMessage->receiver_id,
			'followupMessage' => $this->followupMessage,
			'timestamp' => now()->toDateTimeString(),
		];
	}
}
