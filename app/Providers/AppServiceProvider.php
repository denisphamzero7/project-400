<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Models\OrderItemModel;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\UpdateCustomerLoyaltyPoints;
use App\Observers\OrderItemObserver;
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
        OrderItemModel::observe(OrderItemObserver::class);
    }
}
