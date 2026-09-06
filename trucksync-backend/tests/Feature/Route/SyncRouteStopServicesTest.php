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

it('syncs services and quantities for a route stop owned by the authenticated dispatcher', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForRouteStopServicesEndpointUser($user);
    $route = createRouteForRouteStopServicesEndpointDispatcher($dispatcher);
    $routeStop = createRouteStopForRouteStopServicesEndpoint($route);
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $tireReplacement = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'piece',
    ]);
    $wash = Service::query()->create([
        'name' => 'Wash',
    ]);

    $routeStop->services()->attach([
        $fuel->id => ['quantity' => 100],
        $tireReplacement->id => ['quantity' => 2],
    ]);

    Sanctum::actingAs($user);

    $this->putJson("/api/dispatcher/route/route-stop/{$routeStop->id}/services", [
        'services' => [
            [
                'service_id' => $tireReplacement->id,
                'quantity' => 4,
            ],
            [
                'service_id' => $wash->id,
                'quantity' => 1,
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Route stop services updated successfully.')
        ->assertJsonPath('data.route_stop.id', $routeStop->id)
        ->assertJsonPath('data.route_stop.services.0.id', $tireReplacement->id)
        ->assertJsonPath('data.route_stop.services.0.quantity', 4)
        ->assertJsonPath('data.route_stop.services.1.id', $wash->id)
        ->assertJsonPath('data.route_stop.services.1.quantity', 1);

    $this->assertDatabaseMissing('route_stop_services', [
        'route_stop_id' => $routeStop->id,
        'service_id' => $fuel->id,
    ]);
    $this->assertDatabaseHas('route_stop_services', [
        'route_stop_id' => $routeStop->id,
        'service_id' => $tireReplacement->id,
        'quantity' => 4,
    ]);
    $this->assertDatabaseHas('route_stop_services', [
        'route_stop_id' => $routeStop->id,
        'service_id' => $wash->id,
        'quantity' => 1,
    ]);
    expect(RouteStopService::query()->where('route_stop_id', $routeStop->id)->count())->toBe(2);
});

it('does not sync route stop services for a route owned by another dispatcher', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForRouteStopServicesEndpointUser($user);

    $otherDispatcher = createDispatcherForRouteStopServicesEndpointUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $route = createRouteForRouteStopServicesEndpointDispatcher($otherDispatcher);
    $routeStop = createRouteStopForRouteStopServicesEndpoint($route);
    $fuel = Service::query()->create([
        'name' => 'Fuel',
    ]);
    $wash = Service::query()->create([
        'name' => 'Wash',
    ]);
    $routeStop->services()->attach($fuel->id, ['quantity' => 100]);

    Sanctum::actingAs($user);

    $this->putJson("/api/dispatcher/route/route-stop/{$routeStop->id}/services", [
        'services' => [
            [
                'service_id' => $wash->id,
                'quantity' => 1,
            ],
        ],
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'You cannot update route stop services for a route you did not create.');

    $this->assertDatabaseHas('route_stop_services', [
        'route_stop_id' => $routeStop->id,
        'service_id' => $fuel->id,
        'quantity' => 100,
    ]);
    $this->assertDatabaseMissing('route_stop_services', [
        'route_stop_id' => $routeStop->id,
        'service_id' => $wash->id,
    ]);
});

it('requires authentication to sync route stop services', function () {
    $this->putJson('/api/dispatcher/route/route-stop/1/services', [])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-dispatcher users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->putJson('/api/dispatcher/route/route-stop/1/services', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can update route stop services.');
});

it('returns not found when the route stop does not exist', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForRouteStopServicesEndpointUser($user);
    $fuel = Service::query()->create([
        'name' => 'Fuel',
    ]);

    Sanctum::actingAs($user);

    $this->putJson('/api/dispatcher/route/route-stop/999/services', [
        'services' => [
            [
                'service_id' => $fuel->id,
                'quantity' => 100,
            ],
        ],
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Route stop not found.');
});

it('validates route stop services payloads', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForRouteStopServicesEndpointUser($user);
    $route = createRouteForRouteStopServicesEndpointDispatcher($dispatcher);
    $routeStop = createRouteStopForRouteStopServicesEndpoint($route);
    $fuel = Service::query()->create([
        'name' => 'Fuel',
    ]);

    Sanctum::actingAs($user);

    $this->putJson("/api/dispatcher/route/route-stop/{$routeStop->id}/services", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['services']);

    $this->putJson("/api/dispatcher/route/route-stop/{$routeStop->id}/services", [
        'services' => [],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['services']);

    $this->putJson("/api/dispatcher/route/route-stop/{$routeStop->id}/services", [
        'services' => [
            [
                'service_id' => 999,
                'quantity' => 0,
            ],
            [
                'service_id' => $fuel->id,
                'quantity' => 1,
            ],
            [
                'service_id' => $fuel->id,
                'quantity' => 2,
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
            'services.0.service_id',
            'services.0.quantity',
            'services.2.service_id',
            'services.3.service_id',
            'services.4.service_id',
            'services.4.quantity',
        ]);
});

function createDispatcherForRouteStopServicesEndpointUser(User $user): Dispatcher
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

function createRouteForRouteStopServicesEndpointDispatcher(Dispatcher $dispatcher): DispatcherRoute
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

function createRouteStopForRouteStopServicesEndpoint(DispatcherRoute $route): RouteStop
{
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
}
