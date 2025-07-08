<?php

// app/Jobs/AutoLogoutInactiveTokens.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class AutoLogoutInactiveTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $cutoff = Carbon::now()->subMinutes(10);

        $count = PersonalAccessToken::where('last_seen_at', '<', $cutoff)->delete();

        Log::info("Auto logout: {$count} inactive tokens deleted.");
    }
}
