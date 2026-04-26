<?php

namespace App\Providers;

use App\Services\EventifyApiService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(EventifyApiService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('appUrl', rtrim(config('services.eventify.app_url'), '/'));
    }
}
