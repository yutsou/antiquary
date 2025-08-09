<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OrderService;
use App\Services\MergeShippingRequestService;

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
            $mergeShippingRequestService = app(MergeShippingRequestService::class);
            $mergeShippingRequestCount = $mergeShippingRequestService->getMergeShippingRequestCount();
            $view->with('orderCount', $orderCount);
            $view->with('mergeShippingRequestCount', $mergeShippingRequestCount);
        });
    }
}
