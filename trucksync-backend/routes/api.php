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
Route::middleware('auth:sanctum')->get('/driver', [DriverController::class, 'show'])->name('driver.show');
Route::middleware('auth:sanctum')->post('/driver', [DriverController::class, 'store'])->name('driver.store');
Route::middleware('auth:sanctum')->get('/dispatchers', [DispatcherController::class, 'index'])->name('dispatchers.index');
Route::middleware('auth:sanctum')->get('/dispatcher', [DispatcherController::class, 'show'])->name('dispatcher.show');
Route::middleware('auth:sanctum')->post('/dispatcher', [DispatcherController::class, 'store'])->name('dispatcher.store');
Route::middleware('auth:sanctum')->get('/rest-stop', [RestStopController::class, 'show'])->name('rest-stop.show');
Route::middleware('auth:sanctum')->post('/rest-stop', [RestStopController::class, 'store'])->name('rest-stop.store');
Route::middleware('auth:sanctum')->get('/service', [ServiceController::class, 'index'])->name('service.index');
Route::middleware('auth:sanctum')->post('/service', [ServiceController::class, 'store'])->name('service.store');
Route::middleware('auth:sanctum')->get('/service/{id}', [ServiceController::class, 'show'])->whereNumber('id')->name('service.show');
Route::middleware('auth:sanctum')->delete('/service/{id}', [ServiceController::class, 'destroy'])->whereNumber('id')->name('service.destroy');
