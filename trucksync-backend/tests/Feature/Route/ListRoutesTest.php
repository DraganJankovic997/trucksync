<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists routes for a dispatcher with open routes first and each group ordered by creation date', function () {
    $dispatcher = createDispatcherForListRoutesUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $otherDispatcher = createDispatcherForListRoutesUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $closedEarlier = createRouteForListRoutesDispatcher(
        $dispatcher,
        'Closed earlier',
        '2026-10-01 08:00:00',
        '2026-10-08 08:00:00',
    );
    $openLater = createRouteForListRoutesDispatcher(
        $dispatcher,
        'Open later',
        '2026-10-03 08:00:00',
    );
    $openEarlier = createRouteForListRoutesDispatcher(
        $dispatcher,
        'Open earlier',
        '2026-10-02 08:00:00',
    );
    $closedLater = createRouteForListRoutesDispatcher(
        $dispatcher,
        'Closed later',
        '2026-10-04 08:00:00',
        '2026-10-09 08:00:00',
    );
    createRouteForListRoutesDispatcher(
        $otherDispatcher,
        'Other dispatcher route',
        '2026-10-01 07:00:00',
    );

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson("/api/dispatcher/route/{$dispatcher->id}")
        ->assertOk()
        ->assertJsonCount(4, 'data.routes')
        ->assertJsonPath('data.routes.0.id', $openEarlier->id)
        ->assertJsonPath('data.routes.0.origin', 'Open earlier')
        ->assertJsonPath('data.routes.0.closed_at', null)
        ->assertJsonPath('data.routes.1.id', $openLater->id)
        ->assertJsonPath('data.routes.1.origin', 'Open later')
        ->assertJsonPath('data.routes.1.closed_at', null)
        ->assertJsonPath('data.routes.2.id', $closedEarlier->id)
        ->assertJsonPath('data.routes.2.origin', 'Closed earlier')
        ->assertJsonPath('data.routes.3.id', $closedLater->id)
        ->assertJsonPath('data.routes.3.origin', 'Closed later');
});

it('returns an empty route list when the dispatcher has no routes', function () {
    $dispatcher = createDispatcherForListRoutesUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->getJson("/api/dispatcher/route/{$dispatcher->id}")
        ->assertOk()
        ->assertJsonPath('data.routes', []);
});

it('requires authentication to list dispatcher routes', function () {
    $this->getJson('/api/dispatcher/route/1')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('returns not found when listing routes for a missing dispatcher', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/dispatcher/route/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Dispatcher not found.');
});

function createDispatcherForListRoutesUser(User $user): Dispatcher
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

function createRouteForListRoutesDispatcher(
    Dispatcher $dispatcher,
    string $origin,
    string $createdAt,
    ?string $closedAt = null
): DispatcherRoute {
    $route = DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => $origin,
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ]);

    $route->forceFill([
        'created_at' => Carbon::parse($createdAt),
        'updated_at' => Carbon::parse($createdAt),
        'closed_at' => $closedAt ? Carbon::parse($closedAt) : null,
    ])->save();

    return $route->refresh();
}
