<?php

namespace App\Providers;

use App\Services\CastingImportData;
use Illuminate\Support\Facades\URL;
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
        $this->app->singleton(CastingImportData::class, function ($app) {
            return new CastingImportData();
        });
        $this->app->singleton(ConvertingDateTimeFromStringService::class, function ($app) {
            return new ConvertingDateTimeFromStringService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (request()->isSecure()) {
            URL::forceScheme('https');
        }
    }
}
