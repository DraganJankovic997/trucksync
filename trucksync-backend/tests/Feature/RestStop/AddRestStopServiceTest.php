<?php

use App\Models\RestStop;
use App\Models\RestStopService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('adds a service to the authenticated rest stop', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForUser($user);
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/rest-stop/services/add', [
        'service_id' => $service->id,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Rest stop service added successfully.')
        ->assertJsonPath('data.rest_stop_service.rest_stop_id', $restStop->id)
        ->assertJsonPath('data.rest_stop_service.service_id', $service->id)
        ->assertJsonMissingPath('data.rest_stop_service.id');

    $this->assertDatabaseHas('rest_stop_services', [
        'rest_stop_id' => $restStop->id,
        'service_id' => $service->id,
    ]);
});

it('does not create duplicate rest stop services', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = createRestStopForUser($user);
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    RestStopService::query()->create([
        'rest_stop_id' => $restStop->id,
        'service_id' => $service->id,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/rest-stop/services/add', [
        'service_id' => $service->id,
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Rest stop service already exists.')
        ->assertJsonPath('data.rest_stop_service.rest_stop_id', $restStop->id)
        ->assertJsonPath('data.rest_stop_service.service_id', $service->id);

    expect(RestStopService::query()->count())->toBe(1);
});

it('requires authentication to add a rest stop service', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    $this->postJson('/api/rest-stop/services/add', [
        'service_id' => $service->id,
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-rest-stop users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/rest-stop/services/add', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only rest stop users can add rest stop services.');

    expect(RestStopService::query()->count())->toBe(0);
});

it('returns not found when the authenticated rest stop user has no rest stop profile', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->postJson('/api/rest-stop/services/add', [
        'service_id' => $service->id,
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Rest stop profile not found.');
});

it('validates rest stop service payloads', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    createRestStopForUser($user);

    Sanctum::actingAs($user);

    $this->postJson('/api/rest-stop/services/add', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['service_id']);

    $this->postJson('/api/rest-stop/services/add', [
        'service_id' => 'not-an-id',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['service_id']);

    $this->postJson('/api/rest-stop/services/add', [
        'service_id' => 999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['service_id']);
});

function createRestStopForUser(User $user): RestStop
{
    return RestStop::query()->create([
        'user_id' => $user->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}
