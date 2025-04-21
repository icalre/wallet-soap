<?php

namespace App\Providers;

use App\Services\SoapService;
use App\Services\SoapWalletServer;
use Illuminate\Support\ServiceProvider;

class SoapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SoapService::class, function ($app) {
            return new SoapService();
        });

        $this->app->singleton(SoapWalletServer::class, function ($app) {
            return new SoapWalletServer($app->make(SoapService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
