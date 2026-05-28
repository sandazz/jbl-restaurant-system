<?php

namespace App\Providers;

use App\Events\LowStockDetected;
use App\Listeners\HandleLowStockDetected;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Policies\OrderPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Observe Order status changes to trigger inventory deductions
        Order::observe(OrderObserver::class);

        // Inventory low-stock event
        Event::listen(LowStockDetected::class, HandleLowStockDetected::class);

        // Order policy gates
        Gate::policy(Order::class, OrderPolicy::class);
    }
}
