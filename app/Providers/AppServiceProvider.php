<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\UpdateCustomerLoyaltyPoints;
use App\Models\OrderModel;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderPaid::class => [
            SendOrderConfirmationEmail::class,
            UpdateCustomerLoyaltyPoints::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        OrderModel::observe(OrderObserver::class);

        // Manually register events and listeners because EventServiceProvider is missing
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }
}
