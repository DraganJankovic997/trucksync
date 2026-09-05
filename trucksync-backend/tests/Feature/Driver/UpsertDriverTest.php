<?php

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a driver profile for an authenticated driver user', function () {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $dispatcherId = createDispatcherId();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/driver', [
        'license_number' => '  D1234567  ',
        'dispatcher_id' => $dispatcherId,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Driver profile created successfully.')
        ->assertJsonPath('data.driver.user_id', $user->id)
        ->assertJsonPath('data.driver.dispatcher_id', $dispatcherId)
        ->assertJsonPath('data.driver.license_number', 'D1234567')
        ->assertJsonPath('data.driver.is_dispatcher_approved', false);

    $this->assertDatabaseHas('drivers', [
        'user_id' => $user->id,
        'dispatcher_id' => $dispatcherId,
        'license_number' => 'D1234567',
        'is_dispatcher_approved' => false,
    ]);
});

it('updates the authenticated driver profile if it already exists', function () {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $driver = Driver::query()->create([
        'user_id' => $user->id,
        'license_number' => 'OLD-123',
    ]);
    $dispatcherId = createDispatcherId();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/driver', [
        'license_number' => 'NEW-456',
        'dispatcher_id' => $dispatcherId,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Driver profile updated successfully.')
        ->assertJsonPath('data.driver.id', $driver->id)
        ->assertJsonPath('data.driver.user_id', $user->id)
        ->assertJsonPath('data.driver.dispatcher_id', $dispatcherId)
        ->assertJsonPath('data.driver.license_number', 'NEW-456');

    expect(Driver::query()->where('user_id', $user->id)->count())->toBe(1);

    $this->assertDatabaseHas('drivers', [
        'id' => $driver->id,
        'user_id' => $user->id,
        'dispatcher_id' => $dispatcherId,
        'license_number' => 'NEW-456',
    ]);
});

it('requires authentication to save a driver profile', function () {
    $this->postJson('/api/driver', [
        'license_number' => 'D1234567',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-driver users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->postJson('/api/driver', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only driver users can create or update driver profiles.');

    expect(Driver::query()->count())->toBe(0);
});

it('validates driver profile fields', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/driver', [
        'license_number' => str_repeat('A', 256),
        'dispatcher_id' => 999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'license_number',
            'dispatcher_id',
        ]);
});

it('validates unique license numbers except for the authenticated driver profile', function () {
    Driver::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'driver',
        ])->id,
        'license_number' => 'TAKEN-123',
    ]);

    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    Driver::query()->create([
        'user_id' => $user->id,
        'license_number' => 'OWN-123',
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/driver', [
        'license_number' => '  TAKEN-123  ',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['license_number']);

    $this->postJson('/api/driver', [
        'license_number' => 'OWN-123',
    ])
        ->assertOk()
        ->assertJsonPath('data.driver.license_number', 'OWN-123');
});

function createDispatcherId(): int
{
    $dispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);

    return DB::table('dispatchers')->insertGetId([
        'user_id' => $dispatcherUser->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => fake()->unique()->bothify('REG-####'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
