<?php

use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('assigns drivers to a route and marks one convoy leader', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForSyncRouteDrivers($dispatcherUser);
    $route = createRouteForSyncRouteDrivers($dispatcher);
    $convoyLeader = createDriverForSyncRouteDrivers($dispatcher, 'LEADER-123');
    $regularDriver = createDriverForSyncRouteDrivers($dispatcher, 'REGULAR-456');

    Sanctum::actingAs($dispatcherUser);

    $this->putJson("/api/dispatcher/route/{$route->id}/drivers", [
        'drivers' => [
            [
                'driver_id' => $convoyLeader->id,
                'is_convoy_leader' => true,
            ],
            [
                'driver_id' => $regularDriver->id,
                'is_convoy_leader' => false,
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Route drivers updated successfully.')
        ->assertJsonCount(2, 'data.route.drivers')
        ->assertJsonPath('data.route.drivers.0.id', $convoyLeader->id)
        ->assertJsonPath('data.route.drivers.0.is_convoy_leader', true)
        ->assertJsonPath('data.route.drivers.1.id', $regularDriver->id)
        ->assertJsonPath('data.route.drivers.1.is_convoy_leader', false);

    $this->assertDatabaseHas('driver_route', [
        'route_id' => $route->id,
        'driver_id' => $convoyLeader->id,
        'is_convoy_leader' => true,
    ]);
    $this->assertDatabaseHas('driver_route', [
        'route_id' => $route->id,
        'driver_id' => $regularDriver->id,
        'is_convoy_leader' => false,
    ]);
});

it('syncs route driver assignments by removing omitted drivers and changing the convoy leader', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForSyncRouteDrivers($dispatcherUser);
    $route = createRouteForSyncRouteDrivers($dispatcher);
    $firstDriver = createDriverForSyncRouteDrivers($dispatcher, 'FIRST-123');
    $secondDriver = createDriverForSyncRouteDrivers($dispatcher, 'SECOND-456');

    $route->drivers()->attach([
        $firstDriver->id => ['is_convoy_leader' => true],
        $secondDriver->id => ['is_convoy_leader' => false],
    ]);

    Sanctum::actingAs($dispatcherUser);

    $this->putJson("/api/dispatcher/route/{$route->id}/drivers", [
        'drivers' => [
            [
                'driver_id' => $secondDriver->id,
                'is_convoy_leader' => true,
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonCount(1, 'data.route.drivers')
        ->assertJsonPath('data.route.drivers.0.id', $secondDriver->id)
        ->assertJsonPath('data.route.drivers.0.is_convoy_leader', true);

    $this->assertDatabaseMissing('driver_route', [
        'route_id' => $route->id,
        'driver_id' => $firstDriver->id,
    ]);
    $this->assertDatabaseHas('driver_route', [
        'route_id' => $route->id,
        'driver_id' => $secondDriver->id,
        'is_convoy_leader' => true,
    ]);
});

it('rejects assigning more than one convoy leader to a route', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForSyncRouteDrivers($dispatcherUser);
    $route = createRouteForSyncRouteDrivers($dispatcher);
    $firstDriver = createDriverForSyncRouteDrivers($dispatcher, 'FIRST-123');
    $secondDriver = createDriverForSyncRouteDrivers($dispatcher, 'SECOND-456');

    Sanctum::actingAs($dispatcherUser);

    $this->putJson("/api/dispatcher/route/{$route->id}/drivers", [
        'drivers' => [
            [
                'driver_id' => $firstDriver->id,
                'is_convoy_leader' => true,
            ],
            [
                'driver_id' => $secondDriver->id,
                'is_convoy_leader' => true,
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['drivers']);
});

it('rejects drivers that do not belong to the authenticated dispatcher', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForSyncRouteDrivers($dispatcherUser);
    $otherDispatcher = createDispatcherForSyncRouteDrivers(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $route = createRouteForSyncRouteDrivers($dispatcher);
    $otherDriver = createDriverForSyncRouteDrivers($otherDispatcher, 'OTHER-123');

    Sanctum::actingAs($dispatcherUser);

    $this->putJson("/api/dispatcher/route/{$route->id}/drivers", [
        'drivers' => [
            [
                'driver_id' => $otherDriver->id,
                'is_convoy_leader' => true,
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Selected drivers must belong to your dispatcher profile.')
        ->assertJsonValidationErrors(['drivers']);
});

it('rejects drivers assigned to overlapping routes', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForSyncRouteDrivers($dispatcherUser);
    $route = createRouteForSyncRouteDrivers(
        $dispatcher,
        '2026-10-01',
        '2026-10-05'
    );
    $overlappingRoute = createRouteForSyncRouteDrivers(
        $dispatcher,
        '2026-10-03',
        '2026-10-06'
    );
    $overlappingRoute->forceFill([
        'closed_at' => '2026-09-25 12:00:00',
    ])->save();
    $busyDriver = createDriverForSyncRouteDrivers($dispatcher, 'BUSY-123');
    $overlappingRoute->drivers()->attach($busyDriver->id, [
        'is_convoy_leader' => false,
    ]);

    Sanctum::actingAs($dispatcherUser);

    $this->putJson("/api/dispatcher/route/{$route->id}/drivers", [
        'drivers' => [
            [
                'driver_id' => $busyDriver->id,
                'is_convoy_leader' => true,
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Selected drivers must be available for this route schedule.')
        ->assertJsonValidationErrors(['drivers']);

    $this->assertDatabaseMissing('driver_route', [
        'route_id' => $route->id,
        'driver_id' => $busyDriver->id,
    ]);
});

it('allows drivers assigned to non-overlapping routes', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForSyncRouteDrivers($dispatcherUser);
    $route = createRouteForSyncRouteDrivers(
        $dispatcher,
        '2026-10-01',
        '2026-10-05'
    );
    $nonOverlappingRoute = createRouteForSyncRouteDrivers(
        $dispatcher,
        '2026-10-06',
        '2026-10-08'
    );
    $futureRouteDriver = createDriverForSyncRouteDrivers($dispatcher, 'FUTURE-456');

    $nonOverlappingRoute->drivers()->attach($futureRouteDriver->id, [
        'is_convoy_leader' => false,
    ]);

    Sanctum::actingAs($dispatcherUser);

    $this->putJson("/api/dispatcher/route/{$route->id}/drivers", [
        'drivers' => [
            [
                'driver_id' => $futureRouteDriver->id,
                'is_convoy_leader' => true,
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonCount(1, 'data.route.drivers');
});

it('rejects route driver assignment for a route owned by another dispatcher', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForSyncRouteDrivers($dispatcherUser);
    $otherDispatcher = createDispatcherForSyncRouteDrivers(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $otherRoute = createRouteForSyncRouteDrivers($otherDispatcher);
    $driver = createDriverForSyncRouteDrivers($dispatcher, 'DRIVER-123');

    Sanctum::actingAs($dispatcherUser);

    $this->putJson("/api/dispatcher/route/{$otherRoute->id}/drivers", [
        'drivers' => [
            [
                'driver_id' => $driver->id,
                'is_convoy_leader' => true,
            ],
        ],
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'You cannot assign drivers to a route you did not create.');
});

it('rejects route driver assignment for a closed route', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForSyncRouteDrivers($dispatcherUser);
    $route = createRouteForSyncRouteDrivers($dispatcher);
    $driver = createDriverForSyncRouteDrivers($dispatcher, 'DRIVER-123');

    $route->forceFill([
        'closed_at' => '2026-10-01 10:00:00',
    ])->save();

    Sanctum::actingAs($dispatcherUser);

    $this->putJson("/api/dispatcher/route/{$route->id}/drivers", [
        'drivers' => [
            [
                'driver_id' => $driver->id,
                'is_convoy_leader' => true,
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_id']);
});

it('requires a dispatcher user to assign route drivers', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->putJson('/api/dispatcher/route/1/drivers', [
        'drivers' => [],
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can assign drivers to routes.');
});

it('returns not found when assigning drivers to a missing route', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForSyncRouteDrivers($dispatcherUser);

    Sanctum::actingAs($dispatcherUser);

    $this->putJson('/api/dispatcher/route/999/drivers', [
        'drivers' => [],
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Route not found.');
});

it('requires authentication to assign route drivers', function () {
    $this->putJson('/api/dispatcher/route/1/drivers', [
        'drivers' => [],
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

function createDispatcherForSyncRouteDrivers(User $user): Dispatcher
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

function createRouteForSyncRouteDrivers(
    Dispatcher $dispatcher,
    string $startDate = '2026-10-01',
    string $endDate = '2026-10-05'
): DispatcherRoute {
    return DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);
}

function createDriverForSyncRouteDrivers(
    Dispatcher $dispatcher,
    string $licenseNumber
): Driver {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);

    return Driver::query()->create([
        'user_id' => $user->id,
        'dispatcher_id' => $dispatcher->id,
        'license_number' => $licenseNumber,
    ]);
}
