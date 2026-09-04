<?php

namespace App\Providers;

use App\Contracts\AuthServiceContract;
use App\Contracts\DispatcherServiceContract;
use App\Contracts\DriverServiceContract;
use App\Contracts\RestStopServiceContract;
use App\Contracts\UserServiceContract;
use App\Services\AuthService;
use App\Services\DispatcherService;
use App\Services\DriverService;
use App\Services\RestStopService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceContract::class, AuthService::class);
        $this->app->bind(DispatcherServiceContract::class, DispatcherService::class);
        $this->app->bind(DriverServiceContract::class, DriverService::class);
        $this->app->bind(RestStopServiceContract::class, RestStopService::class);
        $this->app->bind(UserServiceContract::class, UserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
