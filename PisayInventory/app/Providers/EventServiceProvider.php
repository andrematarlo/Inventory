<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // Add your custom events here
        'App\Events\UserLoggedIn' => [
            'App\Listeners\LogSuccessfulLogin',
        ],
        'App\Events\ItemCreated' => [
            'App\Listeners\LogInventoryChange',
        ],
        'App\Events\ItemUpdated' => [
            'App\Listeners\LogInventoryChange',
        ],
        'App\Events\ItemDeleted' => [
            'App\Listeners\LogInventoryChange',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
