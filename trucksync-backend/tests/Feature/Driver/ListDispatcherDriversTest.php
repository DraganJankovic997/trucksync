<?php

use App\Models\Dispatcher;
use App\Models\Driver;
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
