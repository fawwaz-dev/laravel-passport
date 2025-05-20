<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use App\Contracts\MakesInternalRequests;
use App\Services\InternalRequest;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MakesInternalRequests::class, InternalRequest::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
