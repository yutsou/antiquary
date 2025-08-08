<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OrderService;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
         view()->composer('layouts.auctioneer', function ($view) {
            $orderService = app(OrderService::class);
            $orderCount = $orderService->getOrderCountByStatus([11, 13, 52]);
            $view->with('orderCount', $orderCount);
        });
    }
}
