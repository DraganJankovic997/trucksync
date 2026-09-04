<?php

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('deletes a service for an admin user', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($user);

    $this->deleteJson("/api/service/{$service->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Service deleted successfully.');

    $this->assertDatabaseMissing('services', [
        'id' => $service->id,
    ]);
});

it('forbids non-admin users from deleting a service', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson("/api/service/{$service->id}")
        ->assertForbidden()
        ->assertJsonPath('message', 'User does not have the right roles.');

    $this->assertDatabaseHas('services', [
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
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($user);

    $this->deleteJson('/api/service/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Service not found.');
});
