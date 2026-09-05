<?php

use App\Models\Dispatcher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns all dispatchers for an authenticated user without profile type limitation', function () {
    $authenticatedUser = User::factory()->create([
        'profile_type' => 'driver',
    ]);

    $firstDispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $firstDispatcher = Dispatcher::query()->create([
        'user_id' => $firstDispatcherUser->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'REG-1234',
    ]);

    $secondDispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $secondDispatcher = Dispatcher::query()->create([
        'user_id' => $secondDispatcherUser->id,
        'company_name' => 'North Dispatch',
        'city' => 'Novi Sad',
        'address' => 'River Street 2',
        'post_code' => '21000',
        'registration_number' => 'REG-5678',
    ]);

    Sanctum::actingAs($authenticatedUser);

    $this->getJson('/api/dispatcher/all')
        ->assertOk()
        ->assertJsonCount(2, 'data.dispatchers')
        ->assertJsonPath('data.dispatchers.0.id', $firstDispatcher->id)
        ->assertJsonPath('data.dispatchers.0.user_id', $firstDispatcherUser->id)
        ->assertJsonPath('data.dispatchers.0.company_name', 'Acme Dispatch')
        ->assertJsonMissingPath('data.dispatchers.0.country')
        ->assertJsonPath('data.dispatchers.0.city', 'Belgrade')
        ->assertJsonPath('data.dispatchers.0.address', 'Main Street 1')
        ->assertJsonPath('data.dispatchers.0.post_code', '11000')
        ->assertJsonPath('data.dispatchers.0.registration_number', 'REG-1234')
        ->assertJsonPath('data.dispatchers.1.id', $secondDispatcher->id)
        ->assertJsonPath('data.dispatchers.1.user_id', $secondDispatcherUser->id)
        ->assertJsonPath('data.dispatchers.1.company_name', 'North Dispatch')
        ->assertJsonMissingPath('data.dispatchers.1.country')
        ->assertJsonPath('data.dispatchers.1.city', 'Novi Sad')
        ->assertJsonPath('data.dispatchers.1.address', 'River Street 2')
        ->assertJsonPath('data.dispatchers.1.post_code', '21000')
        ->assertJsonPath('data.dispatchers.1.registration_number', 'REG-5678');
});

it('returns an empty dispatcher list when no dispatchers exist', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->getJson('/api/dispatcher/all')
        ->assertOk()
        ->assertJsonPath('data.dispatchers', []);
});

it('requires authentication to list dispatchers', function () {
    $this->getJson('/api/dispatcher/all')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});
