<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        app('Dingo\Api\Auth\Auth')->extend('fae', function ($app) {
            return new \App\Api\v1\Providers\FaeAuthorizationProvider();
        });

        app('Dingo\Api\Auth\Auth')->extend('guest', function ($app) {
            return new \App\Api\v1\Providers\FaeGuestAuthorizationProvider();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
