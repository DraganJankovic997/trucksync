<?php

use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists drivers linked to the authenticated dispatcher', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForListDispatcherDrivers($dispatcherUser);
    $firstDriver = createDriverForListDispatcherDrivers(
        $dispatcher,
        'Ada',
        'Lovelace',
        'ada.driver@example.com',
        'ADA-123'
    );
    $secondDriver = createDriverForListDispatcherDrivers(
        $dispatcher,
        'Grace',
        'Hopper',
        'grace.driver@example.com',
        'GRACE-456'
    );
    $otherDispatcher = createDispatcherForListDispatcherDrivers(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    createDriverForListDispatcherDrivers(
        $otherDispatcher,
        'Other',
        'Driver',
        'other.driver@example.com',
        'OTHER-789'
    );

    Sanctum::actingAs($dispatcherUser);

    $this->getJson('/api/dispatcher/drivers')
        ->assertOk()
        ->assertJsonCount(2, 'data.drivers')
        ->assertJsonPath('data.drivers.0.id', $firstDriver->id)
        ->assertJsonPath('data.drivers.0.license_number', 'ADA-123')
        ->assertJsonPath('data.drivers.0.user.email', 'ada.driver@example.com')
        ->assertJsonPath('data.drivers.1.id', $secondDriver->id)
        ->assertJsonPath('data.drivers.1.user.first_name', 'Grace');
});

it('marks dispatcher drivers unavailable when assigned to overlapping open routes', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForListDispatcherDrivers($dispatcherUser);
    $targetRoute = createRouteForListDispatcherDrivers(
        $dispatcher,
        '2026-10-01',
        '2026-10-05'
    );
    $overlappingRoute = createRouteForListDispatcherDrivers(
        $dispatcher,
        '2026-10-03',
        '2026-10-06'
    );
    $nonOverlappingRoute = createRouteForListDispatcherDrivers(
        $dispatcher,
        '2026-10-06',
        '2026-10-08'
    );
    $closedOverlappingRoute = createRouteForListDispatcherDrivers(
        $dispatcher,
        '2026-10-02',
        '2026-10-04'
    );
    $closedOverlappingRoute->forceFill([
        'closed_at' => '2026-09-25 12:00:00',
    ])->save();

    $availableDriver = createDriverForListDispatcherDrivers(
        $dispatcher,
        'Free',
        'Driver',
        'free.driver@example.com',
        'FREE-123'
    );
    $busyDriver = createDriverForListDispatcherDrivers(
        $dispatcher,
        'Busy',
        'Driver',
        'busy.driver@example.com',
        'BUSY-456'
    );
    $currentRouteDriver = createDriverForListDispatcherDrivers(
        $dispatcher,
        'Current',
        'Driver',
        'current.driver@example.com',
        'CURRENT-789'
    );
    $closedRouteDriver = createDriverForListDispatcherDrivers(
        $dispatcher,
        'Closed',
        'Driver',
        'closed.driver@example.com',
        'CLOSED-123'
    );
    $futureDriver = createDriverForListDispatcherDrivers(
        $dispatcher,
        'Future',
        'Driver',
        'future.driver@example.com',
        'FUTURE-456'
    );

    $overlappingRoute->drivers()->attach($busyDriver->id, [
        'is_convoy_leader' => false,
    ]);
    $targetRoute->drivers()->attach($currentRouteDriver->id, [
        'is_convoy_leader' => false,
    ]);
    $closedOverlappingRoute->drivers()->attach($closedRouteDriver->id, [
        'is_convoy_leader' => false,
    ]);
    $nonOverlappingRoute->drivers()->attach($futureDriver->id, [
        'is_convoy_leader' => false,
    ]);

    Sanctum::actingAs($dispatcherUser);

    $response = $this->getJson("/api/dispatcher/drivers?available_for_route={$targetRoute->id}")
        ->assertOk()
        ->assertJsonCount(5, 'data.drivers');

    $drivers = collect($response->json('data.drivers'))->keyBy('id');

    expect($drivers->get($availableDriver->id)['is_available'])->toBeTrue()
        ->and($drivers->get($busyDriver->id)['is_available'])->toBeFalse()
        ->and($drivers->get($currentRouteDriver->id)['is_available'])->toBeTrue()
        ->and($drivers->get($closedRouteDriver->id)['is_available'])->toBeTrue()
        ->and($drivers->get($futureDriver->id)['is_available'])->toBeTrue();
});

it('forbids viewing driver availability for a route owned by another dispatcher', function () {
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForListDispatcherDrivers($dispatcherUser);
    $otherDispatcher = createDispatcherForListDispatcherDrivers(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $otherRoute = createRouteForListDispatcherDrivers(
        $otherDispatcher,
        '2026-10-01',
        '2026-10-05'
    );

    Sanctum::actingAs($dispatcherUser);

    $this->getJson("/api/dispatcher/drivers?available_for_route={$otherRoute->id}")
        ->assertForbidden()
        ->assertJsonPath('message', 'You cannot view driver availability for a route you did not create.');
});

it('requires a dispatcher user to list dispatcher drivers', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/dispatcher/drivers')
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can view drivers.');
});

it('returns not found when the authenticated dispatcher profile is missing', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->getJson('/api/dispatcher/drivers')
        ->assertNotFound()
        ->assertJsonPath('message', 'Dispatcher profile not found.');
});

it('requires authentication to list dispatcher drivers', function () {
    $this->getJson('/api/dispatcher/drivers')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

function createDispatcherForListDispatcherDrivers(User $user): Dispatcher
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

function createRouteForListDispatcherDrivers(
    Dispatcher $dispatcher,
    string $startDate,
    string $endDate
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

function createDriverForListDispatcherDrivers(
    Dispatcher $dispatcher,
    string $firstName,
    string $lastName,
    string $email,
    string $licenseNumber
): Driver {
    $user = User::factory()->create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'profile_type' => 'driver',
    ]);

    return Driver::query()->create([
        'user_id' => $user->id,
        'dispatcher_id' => $dispatcher->id,
        'license_number' => $licenseNumber,
    ]);
}
