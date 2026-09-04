<?php

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns a service by id for an authenticated user', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/service/{$service->id}")
        ->assertOk()
        ->assertJsonPath('data.service.id', $service->id)
        ->assertJsonPath('data.service.name', 'Tire replacement');
});

it('requires authentication to view a service', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    $this->getJson("/api/service/{$service->id}")
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('returns not found when viewing a missing service', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/service/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Service not found.');
});
