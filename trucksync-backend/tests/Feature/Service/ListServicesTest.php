<?php

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns all services for an authenticated user', function () {
    $secondService = Service::query()->create([
        'name' => 'Washout',
        'measurement_unit' => 'bay',
    ]);
    $firstService = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'tire',
    ]);

    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/service')
        ->assertOk()
        ->assertJsonCount(2, 'data.services')
        ->assertJsonPath('data.services.0.id', $firstService->id)
        ->assertJsonPath('data.services.0.name', 'Tire replacement')
        ->assertJsonPath('data.services.0.measurement_unit', 'tire')
        ->assertJsonPath('data.services.1.id', $secondService->id)
        ->assertJsonPath('data.services.1.name', 'Washout')
        ->assertJsonPath('data.services.1.measurement_unit', 'bay');
});

it('returns an empty service list when no services exist', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/service')
        ->assertOk()
        ->assertJsonPath('data.services', []);
});

it('requires authentication to list services', function () {
    $this->getJson('/api/service')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});
