<?php

namespace App\Providers;

use App\Contracts\AuthServiceContract;
use App\Contracts\BidServiceContract;
use App\Contracts\DispatcherServiceContract;
use App\Contracts\DriverServiceContract;
use App\Contracts\RestStopServiceContract;
use App\Contracts\RouteServiceContract;
use App\Contracts\RouteStopServiceContract;
use App\Contracts\ServiceServiceContract;
use App\Contracts\UserManagementServiceContract;
use App\Contracts\UserServiceContract;
use App\Services\AuthService;
use App\Services\BidService;
use App\Services\DispatcherService;
use App\Services\DriverService;
use App\Services\RestStopService;
use App\Services\RouteService;
use App\Services\RouteStopService;
use App\Services\ServiceService;
use App\Services\UserManagementService;
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
        $this->app->bind(BidServiceContract::class, BidService::class);
        $this->app->bind(DispatcherServiceContract::class, DispatcherService::class);
        $this->app->bind(DriverServiceContract::class, DriverService::class);
        $this->app->bind(RestStopServiceContract::class, RestStopService::class);
        $this->app->bind(RouteServiceContract::class, RouteService::class);
        $this->app->bind(RouteStopServiceContract::class, RouteStopService::class);
        $this->app->bind(ServiceServiceContract::class, ServiceService::class);
        $this->app->bind(UserManagementServiceContract::class, UserManagementService::class);
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
