<?php

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('deletes a service for an authenticated user', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson("/api/service/{$service->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Service deleted successfully.');

    $this->assertDatabaseMissing('services', [
        'id' => $service->id,
    ]);
});

it('requires authentication to delete a service', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    $this->deleteJson("/api/service/{$service->id}")
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('returns not found when deleting a missing service', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/service/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Service not found.');
});
