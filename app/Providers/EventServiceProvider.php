<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\EmployeeEvent;
use App\Listeners\EmployeeListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        EmployeeEvent::class => [
            EmployeeListener::class,
        ],
        \App\Events\SocialMediaEvent::class => [
            \App\Listeners\SocialMediaListener::class,
        ],
    ];


    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
