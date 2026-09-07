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
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.from', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.to', 2)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('links.prev', null)
        ->assertJsonPath('links.next', null)
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
        ->assertJsonPath('data.route_stops', [])
        ->assertJsonPath('meta.total', 0)
        ->assertJsonPath('meta.from', null)
        ->assertJsonPath('meta.to', null);
});

it('paginates unfulfilled route stops ordered by stop_at descending', function () {
    $dispatcher = createDispatcherForUnfulfilledRouteStopsEndpoint('Acme Dispatch');
    $route = createRouteForUnfulfilledRouteStopsEndpoint($dispatcher);
    $oldestRouteStop = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Oldest stop',
        null,
        '2026-10-01 08:00:00',
        null
    );
    $middleRouteStop = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Middle stop',
        null,
        '2026-10-02 08:00:00',
        null
    );
    $newestRouteStop = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Newest stop',
        null,
        '2026-10-03 08:00:00',
        null
    );

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $response = $this->getJson('/api/route/route-stops?per_page=2&page=1')
        ->assertOk()
        ->assertJsonCount(2, 'data.route_stops')
        ->assertJsonPath('data.route_stops.0.id', $newestRouteStop->id)
        ->assertJsonPath('data.route_stops.1.id', $middleRouteStop->id)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.from', 1)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.to', 2)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('links.prev', null);

    expect($response->json('links.next'))->not->toBeNull();

    $this->getJson('/api/route/route-stops?per_page=2&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data.route_stops')
        ->assertJsonPath('data.route_stops.0.id', $oldestRouteStop->id)
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.from', 3)
        ->assertJsonPath('meta.to', 3)
        ->assertJsonPath('links.next', null);
});

it('searches unfulfilled route stops by location and description case insensitively', function () {
    $dispatcher = createDispatcherForUnfulfilledRouteStopsEndpoint('Acme Dispatch');
    $route = createRouteForUnfulfilledRouteStopsEndpoint($dispatcher);
    $locationMatch = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Vienna FUEL yard',
        null,
        '2026-10-04 08:00:00',
        null
    );
    $descriptionMatch = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Budapest staging point',
        'Bring extra fuel cards.',
        '2026-10-03 08:00:00',
        null
    );
    createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Munich overnight stop',
        'Parking only.',
        '2026-10-02 08:00:00',
        null
    );
    createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Fulfilled fuel stop',
        null,
        '2026-10-05 08:00:00',
        '2026-10-05 09:00:00'
    );

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->getJson('/api/route/route-stops?search=fUeL')
        ->assertOk()
        ->assertJsonCount(2, 'data.route_stops')
        ->assertJsonPath('data.route_stops.0.id', $locationMatch->id)
        ->assertJsonPath('data.route_stops.1.id', $descriptionMatch->id)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonMissing([
            'location' => 'Munich overnight stop',
        ])
        ->assertJsonMissing([
            'location' => 'Fulfilled fuel stop',
        ]);
});

it('sorts unfulfilled route stops by a requested route stop field', function () {
    $dispatcher = createDispatcherForUnfulfilledRouteStopsEndpoint('Acme Dispatch');
    $route = createRouteForUnfulfilledRouteStopsEndpoint($dispatcher);
    $zurichRouteStop = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Zurich inspection',
        null,
        '2026-10-01 08:00:00',
        null
    );
    $amsterdamRouteStop = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Amsterdam refuel',
        null,
        '2026-10-03 08:00:00',
        null
    );
    $belgradeRouteStop = createRouteStopForUnfulfilledRouteStopsEndpoint(
        $route,
        'Belgrade loading',
        null,
        '2026-10-02 08:00:00',
        null
    );

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/route/route-stops?sortBy.key=LOCATION&sortBy.order=ASC')
        ->assertOk()
        ->assertJsonPath('data.route_stops.0.id', $amsterdamRouteStop->id)
        ->assertJsonPath('data.route_stops.1.id', $belgradeRouteStop->id)
        ->assertJsonPath('data.route_stops.2.id', $zurichRouteStop->id);
});

it('validates pagination and search query parameters', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/route/route-stops?per_page=101&page=0')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['per_page', 'page']);
});

it('validates sort query parameters', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/route/route-stops?sortBy.key=dispatcher_company_name&sortBy.order=sideways')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sortBy.key', 'sortBy.order']);
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
