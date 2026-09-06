<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a route stop with needed services for a route owned by the authenticated dispatcher', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForRouteStopEndpointUser($user);
    $route = createRouteForRouteStopEndpointDispatcher($dispatcher);
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $tireReplacement = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'piece',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/dispatcher/route/route-stop', [
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
        'services' => [
            [
                'service_id' => $fuel->id,
                'quantity' => 200,
            ],
            [
                'service_id' => $tireReplacement->id,
                'quantity' => 2,
            ],
        ],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Route stop created successfully.')
        ->assertJsonPath('data.route_stop.route_id', $route->id)
        ->assertJsonPath('data.route_stop.location', 'Vienna fuel stop')
        ->assertJsonPath('data.route_stop.description', 'Refuel and inspect tires before crossing into Germany.')
        ->assertJsonPath('data.route_stop.number_of_trucks', 3)
        ->assertJsonPath('data.route_stop.number_of_drivers', 4)
        ->assertJsonPath('data.route_stop.services.0.id', $fuel->id)
        ->assertJsonPath('data.route_stop.services.0.name', 'Fuel')
        ->assertJsonPath('data.route_stop.services.0.measurement_unit', 'liter')
        ->assertJsonPath('data.route_stop.services.0.quantity', 200)
        ->assertJsonPath('data.route_stop.services.1.id', $tireReplacement->id)
        ->assertJsonPath('data.route_stop.services.1.name', 'Tire replacement')
        ->assertJsonPath('data.route_stop.services.1.measurement_unit', 'piece')
        ->assertJsonPath('data.route_stop.services.1.quantity', 2);

    $routeStopId = $response->json('data.route_stop.id');

    $this->assertDatabaseHas('route_stops', [
        'id' => $routeStopId,
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
    $this->assertDatabaseHas('route_stop_services', [
        'route_stop_id' => $routeStopId,
        'service_id' => $fuel->id,
        'quantity' => 200,
    ]);
    $this->assertDatabaseHas('route_stop_services', [
        'route_stop_id' => $routeStopId,
        'service_id' => $tireReplacement->id,
        'quantity' => 2,
    ]);
});

it('forbids creating a route stop for a route owned by another dispatcher', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForRouteStopEndpointUser($user);

    $otherDispatcher = createDispatcherForRouteStopEndpointUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $otherRoute = createRouteForRouteStopEndpointDispatcher($otherDispatcher);
    $service = Service::query()->create([
        'name' => 'Fuel',
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/dispatcher/route/route-stop', [
        'route_id' => $otherRoute->id,
        'location' => 'Vienna fuel stop',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
        'services' => [
            [
                'service_id' => $service->id,
                'quantity' => 200,
            ],
        ],
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'You cannot add route stops to a route you did not create.');

    expect(RouteStop::query()->count())->toBe(0)
        ->and(RouteStopService::query()->count())->toBe(0);
});

it('requires authentication to create a route stop', function () {
    $this->postJson('/api/dispatcher/route/route-stop', [])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-dispatcher users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/dispatcher/route/route-stop', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can create route stops.');

    expect(RouteStop::query()->count())->toBe(0)
        ->and(RouteStopService::query()->count())->toBe(0);
});

it('returns not found when the route does not exist for the authenticated dispatcher', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForRouteStopEndpointUser($user);
    $service = Service::query()->create([
        'name' => 'Fuel',
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/dispatcher/route/route-stop', [
        'route_id' => 999,
        'location' => 'Vienna fuel stop',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
        'services' => [
            [
                'service_id' => $service->id,
                'quantity' => 200,
            ],
        ],
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Route not found.');

    expect(RouteStop::query()->count())->toBe(0)
        ->and(RouteStopService::query()->count())->toBe(0);
});

it('validates route stop payloads', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForRouteStopEndpointUser($user);

    Sanctum::actingAs($user);

    $this->postJson('/api/dispatcher/route/route-stop', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'route_id',
            'location',
            'number_of_trucks',
            'number_of_drivers',
            'services',
        ]);

    $this->postJson('/api/dispatcher/route/route-stop', [
        'route_id' => 0,
        'location' => '',
        'description' => [],
        'number_of_trucks' => 0,
        'number_of_drivers' => 0,
        'services' => [
            [
                'service_id' => 999,
                'quantity' => 0,
            ],
            [
                'quantity' => 1,
            ],
            [
                'service_id' => 'not-an-id',
                'quantity' => 'many',
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'route_id',
            'location',
            'description',
            'number_of_trucks',
            'number_of_drivers',
            'services.0.service_id',
            'services.0.quantity',
            'services.1.service_id',
            'services.2.service_id',
            'services.2.quantity',
        ]);
});

function createDispatcherForRouteStopEndpointUser(User $user): Dispatcher
{
    return Dispatcher::query()->create([
        'user_id' => $user->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => fake()->unique()->bothify('REG-####'),
    ]);
}

function createRouteForRouteStopEndpointDispatcher(Dispatcher $dispatcher): DispatcherRoute
{
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
