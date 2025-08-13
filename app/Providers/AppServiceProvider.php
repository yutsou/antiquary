<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Lot;
use App\Observers\LotObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

            \URL::forceScheme('https');

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 註冊 Lot Observer
        Lot::observe(LotObserver::class);

        View::composer('layouts.member', function ($view) {
            $categories = Category::whereIsRoot()->get();
            $view->with('categories', $categories);
        });
    }
}
