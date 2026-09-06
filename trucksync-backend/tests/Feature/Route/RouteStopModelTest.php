<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores route stops for a route', function () {
    $route = createRouteForRouteStopTest();

    $routeStop = RouteStop::query()->create([
        'route_id' => $route->id,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);

    expect(Schema::getColumnListing('route_stops'))->toBe([
        'id',
        'route_id',
        'number_of_trucks',
        'number_of_drivers',
        'created_at',
        'updated_at',
    ])
        ->and($route->routeStops()->first()->is($routeStop))->toBeTrue()
        ->and($routeStop->route->is($route))->toBeTrue()
        ->and($routeStop->number_of_trucks)->toBe(3)
        ->and($routeStop->number_of_drivers)->toBe(4);

    $this->assertDatabaseHas('route_stops', [
        'id' => $routeStop->id,
        'route_id' => $route->id,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
});

it('stores needed services with quantity for a route stop', function () {
    $route = createRouteForRouteStopTest();
    $routeStop = RouteStop::query()->create([
        'route_id' => $route->id,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    $routeStopService = RouteStopService::query()->create([
        'route_stop_id' => $routeStop->id,
        'service_id' => $service->id,
        'quantity' => 2,
    ]);

    expect(Schema::getColumnListing('route_stop_services'))->toBe([
        'route_stop_id',
        'service_id',
        'quantity',
    ])
        ->and($routeStopService->routeStop->is($routeStop))->toBeTrue()
        ->and($routeStopService->service->is($service))->toBeTrue()
        ->and($routeStop->routeStopServices()->first()->is($routeStopService))->toBeTrue()
        ->and($service->routeStopServices()->first()->is($routeStopService))->toBeTrue()
        ->and($routeStop->services()->first()->is($service))->toBeTrue()
        ->and($service->routeStops()->first()->is($routeStop))->toBeTrue()
        ->and($routeStopService->quantity)->toBe(2);

    $this->assertDatabaseHas('route_stop_services', [
        'route_stop_id' => $routeStop->id,
        'service_id' => $service->id,
        'quantity' => 2,
    ]);
});

function createRouteForRouteStopTest(): DispatcherRoute
{
    $dispatcher = Dispatcher::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'dispatcher',
        ])->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => fake()->unique()->bothify('REG-####'),
    ]);

    return DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => null,
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ]);
}
