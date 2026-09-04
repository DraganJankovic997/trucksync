<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DispatcherController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RestStopController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
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
    });

Route::prefix('rest-stop')
    ->middleware('auth:sanctum')
    ->controller(RestStopController::class)
    ->group(function () {
        Route::get('/', 'show')->name('rest-stop.show');
        Route::post('/', 'store')->name('rest-stop.store');
        Route::post('/services', 'storeService')->name('rest-stop.services.store');
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
