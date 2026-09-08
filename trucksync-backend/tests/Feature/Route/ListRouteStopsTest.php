<?php

use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
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
    $routeStop = createRouteStopForRouteStopListEndpoint(
        $route,
        'Vienna fuel stop',
        'Refuel and inspect tires before crossing into Germany.',
        '2026-10-02 10:30:00',
        3,
        4
    );
    $secondRouteStop = createRouteStopForRouteStopListEndpoint(
        $route,
        'Munich overnight stop',
        null,
        '2026-10-03 21:00:00',
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
    createRouteStopForRouteStopListEndpoint(
        $otherRoute,
        'Other dispatcher stop',
        null,
        '2026-10-04 08:00:00',
        9,
        10
    );
    $restStop = createRestStopForRouteStopListEndpointBid(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.00',
    ]);

    $this->getJson("/api/route/route-stops/{$route->id}")
        ->assertOk()
        ->assertJsonCount(2, 'data.route_stops')
        ->assertJsonPath('data.route_stops.0.id', $routeStop->id)
        ->assertJsonPath('data.route_stops.0.route_id', $route->id)
        ->assertJsonPath('data.route_stops.0.location', 'Vienna fuel stop')
        ->assertJsonPath('data.route_stops.0.description', 'Refuel and inspect tires before crossing into Germany.')
        ->assertJsonPath('data.route_stops.0.stop_at', $routeStop->stop_at->toJSON())
        ->assertJsonPath('data.route_stops.0.fulfiled_at', null)
        ->assertJsonPath('data.route_stops.0.fulfiled_by', null)
        ->assertJsonPath('data.route_stops.0.number_of_trucks', 3)
        ->assertJsonPath('data.route_stops.0.number_of_drivers', 4)
        ->assertJsonPath('data.route_stops.0.bids_count', 1)
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
        ->assertJsonPath('data.route_stops.1.location', 'Munich overnight stop')
        ->assertJsonPath('data.route_stops.1.description', null)
        ->assertJsonPath('data.route_stops.1.stop_at', $secondRouteStop->stop_at->toJSON())
        ->assertJsonPath('data.route_stops.1.bids_count', 0)
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
    string $location,
    ?string $description,
    string $stopAt,
    int $numberOfTrucks,
    int $numberOfDrivers
): RouteStop {
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => $location,
        'description' => $description,
        'stop_at' => $stopAt,
        'number_of_trucks' => $numberOfTrucks,
        'number_of_drivers' => $numberOfDrivers,
    ]);
}

function createRestStopForRouteStopListEndpointBid(User $user): RestStop
{
    return RestStop::query()->create([
        'user_id' => $user->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}
