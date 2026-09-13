<?php

use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists authenticated rest stop bids sorted by created at descending', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForRestStopBidIndexUser($user);
    $otherRestStop = createRestStopForRestStopBidIndexUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $route = createRouteForRestStopBidIndexDispatcher(
        createDispatcherForRestStopBidIndexUser(User::factory()->create([
            'profile_type' => 'dispatcher',
        ]))
    );
    $oldRouteStop = createRouteStopForRestStopBidIndexRoute($route);
    $middleRouteStop = createRouteStopForRestStopBidIndexRoute($route);
    $newRouteStop = createRouteStopForRestStopBidIndexRoute($route);
    $otherRouteStop = createRouteStopForRestStopBidIndexRoute($route);

    createRouteStopBidForRestStopBidIndex(
        $oldRouteStop,
        $restStop,
        RouteStopBid::STATUS_PENDING,
        '2026-10-01 08:00:00'
    );
    createRouteStopBidForRestStopBidIndex(
        $middleRouteStop,
        $restStop,
        RouteStopBid::STATUS_SELECTED,
        '2026-10-02 08:00:00'
    );
    createRouteStopBidForRestStopBidIndex(
        $newRouteStop,
        $restStop,
        RouteStopBid::STATUS_REJECTED,
        '2026-10-03 08:00:00'
    );
    createRouteStopBidForRestStopBidIndex(
        $otherRouteStop,
        $otherRestStop,
        RouteStopBid::STATUS_PENDING,
        '2026-10-04 08:00:00'
    );

    Sanctum::actingAs($user);

    $this->getJson('/api/rest-stop/bids')
        ->assertOk()
        ->assertJsonCount(3, 'data.bids')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.from', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.to', 3)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('links.prev', null)
        ->assertJsonPath('links.next', null)
        ->assertJsonPath('data.bids.0.route_stop_id', $newRouteStop->id)
        ->assertJsonPath('data.bids.0.status', RouteStopBid::STATUS_REJECTED)
        ->assertJsonPath('data.bids.1.route_stop_id', $middleRouteStop->id)
        ->assertJsonPath('data.bids.1.status', RouteStopBid::STATUS_SELECTED)
        ->assertJsonPath('data.bids.2.route_stop_id', $oldRouteStop->id)
        ->assertJsonPath('data.bids.2.status', RouteStopBid::STATUS_PENDING)
        ->assertJsonMissing([
            'route_stop_id' => $otherRouteStop->id,
        ]);
});

it('filters authenticated rest stop bids by status', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForRestStopBidIndexUser($user);
    $route = createRouteForRestStopBidIndexDispatcher(
        createDispatcherForRestStopBidIndexUser(User::factory()->create([
            'profile_type' => 'dispatcher',
        ]))
    );
    $pendingRouteStop = createRouteStopForRestStopBidIndexRoute($route);
    $selectedRouteStop = createRouteStopForRestStopBidIndexRoute($route);
    $rejectedRouteStop = createRouteStopForRestStopBidIndexRoute($route);

    createRouteStopBidForRestStopBidIndex(
        $pendingRouteStop,
        $restStop,
        RouteStopBid::STATUS_PENDING,
        '2026-10-01 08:00:00'
    );
    createRouteStopBidForRestStopBidIndex(
        $selectedRouteStop,
        $restStop,
        RouteStopBid::STATUS_SELECTED,
        '2026-10-02 08:00:00'
    );
    createRouteStopBidForRestStopBidIndex(
        $rejectedRouteStop,
        $restStop,
        RouteStopBid::STATUS_REJECTED,
        '2026-10-03 08:00:00'
    );

    Sanctum::actingAs($user);

    $this->getJson('/api/rest-stop/bids?status=selected')
        ->assertOk()
        ->assertJsonCount(1, 'data.bids')
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('data.bids.0.route_stop_id', $selectedRouteStop->id)
        ->assertJsonPath('data.bids.0.status', RouteStopBid::STATUS_SELECTED);
});

it('paginates authenticated rest stop bids while preserving status filters', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForRestStopBidIndexUser($user);
    $route = createRouteForRestStopBidIndexDispatcher(
        createDispatcherForRestStopBidIndexUser(User::factory()->create([
            'profile_type' => 'dispatcher',
        ]))
    );
    $olderPendingRouteStop = createRouteStopForRestStopBidIndexRoute($route);
    $selectedRouteStop = createRouteStopForRestStopBidIndexRoute($route);
    $newerPendingRouteStop = createRouteStopForRestStopBidIndexRoute($route);

    createRouteStopBidForRestStopBidIndex(
        $olderPendingRouteStop,
        $restStop,
        RouteStopBid::STATUS_PENDING,
        '2026-10-01 08:00:00'
    );
    createRouteStopBidForRestStopBidIndex(
        $selectedRouteStop,
        $restStop,
        RouteStopBid::STATUS_SELECTED,
        '2026-10-02 08:00:00'
    );
    createRouteStopBidForRestStopBidIndex(
        $newerPendingRouteStop,
        $restStop,
        RouteStopBid::STATUS_PENDING,
        '2026-10-03 08:00:00'
    );

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/rest-stop/bids?status=pending&per_page=1&page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data.bids')
        ->assertJsonPath('data.bids.0.route_stop_id', $newerPendingRouteStop->id)
        ->assertJsonPath('data.bids.0.status', RouteStopBid::STATUS_PENDING)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.from', 1)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.to', 1)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('links.prev', null);

    expect($response->json('links.next'))->toContain('status=pending');

    $this->getJson('/api/rest-stop/bids?status=pending&per_page=1&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data.bids')
        ->assertJsonPath('data.bids.0.route_stop_id', $olderPendingRouteStop->id)
        ->assertJsonPath('data.bids.0.status', RouteStopBid::STATUS_PENDING)
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.from', 2)
        ->assertJsonPath('meta.to', 2)
        ->assertJsonPath('links.next', null);
});

it('validates rest stop bid status filters', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    createRestStopForRestStopBidIndexUser($user);

    Sanctum::actingAs($user);

    $this->getJson('/api/rest-stop/bids?status=accepted')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

it('validates rest stop bid pagination parameters', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    createRestStopForRestStopBidIndexUser($user);

    Sanctum::actingAs($user);

    $this->getJson('/api/rest-stop/bids?per_page=101&page=0')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['per_page', 'page']);
});

it('requires authentication to list authenticated rest stop bids', function () {
    $this->getJson('/api/rest-stop/bids')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-rest-stop users from listing authenticated rest stop bids', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/rest-stop/bids')
        ->assertForbidden()
        ->assertJsonPath('message', 'Only rest stop users can view bids.');
});

it('returns not found when listing bids for a rest stop user without a rest stop profile', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->getJson('/api/rest-stop/bids')
        ->assertNotFound()
        ->assertJsonPath('message', 'Rest stop profile not found.');
});

function createDispatcherForRestStopBidIndexUser(User $user): Dispatcher
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

function createRouteForRestStopBidIndexDispatcher(Dispatcher $dispatcher): DispatcherRoute
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

function createRouteStopForRestStopBidIndexRoute(DispatcherRoute $route): RouteStop
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

function createRestStopForRestStopBidIndexUser(User $user): RestStop
{
    return RestStop::query()->create([
        'user_id' => $user->id,
        'city' => 'Nis',
        'address' => 'Highway 1',
        'post_code' => fake()->unique()->postcode(),
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}

function createRouteStopBidForRestStopBidIndex(
    RouteStop $routeStop,
    RestStop $restStop,
    string $status,
    string $createdAt
): RouteStopBid {
    $bid = RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.00',
        'status' => $status,
    ]);

    RouteStopBid::query()
        ->where('route_stop_id', $routeStop->id)
        ->where('rest_stop_id', $restStop->id)
        ->update([
            'created_at' => Carbon::parse($createdAt),
            'updated_at' => Carbon::parse($createdAt),
        ]);

    return RouteStopBid::query()
        ->where('route_stop_id', $bid->route_stop_id)
        ->where('rest_stop_id', $bid->rest_stop_id)
        ->firstOrFail();
}
