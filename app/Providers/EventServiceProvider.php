<?php

namespace App\Providers;

use App\Events\OfferCreated;
use App\Events\OfferStatusChanged;
use App\Listeners\HandleOfferCreated;
use App\Listeners\HandleOfferStatusChanged;
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
        OfferCreated::class => [
            HandleOfferCreated::class,
        ],
        OfferStatusChanged::class => [
            HandleOfferStatusChanged::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        // ... existing code ...

        \App\Models\UserTransaction::observe(\App\Observers\UserTransactionObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
