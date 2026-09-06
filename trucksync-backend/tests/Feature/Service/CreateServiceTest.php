<?php

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('creates a service for an admin user', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/service', [
        'name' => '  Tire replacement  ',
        'measurement_unit' => '  tire  ',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Service created successfully.')
        ->assertJsonPath('data.service.name', 'Tire replacement')
        ->assertJsonPath('data.service.measurement_unit', 'tire');

    $this->assertDatabaseHas('services', [
        'name' => 'Tire replacement',
        'measurement_unit' => 'tire',
    ]);
});

it('forbids non-admin users from creating a service', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/service', [
        'name' => 'Tire replacement',
        'measurement_unit' => 'tire',
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'User does not have the right roles.');
});

it('requires authentication to create a service', function () {
    $this->postJson('/api/service', [
        'name' => 'Tire replacement',
        'measurement_unit' => 'tire',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('validates service names', function () {
    Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'tire',
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($user);

    $this->postJson('/api/service', [
        'name' => '  Tire replacement  ',
        'measurement_unit' => 'tire',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $this->postJson('/api/service', [
        'name' => str_repeat('A', 256),
        'measurement_unit' => 'tire',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $this->postJson('/api/service', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name'])
        ->assertJsonMissingValidationErrors(['measurement_unit']);
});

it('creates a service without a measurement unit', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/service', [
        'name' => 'Tire replacement',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.service.name', 'Tire replacement')
        ->assertJsonPath('data.service.measurement_unit', null);

    $this->assertDatabaseHas('services', [
        'name' => 'Tire replacement',
        'measurement_unit' => null,
    ]);
});

it('validates service measurement units', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($user);

    $this->postJson('/api/service', [
        'name' => 'Tire replacement',
        'measurement_unit' => str_repeat('A', 256),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['measurement_unit']);
});

it('stores blank service measurement units as null', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/service', [
        'name' => 'Tire replacement',
        'measurement_unit' => '   ',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.service.measurement_unit', null);

    $this->assertDatabaseHas('services', [
        'name' => 'Tire replacement',
        'measurement_unit' => null,
    ]);
});
