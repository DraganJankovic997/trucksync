<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a route with route stops and needed services without authentication', function () {
    $route = createRouteForShowRouteEndpoint();
    $otherRoute = createRouteForShowRouteEndpoint();
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $tireReplacement = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'piece',
    ]);
    $routeStop = createRouteStopForShowRouteEndpoint(
        $route,
        'Vienna fuel stop',
        'Refuel and inspect tires before crossing into Germany.',
        3,
        4
    );
    $secondRouteStop = createRouteStopForShowRouteEndpoint(
        $route,
        'Munich overnight stop',
        null,
        1,
        2
    );

    $routeStop->services()->attach([
        $fuel->id => ['quantity' => 200],
        $tireReplacement->id => ['quantity' => 2],
    ]);
    $secondRouteStop->services()->attach([
        $fuel->id => ['quantity' => 100],
    ]);
    createRouteStopForShowRouteEndpoint(
        $otherRoute,
        'Other dispatcher stop',
        null,
        9,
        10
    );

    $this->getJson("/api/route/{$route->id}")
        ->assertOk()
        ->assertJsonPath('data.route.id', $route->id)
        ->assertJsonPath('data.route.dispatcher_id', $route->dispatcher_id)
        ->assertJsonPath('data.route.origin', 'Belgrade warehouse')
        ->assertJsonPath('data.route.destination', 'Berlin logistics hub')
        ->assertJsonPath('data.route.planned_travel_details', 'Take the A3 corridor and stop near Vienna.')
        ->assertJsonPath('data.route.convoy_size', 3)
        ->assertJsonPath('data.route.start_date', '2026-10-01')
        ->assertJsonPath('data.route.end_date', '2026-10-05')
        ->assertJsonPath('data.route.closed_at', null)
        ->assertJsonCount(2, 'data.route.route_stops')
        ->assertJsonPath('data.route.route_stops.0.id', $routeStop->id)
        ->assertJsonPath('data.route.route_stops.0.route_id', $route->id)
        ->assertJsonPath('data.route.route_stops.0.location', 'Vienna fuel stop')
        ->assertJsonPath('data.route.route_stops.0.description', 'Refuel and inspect tires before crossing into Germany.')
        ->assertJsonPath('data.route.route_stops.0.number_of_trucks', 3)
        ->assertJsonPath('data.route.route_stops.0.number_of_drivers', 4)
        ->assertJsonCount(2, 'data.route.route_stops.0.services')
        ->assertJsonPath('data.route.route_stops.0.services.0.id', $fuel->id)
        ->assertJsonPath('data.route.route_stops.0.services.0.name', 'Fuel')
        ->assertJsonPath('data.route.route_stops.0.services.0.measurement_unit', 'liter')
        ->assertJsonPath('data.route.route_stops.0.services.0.quantity', 200)
        ->assertJsonPath('data.route.route_stops.0.services.1.id', $tireReplacement->id)
        ->assertJsonPath('data.route.route_stops.0.services.1.name', 'Tire replacement')
        ->assertJsonPath('data.route.route_stops.0.services.1.measurement_unit', 'piece')
        ->assertJsonPath('data.route.route_stops.0.services.1.quantity', 2)
        ->assertJsonPath('data.route.route_stops.1.id', $secondRouteStop->id)
        ->assertJsonPath('data.route.route_stops.1.route_id', $route->id)
        ->assertJsonPath('data.route.route_stops.1.location', 'Munich overnight stop')
        ->assertJsonPath('data.route.route_stops.1.description', null)
        ->assertJsonPath('data.route.route_stops.1.services.0.id', $fuel->id)
        ->assertJsonPath('data.route.route_stops.1.services.0.quantity', 100);
});

it('shows a route with an empty route stop list', function () {
    $route = createRouteForShowRouteEndpoint();

    $this->getJson("/api/route/{$route->id}")
        ->assertOk()
        ->assertJsonPath('data.route.id', $route->id)
        ->assertJsonPath('data.route.route_stops', []);
});

it('returns not found when showing a missing route', function () {
    $this->getJson('/api/route/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Route not found.');
});

function createRouteForShowRouteEndpoint(): DispatcherRoute
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
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ]);
}

function createRouteStopForShowRouteEndpoint(
    DispatcherRoute $route,
    string $location,
    ?string $description,
    int $numberOfTrucks,
    int $numberOfDrivers
): RouteStop {
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => $location,
        'description' => $description,
        'number_of_trucks' => $numberOfTrucks,
        'number_of_drivers' => $numberOfDrivers,
    ]);
}
