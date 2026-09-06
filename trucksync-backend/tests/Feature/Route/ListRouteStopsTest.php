<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists route stops with needed services for a route without authentication', function () {
    $route = createRouteForRouteStopListEndpoint();
    $otherRoute = createRouteForRouteStopListEndpoint();
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $tireReplacement = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'piece',
    ]);
    $routeStop = createRouteStopForRouteStopListEndpoint($route, 3, 4);
    $secondRouteStop = createRouteStopForRouteStopListEndpoint($route, 1, 2);

    $routeStop->services()->attach([
        $fuel->id => ['quantity' => 200],
        $tireReplacement->id => ['quantity' => 2],
    ]);
    $secondRouteStop->services()->attach([
        $fuel->id => ['quantity' => 100],
    ]);
    createRouteStopForRouteStopListEndpoint($otherRoute, 9, 10);

    $this->getJson("/api/route/route-stops/{$route->id}")
        ->assertOk()
        ->assertJsonCount(2, 'data.route_stops')
        ->assertJsonPath('data.route_stops.0.id', $routeStop->id)
        ->assertJsonPath('data.route_stops.0.route_id', $route->id)
        ->assertJsonPath('data.route_stops.0.number_of_trucks', 3)
        ->assertJsonPath('data.route_stops.0.number_of_drivers', 4)
        ->assertJsonPath('data.route_stops.0.services.0.id', $fuel->id)
        ->assertJsonPath('data.route_stops.0.services.0.name', 'Fuel')
        ->assertJsonPath('data.route_stops.0.services.0.measurement_unit', 'liter')
        ->assertJsonPath('data.route_stops.0.services.0.quantity', 200)
        ->assertJsonPath('data.route_stops.0.services.1.id', $tireReplacement->id)
        ->assertJsonPath('data.route_stops.0.services.1.name', 'Tire replacement')
        ->assertJsonPath('data.route_stops.0.services.1.measurement_unit', 'piece')
        ->assertJsonPath('data.route_stops.0.services.1.quantity', 2)
        ->assertJsonPath('data.route_stops.1.id', $secondRouteStop->id)
        ->assertJsonPath('data.route_stops.1.route_id', $route->id)
        ->assertJsonPath('data.route_stops.1.services.0.id', $fuel->id)
        ->assertJsonPath('data.route_stops.1.services.0.quantity', 100);
});

it('returns an empty route stop list when the route has no stops', function () {
    $route = createRouteForRouteStopListEndpoint();

    $this->getJson("/api/route/route-stops/{$route->id}")
        ->assertOk()
        ->assertJsonPath('data.route_stops', []);
});

it('returns not found when listing route stops for a missing route', function () {
    $this->getJson('/api/route/route-stops/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Route not found.');
});

function createRouteForRouteStopListEndpoint(): DispatcherRoute
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

function createRouteStopForRouteStopListEndpoint(
    DispatcherRoute $route,
    int $numberOfTrucks,
    int $numberOfDrivers
): RouteStop {
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'number_of_trucks' => $numberOfTrucks,
        'number_of_drivers' => $numberOfDrivers,
    ]);
}
