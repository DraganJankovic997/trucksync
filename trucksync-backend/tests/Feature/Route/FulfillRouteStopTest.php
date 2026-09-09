<?php

use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('fulfills a route stop owned by the authenticated dispatcher', function () {
    $fulfilledAt = Carbon::parse('2026-10-06 12:34:56');
    Carbon::setTestNow($fulfilledAt);

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForFulfillRouteStopEndpointUser($user);
    $route = createRouteForFulfillRouteStopEndpointDispatcher($dispatcher);
    $routeStop = createRouteStopForFulfillRouteStopEndpointRoute($route);
    $restStop = createRestStopForFulfillRouteStopEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $routeStop->services()->attach($fuel->id, ['quantity' => 200]);
    createRouteStopBidForFulfillRouteStopEndpoint($routeStop, $restStop);

    Sanctum::actingAs($user);

    $this->postJson("/api/dispatcher/route/route-stop/{$routeStop->id}/fulfill", [
        'rest_stop_id' => $restStop->id,
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Route stop fulfilled successfully.')
        ->assertJsonPath('data.route_stop.id', $routeStop->id)
        ->assertJsonPath('data.route_stop.route_id', $route->id)
        ->assertJsonPath('data.route_stop.fulfiled_at', $fulfilledAt->toJSON())
        ->assertJsonPath('data.route_stop.fulfiled_by', $restStop->id)
        ->assertJsonPath('data.route_stop.bids_count', 1)
        ->assertJsonPath('data.route_stop.services.0.id', $fuel->id)
        ->assertJsonPath('data.route_stop.services.0.quantity', 200);

    $this->assertDatabaseHas('route_stops', [
        'id' => $routeStop->id,
        'fulfiled_at' => '2026-10-06 12:34:56',
        'fulfiled_by' => $restStop->id,
    ]);
    $this->assertDatabaseHas('routes', [
        'id' => $route->id,
        'closed_at' => '2026-10-06 12:34:56',
    ]);
});

it('keeps the route open when it has unfulfilled stops and the start date has not passed', function () {
    $fulfilledAt = Carbon::parse('2026-10-06 12:34:56');
    Carbon::setTestNow($fulfilledAt);

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForFulfillRouteStopEndpointUser($user);
    $route = createRouteForFulfillRouteStopEndpointDispatcher($dispatcher);
    $routeStop = createRouteStopForFulfillRouteStopEndpointRoute($route);
    createRouteStopForFulfillRouteStopEndpointRoute($route);
    $restStop = createRestStopForFulfillRouteStopEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    createRouteStopBidForFulfillRouteStopEndpoint($routeStop, $restStop);

    Sanctum::actingAs($user);

    $this->postJson("/api/dispatcher/route/route-stop/{$routeStop->id}/fulfill", [
        'rest_stop_id' => $restStop->id,
    ])
        ->assertOk()
        ->assertJsonPath('data.route_stop.fulfiled_at', $fulfilledAt->toJSON())
        ->assertJsonPath('data.route_stop.fulfiled_by', $restStop->id);

    expect($route->refresh()->closed_at)->toBeNull();
});

it('closes the route when its start date has passed after fulfilling a stop', function () {
    $fulfilledAt = Carbon::parse('2026-10-06 12:34:56');
    Carbon::setTestNow($fulfilledAt);

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForFulfillRouteStopEndpointUser($user);
    $route = createRouteForFulfillRouteStopEndpointDispatcher($dispatcher, [
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-09',
    ]);
    $routeStop = createRouteStopForFulfillRouteStopEndpointRoute($route);
    createRouteStopForFulfillRouteStopEndpointRoute($route);
    $restStop = createRestStopForFulfillRouteStopEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    createRouteStopBidForFulfillRouteStopEndpoint($routeStop, $restStop);

    Sanctum::actingAs($user);

    $this->postJson("/api/dispatcher/route/route-stop/{$routeStop->id}/fulfill", [
        'rest_stop_id' => $restStop->id,
    ])
        ->assertOk();

    $this->assertDatabaseHas('routes', [
        'id' => $route->id,
        'closed_at' => '2026-10-06 12:34:56',
    ]);
});

it('does not fulfill a route stop when the selected rest stop has not bid on it', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-06 12:34:56'));

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForFulfillRouteStopEndpointUser($user);
    $route = createRouteForFulfillRouteStopEndpointDispatcher($dispatcher);
    $routeStop = createRouteStopForFulfillRouteStopEndpointRoute($route);
    $otherRouteStop = createRouteStopForFulfillRouteStopEndpointRoute($route);
    $restStop = createRestStopForFulfillRouteStopEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    createRouteStopBidForFulfillRouteStopEndpoint($otherRouteStop, $restStop);

    Sanctum::actingAs($user);

    $this->postJson("/api/dispatcher/route/route-stop/{$routeStop->id}/fulfill", [
        'rest_stop_id' => $restStop->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rest_stop_id'])
        ->assertJsonPath(
            'errors.rest_stop_id.0',
            'The selected rest stop has not bid on this route stop.'
        );

    expect($routeStop->refresh()->fulfiled_at)->toBeNull()
        ->and($routeStop->fulfiled_by)->toBeNull()
        ->and($route->refresh()->closed_at)->toBeNull();
});

it('does not fulfill a route stop owned by another dispatcher', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-06 12:34:56'));

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForFulfillRouteStopEndpointUser($user);

    $otherDispatcher = createDispatcherForFulfillRouteStopEndpointUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $routeStop = createRouteStopForFulfillRouteStopEndpointRoute(
        createRouteForFulfillRouteStopEndpointDispatcher($otherDispatcher)
    );
    $restStop = createRestStopForFulfillRouteStopEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    Sanctum::actingAs($user);

    $this->postJson("/api/dispatcher/route/route-stop/{$routeStop->id}/fulfill", [
        'rest_stop_id' => $restStop->id,
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'You cannot fulfill a route stop for a route you did not create.');

    expect($routeStop->refresh()->fulfiled_at)->toBeNull()
        ->and($routeStop->fulfiled_by)->toBeNull();
});

it('requires authentication to fulfill a route stop', function () {
    $this->postJson('/api/dispatcher/route/route-stop/1/fulfill', [])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-dispatcher users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/dispatcher/route/route-stop/1/fulfill', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can fulfill route stops.');
});

it('returns not found when the route stop does not exist', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForFulfillRouteStopEndpointUser($user);
    $restStop = createRestStopForFulfillRouteStopEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    Sanctum::actingAs($user);

    $this->postJson('/api/dispatcher/route/route-stop/999/fulfill', [
        'rest_stop_id' => $restStop->id,
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Route stop not found.');
});

it('validates route stop fulfillment payloads', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->postJson('/api/dispatcher/route/route-stop/1/fulfill', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rest_stop_id']);

    $this->postJson('/api/dispatcher/route/route-stop/1/fulfill', [
        'rest_stop_id' => 0,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rest_stop_id']);

    $this->postJson('/api/dispatcher/route/route-stop/1/fulfill', [
        'rest_stop_id' => 999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rest_stop_id']);
});

function createDispatcherForFulfillRouteStopEndpointUser(User $user): Dispatcher
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

function createRouteForFulfillRouteStopEndpointDispatcher(Dispatcher $dispatcher, array $attributes = []): DispatcherRoute
{
    return DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => '2026-10-10',
        'end_date' => '2026-10-15',
        ...$attributes,
    ]);
}

function createRouteStopForFulfillRouteStopEndpointRoute(DispatcherRoute $route): RouteStop
{
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'stop_at' => '2026-10-11 10:30:00',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
}

function createRestStopForFulfillRouteStopEndpointUser(User $user): RestStop
{
    return RestStop::query()->create([
        'user_id' => $user->id,
        'city' => 'Nis',
        'address' => 'Highway 1',
        'post_code' => '18000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}

function createRouteStopBidForFulfillRouteStopEndpoint(RouteStop $routeStop, RestStop $restStop): RouteStopBid
{
    return RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.00',
    ]);
}
