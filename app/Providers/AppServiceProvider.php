<?php

namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        // Paginator::defaultView('vendor.pagination.custom');
        // Paginator::useBootstrap();

        if ($this->app->runningInConsole()) {
            Artisan::call('queue:work');
            Artisan::call('mqtt:listen');
            Artisan::call('reverb:start');
        }

    }
}
