<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        \Log::info('Scheduler is working!');
        // $schedule->command('followup:send')->everyFiveMinutes();
        $schedule->call(function () {
            \Log::info('Test schedule is working!');
        })->everyMinute();

        // $schedule->call(function () {
        //     \App\Models\User::where('user_login', 1)
        //         ->where('last_seen_at', '<', now()->subMinutes(10))
        //         ->update(['user_login' => 0]);
        // })->everyFiveMinutes(); // Or everyTenMinutes()

        $schedule->call(function () {
            \App\Models\User::where('user_login', 1)
                ->where('last_seen_at', '<', now()->subMinutes(10)) // Customize inactivity window
                ->update(['user_login' => 0]);

            \Log::info('Inactive users auto-logged out at ' . now());
        })->everyFiveMinutes();

        // Existing job
        $schedule->job(new \App\Jobs\AutoLogoutInactiveUsers)->everyFiveMinutes();
        $schedule->job(new \App\Jobs\SendFollowupRemindersJob)->everyFiveMinutes();
         $schedule->job(new \App\Jobs\AutoLogoutInactiveTokens)->everyFiveMinutes();

    }




    /**
     * Register the commands for the application.
     */

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
