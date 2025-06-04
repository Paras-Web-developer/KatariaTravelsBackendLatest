<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FollowupMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendFollowUpMessages extends Command
{
    protected $signature = 'followup:send';
    protected $description = 'Send follow-up messages that are due';

    public function handle()
    {
        $now = Carbon::now();

        $followups = FollowupMessage::where('followed_up_at', '<=', $now)
            ->where('is_sent', false)
            ->get();

        if ($followups->isEmpty()) {
            $this->info('No follow-up messages to send.');
            return;
        }

        foreach ($followups as $followup) {
            // ✅ Log the follow-up message before sending
            Log::info("Sending Follow-up ID: {$followup->id}, Message: {$followup->follow_up_message}");

            // ✅ Send follow-up logic (e.g., email, notification, SMS, etc.)
            // Example: Send email (if you have an email field in FollowupMessage model)
            // Mail::to($followup->user->email)->send(new FollowupEmail($followup));

            // ✅ Mark as sent
            $followup->update(['is_sent' => true]);
        }

        $this->info('Follow-up messages sent successfully.');
    }
}
