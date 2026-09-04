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
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Service created successfully.')
        ->assertJsonPath('data.service.name', 'Tire replacement');

    $this->assertDatabaseHas('services', [
        'name' => 'Tire replacement',
    ]);
});

it('forbids non-admin users from creating a service', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/service', [
        'name' => 'Tire replacement',
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'User does not have the right roles.');
});

it('requires authentication to create a service', function () {
    $this->postJson('/api/service', [
        'name' => 'Tire replacement',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('validates service names', function () {
    Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($user);

    $this->postJson('/api/service', [
        'name' => '  Tire replacement  ',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $this->postJson('/api/service', [
        'name' => str_repeat('A', 256),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $this->postJson('/api/service', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});
