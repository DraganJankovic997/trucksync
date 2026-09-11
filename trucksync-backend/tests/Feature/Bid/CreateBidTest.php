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

it('creates a bid for the authenticated rest stop', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForBidEndpointUser($user);
    $otherRestStop = createRestStopForBidEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $otherRestStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Bid created successfully.')
        ->assertJsonPath('data.bid.route_stop_id', $routeStop->id)
        ->assertJsonPath('data.bid.rest_stop_id', $restStop->id)
        ->assertJsonPath('data.bid.original_price', '300.00')
        ->assertJsonPath('data.bid.price', '250.75')
        ->assertJsonMissingPath('data.bid.id');

    $this->assertDatabaseHas('route_stop_bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);
    $this->assertDatabaseMissing('route_stop_bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $otherRestStop->id,
    ]);
});

it('updates an existing bid for the same route stop and rest stop', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForBidEndpointUser($user);
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => $routeStop->id,
        'original_price' => '320.00',
        'price' => '260.00',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Bid updated successfully.')
        ->assertJsonPath('data.bid.route_stop_id', $routeStop->id)
        ->assertJsonPath('data.bid.rest_stop_id', $restStop->id)
        ->assertJsonPath('data.bid.original_price', '320.00')
        ->assertJsonPath('data.bid.price', '260.00');

    expect(RouteStopBid::query()->count())->toBe(1);
    $this->assertDatabaseHas('route_stop_bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '320.00',
        'price' => '260.00',
    ]);
});

it('does not create a bid for a closed route', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    createRestStopForBidEndpointUser($user);
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ])),
            [
                'closed_at' => '2026-10-04 12:00:00',
            ]
        )
    );

    Sanctum::actingAs($user);

    $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => $routeStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_stop_id'])
        ->assertJsonPath(
            'errors.route_stop_id.0',
            'You cannot bid on a closed route.'
        );

    expect(RouteStopBid::query()->count())->toBe(0);
});

it('does not update a bid for a closed route', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForBidEndpointUser($user);
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ])),
            [
                'closed_at' => '2026-10-04 12:00:00',
            ]
        )
    );

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => $routeStop->id,
        'original_price' => '320.00',
        'price' => '260.00',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_stop_id'])
        ->assertJsonPath(
            'errors.route_stop_id.0',
            'You cannot bid on a closed route.'
        );

    $this->assertDatabaseHas('route_stop_bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);
});

it('requires authentication to create a bid', function () {
    $this->postJson('/api/rest-stop/bids', [])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-rest-stop users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/rest-stop/bids', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only rest stop users can create bids.');

    expect(RouteStopBid::query()->count())->toBe(0);
});

it('returns not found when the authenticated rest stop user has no rest stop profile', function () {
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => $routeStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Rest stop profile not found.');

    expect(RouteStopBid::query()->count())->toBe(0);
});

it('returns not found for a rest stop user without a rest stop profile before validating bid creation payloads', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->postJson('/api/rest-stop/bids', [])
        ->assertNotFound()
        ->assertJsonPath('message', 'Rest stop profile not found.');

    expect(RouteStopBid::query()->count())->toBe(0);
});

it('returns not found when the route stop does not exist', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    createRestStopForBidEndpointUser($user);

    Sanctum::actingAs($user);

    $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => 999,
        'original_price' => '300.00',
        'price' => '250.75',
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Route stop not found.');

    expect(RouteStopBid::query()->count())->toBe(0);
});

it('validates bid payloads', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    createRestStopForBidEndpointUser($user);

    Sanctum::actingAs($user);

    $this->postJson('/api/rest-stop/bids', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_stop_id', 'original_price', 'price']);

    $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => 0,
        'original_price' => '-1',
        'price' => '10.999',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_stop_id', 'original_price', 'price']);

    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => $routeStop->id,
        'original_price' => '300',
        'price' => '250.7',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['original_price', 'price']);

    $this->postJson('/api/rest-stop/bids', [
        'route_stop_id' => 'not-an-id',
        'original_price' => '100000000',
        'price' => 'not-a-price',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_stop_id', 'original_price', 'price']);
});

it('shows the authenticated rest stops bid by route stop id', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForBidEndpointUser($user);
    $otherRestStop = createRestStopForBidEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $otherRestStop->id,
        'original_price' => '400.00',
        'price' => '350.00',
    ]);
    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/rest-stop/bids/{$routeStop->id}")
        ->assertOk()
        ->assertJsonPath('data.bid.route_stop_id', $routeStop->id)
        ->assertJsonPath('data.bid.rest_stop_id', $restStop->id)
        ->assertJsonPath('data.bid.original_price', '300.00')
        ->assertJsonPath('data.bid.price', '250.75');
});

it('returns not found when showing a bid the authenticated rest stop did not create', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    createRestStopForBidEndpointUser($user);
    $otherRestStop = createRestStopForBidEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $otherRestStop->id,
        'original_price' => '400.00',
        'price' => '350.00',
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/rest-stop/bids/{$routeStop->id}")
        ->assertNotFound()
        ->assertJsonPath('message', 'Bid not found.');
});

it('requires authentication to show a bid', function () {
    $this->getJson('/api/rest-stop/bids/1')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-rest-stop users from showing bids', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/rest-stop/bids/1')
        ->assertForbidden()
        ->assertJsonPath('message', 'Only rest stop users can view bids.');
});

it('returns not found when showing a bid for a rest stop user without a rest stop profile', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->getJson('/api/rest-stop/bids/1')
        ->assertNotFound()
        ->assertJsonPath('message', 'Rest stop profile not found.');
});

it('deletes the authenticated rest stops bid by route stop id', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForBidEndpointUser($user);
    $otherRestStop = createRestStopForBidEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $otherRestStop->id,
        'original_price' => '400.00',
        'price' => '350.00',
    ]);
    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/rest-stop/bids/{$routeStop->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Bid deleted successfully.')
        ->assertJsonPath('data.bid.route_stop_id', $routeStop->id)
        ->assertJsonPath('data.bid.rest_stop_id', $restStop->id)
        ->assertJsonPath('data.bid.original_price', '300.00')
        ->assertJsonPath('data.bid.price', '250.75');

    $this->assertDatabaseMissing('route_stop_bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
    ]);
    $this->assertDatabaseHas('route_stop_bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $otherRestStop->id,
        'original_price' => '400.00',
        'price' => '350.00',
    ]);
});

it('does not delete a bid for a closed route', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForBidEndpointUser($user);
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ])),
            [
                'closed_at' => '2026-10-04 12:00:00',
            ]
        )
    );

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/rest-stop/bids/{$routeStop->id}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_stop_id'])
        ->assertJsonPath(
            'errors.route_stop_id.0',
            'You cannot delete a bid on a closed route.'
        );

    $this->assertDatabaseHas('route_stop_bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'original_price' => '300.00',
        'price' => '250.75',
    ]);
});

it('returns not found when deleting a bid the authenticated rest stop did not create', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    createRestStopForBidEndpointUser($user);
    $otherRestStop = createRestStopForBidEndpointUser(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $routeStop = createRouteStopForBidEndpointRoute(
        createRouteForBidEndpointDispatcher(
            createDispatcherForBidEndpointUser(User::factory()->create([
                'profile_type' => 'dispatcher',
            ]))
        )
    );

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $otherRestStop->id,
        'original_price' => '400.00',
        'price' => '350.00',
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/rest-stop/bids/{$routeStop->id}")
        ->assertNotFound()
        ->assertJsonPath('message', 'Bid not found.');

    expect(RouteStopBid::query()->count())->toBe(1);
});

it('requires authentication to delete a bid', function () {
    $this->deleteJson('/api/rest-stop/bids/1')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-rest-stop users from deleting bids', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->deleteJson('/api/rest-stop/bids/1')
        ->assertForbidden()
        ->assertJsonPath('message', 'Only rest stop users can delete bids.');
});

it('returns not found when deleting a bid for a rest stop user without a rest stop profile', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->deleteJson('/api/rest-stop/bids/1')
        ->assertNotFound()
        ->assertJsonPath('message', 'Rest stop profile not found.');
});

function createDispatcherForBidEndpointUser(User $user): Dispatcher
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

function createRouteForBidEndpointDispatcher(Dispatcher $dispatcher, array $attributes = []): DispatcherRoute
{
    return DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
        ...$attributes,
    ]);
}

function createRouteStopForBidEndpointRoute(DispatcherRoute $route): RouteStop
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

function createRestStopForBidEndpointUser(User $user): RestStop
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
