<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists unfulfilled route stops with services and dispatcher company names for an authenticated user', function () {
    $dispatcher = createDispatcherForUnfulfilledRouteStopsEndpoint('Acme Dispatch');
    $secondDispatcher = createDispatcherForUnfulfilledRouteStopsEndpoint('Beta Freight');
    $route = createRouteForUnfulfilledRouteStopsEndpoint($dispatcher);
    $secondRoute = createRouteForUnfulfilledRouteStopsEndpoint($secondDispatcher);
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $tireReplacement = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'piece',
    ]);
    $olderRouteStop = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $secondRoute,
        'Munich overnight stop',
        null,
        '2026-10-02 21:00:00',
        null
    );
    $newerRouteStop = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Vienna fuel stop',
        'Refuel and inspect tires before crossing into Germany.',
        '2026-10-03 10:30:00',
        null
    );
    createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Fulfilled Prague stop',
        null,
        '2026-10-04 08:00:00',
        '2026-10-04 08:30:00'
    );

    $newerRouteStop->services()->attach([
        $fuel->id => ['quantity' => 200],
        $tireReplacement->id => ['quantity' => 2],
    ]);
    $olderRouteStop->services()->attach([
        $fuel->id => ['quantity' => 100],
    ]);

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/route/route-stops')
        ->assertOk()
        ->assertJsonCount(2, 'data.route_stops')
        ->assertJsonPath('data.route_stops.0.id', $newerRouteStop->id)
        ->assertJsonPath('data.route_stops.0.route_id', $route->id)
        ->assertJsonPath('data.route_stops.0.dispatcher_company_name', 'Acme Dispatch')
        ->assertJsonPath('data.route_stops.0.location', 'Vienna fuel stop')
        ->assertJsonPath('data.route_stops.0.description', 'Refuel and inspect tires before crossing into Germany.')
        ->assertJsonPath('data.route_stops.0.stop_at', $newerRouteStop->stop_at->toJSON())
        ->assertJsonPath('data.route_stops.0.fulfiled_at', null)
        ->assertJsonPath('data.route_stops.0.fulfiled_by', null)
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
        ->assertJsonPath('data.route_stops.1.id', $olderRouteStop->id)
        ->assertJsonPath('data.route_stops.1.route_id', $secondRoute->id)
        ->assertJsonPath('data.route_stops.1.dispatcher_company_name', 'Beta Freight')
        ->assertJsonPath('data.route_stops.1.location', 'Munich overnight stop')
        ->assertJsonPath('data.route_stops.1.description', null)
        ->assertJsonPath('data.route_stops.1.stop_at', $olderRouteStop->stop_at->toJSON())
        ->assertJsonPath('data.route_stops.1.services.0.id', $fuel->id)
        ->assertJsonPath('data.route_stops.1.services.0.quantity', 100)
        ->assertJsonMissing([
            'location' => 'Fulfilled Prague stop',
        ]);
});

it('returns an empty list when there are no unfulfilled route stops', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->getJson('/api/route/route-stops')
        ->assertOk()
        ->assertJsonPath('data.route_stops', []);
});

it('requires authentication to list unfulfilled route stops', function () {
    $this->getJson('/api/route/route-stops')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

function createDispatcherForUnfulfilledRouteStopsEndpoint(string $companyName): Dispatcher
{
    return Dispatcher::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'dispatcher',
        ])->id,
        'company_name' => $companyName,
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => fake()->unique()->bothify('REG-####'),
    ]);
}

function createRouteForUnfulfilledRouteStopsEndpoint(Dispatcher $dispatcher): DispatcherRoute
{
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

function createRouteStopForUnfulfilledRouteStopsEndpoint(
    DispatcherRoute $route,
    string $location,
    ?string $description,
    string $stopAt,
    ?string $fulfiledAt
): RouteStop {
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => $location,
        'description' => $description,
        'stop_at' => $stopAt,
        'fulfiled_at' => $fulfiledAt,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
}
