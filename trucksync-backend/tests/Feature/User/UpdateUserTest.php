<?php

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('updates the authenticated user', function () {
    Country::query()->create([
        'code' => 'RS',
        'name' => 'Serbia',
    ]);

    $user = User::factory()->create([
        'first_name' => 'Sam',
        'last_name' => 'Driver',
        'email' => 'sam.driver@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/user', [
        'first_name' => '  Samuel  ',
        'last_name' => '  Updated  ',
        'email' => '  SAMUEL.UPDATED@EXAMPLE.COM  ',
        'country' => '  Serbia  ',
        'phone_number' => '  +381601234567  ',
        'profile_type' => 'driver',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'User updated successfully.')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.first_name', 'Samuel')
        ->assertJsonPath('data.user.last_name', 'Updated')
        ->assertJsonPath('data.user.email', 'samuel.updated@example.com')
        ->assertJsonPath('data.user.country', 'Serbia')
        ->assertJsonPath('data.user.phone_number', '+381601234567')
        ->assertJsonPath('data.user.profile_type', 'driver')
        ->assertJsonMissingPath('data.user.password');

    $user->refresh();

    expect($user->first_name)->toBe('Samuel')
        ->and($user->last_name)->toBe('Updated')
        ->and($user->email)->toBe('samuel.updated@example.com')
        ->and($user->country)->toBe('Serbia')
        ->and($user->phone_number)->toBe('+381601234567')
        ->and($user->profile_type)->toBe('driver');
});

it('requires authentication to update a user', function () {
    $this->putJson('/api/user', [
        'first_name' => 'Sam',
        'last_name' => 'Driver',
        'email' => 'sam.driver@example.com',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('validates user update fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->putJson('/api/user', [
        'first_name' => '',
        'last_name' => '',
        'email' => 'not-an-email',
        'country' => 'Atlantis',
        'phone_number' => str_repeat('1', 31),
        'profile_type' => 'admin',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'email',
            'country',
            'phone_number',
            'profile_type',
        ]);
});

it('requires full user profile fields for update', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/user', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'email',
            'country',
            'phone_number',
            'profile_type',
        ]);
});

it('validates unique email addresses except for the authenticated user', function () {
    Country::query()->create([
        'code' => 'RS',
        'name' => 'Serbia',
    ]);

    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $user = User::factory()->create([
        'first_name' => 'Sam',
        'last_name' => 'Driver',
        'email' => 'sam.driver@example.com',
    ]);

    Sanctum::actingAs($user);

    $this->putJson('/api/user', [
        'first_name' => 'Sam',
        'last_name' => 'Driver',
        'email' => 'taken@example.com',
        'country' => 'Serbia',
        'phone_number' => '+381601234567',
        'profile_type' => 'driver',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    expect($user->refresh()->email)->toBe('sam.driver@example.com');

    $this->putJson('/api/user', [
        'first_name' => 'Sam',
        'last_name' => 'Driver',
        'email' => 'SAM.DRIVER@EXAMPLE.COM',
        'country' => 'Serbia',
        'phone_number' => '+381601234567',
        'profile_type' => 'driver',
    ])
        ->assertOk()
        ->assertJsonPath('data.user.email', 'sam.driver@example.com');
});
