<?php

use App\Jobs\SendFollowupRemindersJob;
use App\Jobs\AutoLogoutInactiveUsers;
use App\Jobs\AutoLogoutInactiveTokens;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();


Schedule::job(new SendFollowupRemindersJob())->everyMinute();
Schedule::job(new AutoLogoutInactiveUsers())->everyMinute();

