<?php

namespace App\Providers;

use App\Services\AI\AiArticleService;
use App\Services\AI\AiInternalLinker;
use App\Services\EventifyApiService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(EventifyApiService::class);
        $this->app->singleton(AiInternalLinker::class);
        $this->app->singleton(AiArticleService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        View::share('appUrl', rtrim(config('services.eventify.app_url'), '/'));
    }
}
