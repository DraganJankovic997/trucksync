<?php

use App\Models\Country;
use App\Models\Dispatcher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a dispatcher profile for an authenticated dispatcher user', function () {
    createDispatcherCountry();

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/dispatcher', [
        'company_name' => '  Acme Dispatch  ',
        'country' => '  Serbia  ',
        'city' => '  Belgrade  ',
        'address' => '  Main Street 1  ',
        'post_code' => '  11000  ',
        'registration_number' => '  REG-1234  ',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Dispatcher profile created successfully.')
        ->assertJsonPath('data.dispatcher.user_id', $user->id)
        ->assertJsonPath('data.dispatcher.company_name', 'Acme Dispatch')
        ->assertJsonPath('data.dispatcher.country', 'Serbia')
        ->assertJsonPath('data.dispatcher.city', 'Belgrade')
        ->assertJsonPath('data.dispatcher.address', 'Main Street 1')
        ->assertJsonPath('data.dispatcher.post_code', '11000')
        ->assertJsonPath('data.dispatcher.registration_number', 'REG-1234');

    $this->assertDatabaseHas('dispatchers', [
        'user_id' => $user->id,
        'company_name' => 'Acme Dispatch',
        'country' => 'Serbia',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'REG-1234',
    ]);
});

it('updates the authenticated dispatcher profile if it already exists', function () {
    createDispatcherCountry();

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = Dispatcher::query()->create([
        'user_id' => $user->id,
        'company_name' => 'Old Dispatch',
        'country' => 'Serbia',
        'city' => 'Novi Sad',
        'address' => 'Old Street 5',
        'post_code' => '21000',
        'registration_number' => 'OLD-123',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/dispatcher', [
        'company_name' => 'New Dispatch',
        'country' => 'Serbia',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'NEW-456',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Dispatcher profile updated successfully.')
        ->assertJsonPath('data.dispatcher.id', $dispatcher->id)
        ->assertJsonPath('data.dispatcher.user_id', $user->id)
        ->assertJsonPath('data.dispatcher.company_name', 'New Dispatch')
        ->assertJsonPath('data.dispatcher.registration_number', 'NEW-456');

    expect(Dispatcher::query()->where('user_id', $user->id)->count())->toBe(1);

    $this->assertDatabaseHas('dispatchers', [
        'id' => $dispatcher->id,
        'user_id' => $user->id,
        'company_name' => 'New Dispatch',
        'country' => 'Serbia',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'NEW-456',
    ]);
});

it('requires authentication to save a dispatcher profile', function () {
    $this->postJson('/api/dispatcher', [
        'company_name' => 'Acme Dispatch',
        'country' => 'Serbia',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'REG-1234',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-dispatcher users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/dispatcher', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can create or update dispatcher profiles.');

    expect(Dispatcher::query()->count())->toBe(0);
});

it('validates required dispatcher profile fields', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->postJson('/api/dispatcher', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'company_name',
            'country',
            'city',
            'address',
            'post_code',
            'registration_number',
        ]);
});

it('validates dispatcher profile field values', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->postJson('/api/dispatcher', [
        'company_name' => str_repeat('A', 256),
        'country' => 'Atlantis',
        'city' => str_repeat('B', 256),
        'address' => str_repeat('C', 256),
        'post_code' => str_repeat('D', 256),
        'registration_number' => str_repeat('E', 256),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'company_name',
            'country',
            'city',
            'address',
            'post_code',
            'registration_number',
        ]);
});

function createDispatcherCountry(): void
{
    Country::query()->create([
        'code' => 'RS',
        'name' => 'Serbia',
    ]);
}
