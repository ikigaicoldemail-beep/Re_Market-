<?php

namespace App\Providers;

use App\Events\ProductCreated;
use App\Listeners\AutoPostProductToSocial;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ProductCreated::class => [
            AutoPostProductToSocial::class,
        ],
    ];

    /**
     * Enable the booting of all listening to events.
     */
    public function boot(): void
    {
        //
    }
}
