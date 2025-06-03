<?php

namespace App\Jobs;

use App\Events\FollowupReminderSent;
use App\Models\FollowupMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendFollowupRemindersJob implements ShouldQueue
{
	use Queueable;

	/**
	 * Create a new job instance.
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		FollowupMessage::where('followed_up_at', '<=', now())
			->where('receiver_id', '!=', null)
			->where('is_sent', false)
			->get()
			->each(function ($followup) {
				$followup->update(['is_sent' => true]);

				FollowupReminderSent::dispatch(
					$followup
				);
			});
	}
}
