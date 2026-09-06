<?php

use App\Models\RestStop;
use App\Models\RestStopService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns services for a rest stop without authentication', function () {
    $restStop = createRestStopForListServices();
    $otherRestStop = createRestStopForListServices();
    $washout = Service::query()->create([
        'name' => 'Washout',
        'measurement_unit' => 'bay',
    ]);
    $tireReplacement = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'tire',
    ]);
    $parking = Service::query()->create([
        'name' => 'Parking',
        'measurement_unit' => 'space',
    ]);

    RestStopService::query()->create([
        'rest_stop_id' => $restStop->id,
        'service_id' => $washout->id,
    ]);
    RestStopService::query()->create([
        'rest_stop_id' => $restStop->id,
        'service_id' => $tireReplacement->id,
    ]);
    RestStopService::query()->create([
        'rest_stop_id' => $otherRestStop->id,
        'service_id' => $parking->id,
    ]);

    $this->getJson("/api/rest-stop/services/{$restStop->id}")
        ->assertOk()
        ->assertJsonCount(2, 'data.services')
        ->assertJsonPath('data.services.0.id', $tireReplacement->id)
        ->assertJsonPath('data.services.0.name', 'Tire replacement')
        ->assertJsonPath('data.services.0.measurement_unit', 'tire')
        ->assertJsonPath('data.services.1.id', $washout->id)
        ->assertJsonPath('data.services.1.name', 'Washout')
        ->assertJsonPath('data.services.1.measurement_unit', 'bay')
        ->assertJsonMissingPath('data.services.0.rest_stop_id')
        ->assertJsonMissingPath('data.services.0.service_id');
});

it('returns an empty service list for a rest stop without services', function () {
    $restStop = createRestStopForListServices();

    $this->getJson("/api/rest-stop/services/{$restStop->id}")
        ->assertOk()
        ->assertJsonPath('data.services', []);
});

it('returns not found when listing services for a missing rest stop', function () {
    $this->getJson('/api/rest-stop/services/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Rest stop not found.');
});

function createRestStopForListServices(): RestStop
{
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);

    return RestStop::query()->create([
        'user_id' => $user->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}
