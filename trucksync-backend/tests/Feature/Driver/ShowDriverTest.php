<?php

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns the authenticated users driver profile', function () {
    Driver::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'driver',
        ])->id,
        'license_number' => 'OTHER-123',
    ]);

    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $driver = Driver::query()->create([
        'user_id' => $user->id,
        'license_number' => 'OWN-123',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/driver')
        ->assertOk()
        ->assertJsonPath('data.driver.id', $driver->id)
        ->assertJsonPath('data.driver.user_id', $user->id)
        ->assertJsonPath('data.driver.dispatcher_id', null)
        ->assertJsonPath('data.driver.license_number', 'OWN-123')
        ->assertJsonPath('data.driver.is_dispatcher_approved', false)
        ->assertJsonMissingPath('message');
});

it('requires authentication to view a driver profile', function () {
    $this->getJson('/api/driver')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('returns not found when the authenticated user has no driver profile', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/driver')
        ->assertNotFound()
        ->assertJsonPath('message', 'Driver profile not found.');
});
