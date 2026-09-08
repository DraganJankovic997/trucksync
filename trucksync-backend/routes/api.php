<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DispatcherController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RestStopController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\RouteStopController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/register', 'register')->name('auth.register');
    Route::post('/login', 'login')->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', 'me')->name('auth.me');
        Route::post('/logout', 'logout')->name('auth.logout');
    });
});

Route::middleware('auth:sanctum')->put('/user', [UserController::class, 'update'])->name('user.update');

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->controller(UserManagementController::class)
    ->group(function () {
        Route::get('/approve', 'index')->name('admin.approve.index');
        Route::post('/approve/{userId}', 'approve')->whereNumber('userId')->name('admin.approve');
    });

Route::get('/rest-stop/services/{id}', [RestStopController::class, 'indexServices'])
    ->whereNumber('id')
    ->name('rest-stop.services.index');

Route::get('/route/{route_id}', [RouteController::class, 'show'])
    ->whereNumber('route_id')
    ->name('route.show');

Route::get('/route-stop/{routeStopId}', [RouteStopController::class, 'show'])
    ->whereNumber('routeStopId')
    ->name('route-stop.show');

Route::middleware('auth:sanctum')
    ->get('/route/route-stops', [RouteStopController::class, 'indexUnfulfilled'])
    ->name('route.route-stops.unfulfilled');

Route::get('/route/route-stops/{route_id}', [RouteStopController::class, 'index'])
    ->whereNumber('route_id')
    ->name('route.route-stops.index');

Route::prefix('driver')
    ->middleware('auth:sanctum')
    ->controller(DriverController::class)
    ->group(function () {
        Route::get('/', 'show')->name('driver.show');
        Route::post('/', 'store')->name('driver.store');
    });

Route::prefix('dispatcher')
    ->middleware('auth:sanctum')
    ->controller(DispatcherController::class)
    ->group(function () {
        Route::get('/all', 'index')->name('dispatcher.index');
        Route::get('/', 'show')->name('dispatcher.show');
        Route::post('/', 'store')->name('dispatcher.store');

        Route::prefix('route')
            ->controller(RouteController::class)
            ->group(function () {
                Route::get('/{dispatcherId}', 'index')
                    ->whereNumber('dispatcherId')
                    ->name('dispatcher.route.index');
                Route::post('/', 'store')->name('dispatcher.route.store');
                Route::post('/close/{routeId}', 'close')
                    ->whereNumber('routeId')
                    ->name('dispatcher.route.close');
                Route::post('/route-stop', [RouteStopController::class, 'store'])
                    ->name('dispatcher.route.route-stop.store');
                Route::put('/route-stop/{routeStopId}/services', [RouteStopController::class, 'syncServices'])
                    ->whereNumber('routeStopId')
                    ->name('dispatcher.route.route-stop.services.update');
                Route::get('/route-stop/{routeStopId}/bids', [BidController::class, 'indexForDispatcherRouteStop'])
                    ->whereNumber('routeStopId')
                    ->name('dispatcher.route.route-stop.bids.index');
            });
    });

Route::prefix('rest-stop')
    ->middleware('auth:sanctum')
    ->controller(RestStopController::class)
    ->group(function () {
        Route::get('/', 'show')->name('rest-stop.show');
        Route::post('/', 'store')->name('rest-stop.store');

        Route::prefix('bids')
            ->name('rest-stop.bids.')
            ->controller(BidController::class)
            ->group(function () {
                Route::post('/', 'store')->name('store');
                Route::get('/{routeStopId}', 'show')
                    ->whereNumber('routeStopId')
                    ->name('show');
                Route::delete('/{routeStopId}', 'destroy')
                    ->whereNumber('routeStopId')
                    ->name('destroy');
            });

        Route::prefix('services')
            ->name('rest-stop.services.')
            ->group(function () {
                Route::post('/add', 'storeService')->name('add');
                Route::post('/remove', 'destroyService')->name('remove');
            });
    });

Route::prefix('service')
    ->middleware('auth:sanctum')
    ->controller(ServiceController::class)
    ->group(function () {
        Route::get('/', 'index')->name('service.index');
        Route::get('/{id}', 'show')->whereNumber('id')->name('service.show');

        Route::middleware('role:admin')->group(function () {
            Route::post('/', 'store')->name('service.store');
            Route::delete('/{id}', 'destroy')->whereNumber('id')->name('service.destroy');
        });
    });
