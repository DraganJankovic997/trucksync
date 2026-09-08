<?php

use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists all bids for a route stop owned by the authenticated dispatcher', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForRouteStopBidsEndpointUser($dispatcherUser);
    $route = createRouteForRouteStopBidsEndpointDispatcher($dispatcher);
    $routeStop = createRouteStopForRouteStopBidsEndpointRoute($route);
    $otherRouteStop = createRouteStopForRouteStopBidsEndpointRoute($route);
    $firstRestStop = createRestStopForRouteStopBidsEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $secondRestStop = createRestStopForRouteStopBidsEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $secondRestStop->id,
        'original_price' => '400.00',
        'price' => '350.00',
    ]);
    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $firstRestStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);
    RouteStopBid::query()->create([
        'route_stop_id' => $otherRouteStop->id,
        'rest_stop_id' => $firstRestStop->id,
        'original_price' => '500.00',
        'price' => '450.00',
    ]);

    Sanctum::actingAs($dispatcherUser);

    $this->getJson("/api/dispatcher/route/route-stop/{$routeStop->id}/bids")
        ->assertOk()
        ->assertJsonCount(2, 'data.bids')
        ->assertJsonPath('data.bids.0.route_stop_id', $routeStop->id)
        ->assertJsonPath('data.bids.0.rest_stop_id', $firstRestStop->id)
        ->assertJsonPath('data.bids.0.original_price', '300.00')
        ->assertJsonPath('data.bids.0.price', '250.75')
        ->assertJsonPath('data.bids.0.rest_stop.id', $firstRestStop->id)
        ->assertJsonPath('data.bids.0.rest_stop.user_id', $firstRestStop->user_id)
        ->assertJsonPath('data.bids.0.rest_stop.city', 'Nis')
        ->assertJsonPath('data.bids.0.rest_stop.address', 'Highway 1')
        ->assertJsonPath('data.bids.0.rest_stop.post_code', '18000')
        ->assertJsonPath('data.bids.0.rest_stop.works_from', '08:00')
        ->assertJsonPath('data.bids.0.rest_stop.works_to', '22:00')
        ->assertJsonPath('data.bids.1.route_stop_id', $routeStop->id)
        ->assertJsonPath('data.bids.1.rest_stop_id', $secondRestStop->id)
        ->assertJsonPath('data.bids.1.original_price', '400.00')
        ->assertJsonPath('data.bids.1.price', '350.00')
        ->assertJsonPath('data.bids.1.rest_stop.id', $secondRestStop->id)
        ->assertJsonPath('data.bids.1.rest_stop.city', 'Novi Sad')
        ->assertJsonMissing([
            'price' => '450.00',
        ]);
});

it('returns an empty bid list when an owned route stop has no bids', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForRouteStopBidsEndpointUser($dispatcherUser);
    $routeStop = createRouteStopForRouteStopBidsEndpointRoute(
        createRouteForRouteStopBidsEndpointDispatcher($dispatcher)
    );

    Sanctum::actingAs($dispatcherUser);

    $this->getJson("/api/dispatcher/route/route-stop/{$routeStop->id}/bids")
        ->assertOk()
        ->assertJsonPath('data.bids', []);
});

it('forbids dispatchers from listing bids for a route stop they do not own', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForRouteStopBidsEndpointUser($dispatcherUser);
    $otherDispatcher = createDispatcherForRouteStopBidsEndpointUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $routeStop = createRouteStopForRouteStopBidsEndpointRoute(
        createRouteForRouteStopBidsEndpointDispatcher($otherDispatcher)
    );

    Sanctum::actingAs($dispatcherUser);

    $this->getJson("/api/dispatcher/route/route-stop/{$routeStop->id}/bids")
        ->assertForbidden()
        ->assertJsonPath('message', 'You cannot view bids for a route stop on a route you did not create.');
});

it('forbids non-dispatcher users from listing route stop bids', function () {
    $routeStop = createRouteStopForRouteStopBidsEndpointRoute(
        createRouteForRouteStopBidsEndpointDispatcher(
            createDispatcherForRouteStopBidsEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->getJson("/api/dispatcher/route/route-stop/{$routeStop->id}/bids")
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can view route stop bids.');
});

it('returns not found when listing bids for a missing route stop', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->getJson('/api/dispatcher/route/route-stop/999/bids')
        ->assertNotFound()
        ->assertJsonPath('message', 'Route stop not found.');
});

it('requires authentication to list route stop bids', function () {
    $this->getJson('/api/dispatcher/route/route-stop/1/bids')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

function createDispatcherForRouteStopBidsEndpointUser(User $user): Dispatcher
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

function createRouteForRouteStopBidsEndpointDispatcher(Dispatcher $dispatcher): DispatcherRoute
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

function createRouteStopForRouteStopBidsEndpointRoute(DispatcherRoute $route): RouteStop
{
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'stop_at' => '2026-10-02 10:30:00',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
}

function createRestStopForRouteStopBidsEndpointUser(User $user): RestStop
{
    $city = RestStop::query()->exists() ? 'Novi Sad' : 'Nis';
    $postCode = RestStop::query()->exists() ? '21000' : '18000';

    return RestStop::query()->create([
        'user_id' => $user->id,
        'city' => $city,
        'address' => 'Highway 1',
        'post_code' => $postCode,
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}
