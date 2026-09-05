<?php

use App\Models\RestStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a rest stop profile for an authenticated rest stop user', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/rest-stop', [
        'city' => '  Belgrade  ',
        'address' => '  Highway 1  ',
        'post_code' => '  11000  ',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Rest stop profile created successfully.')
        ->assertJsonPath('data.rest_stop.user_id', $user->id)
        ->assertJsonMissingPath('data.rest_stop.country')
        ->assertJsonPath('data.rest_stop.city', 'Belgrade')
        ->assertJsonPath('data.rest_stop.address', 'Highway 1')
        ->assertJsonPath('data.rest_stop.post_code', '11000')
        ->assertJsonPath('data.rest_stop.works_from', '08:00')
        ->assertJsonPath('data.rest_stop.works_to', '22:00');

    $this->assertDatabaseHas('rest_stops', [
        'user_id' => $user->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
        'is_approved' => false,
    ]);

    expect(RestStop::query()->where('user_id', $user->id)->first()?->is_approved)->toBeFalse();
});

it('updates the authenticated rest stop profile if it already exists', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = RestStop::query()->create([
        'user_id' => $user->id,
        'city' => 'Novi Sad',
        'address' => 'Old Highway 5',
        'post_code' => '21000',
        'works_from' => '07:00',
        'works_to' => '20:00',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/rest-stop', [
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Rest stop profile updated successfully.')
        ->assertJsonPath('data.rest_stop.id', $restStop->id)
        ->assertJsonPath('data.rest_stop.user_id', $user->id)
        ->assertJsonMissingPath('data.rest_stop.country')
        ->assertJsonPath('data.rest_stop.city', 'Belgrade')
        ->assertJsonPath('data.rest_stop.address', 'Highway 1')
        ->assertJsonPath('data.rest_stop.works_from', '08:00')
        ->assertJsonPath('data.rest_stop.works_to', '22:00');

    expect(RestStop::query()->where('user_id', $user->id)->count())->toBe(1);

    $this->assertDatabaseHas('rest_stops', [
        'id' => $restStop->id,
        'user_id' => $user->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
});

it('requires authentication to save a rest stop profile', function () {
    $this->postJson('/api/rest-stop', [
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-rest-stop users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/rest-stop', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only rest stop users can create or update rest stop profiles.');

    expect(RestStop::query()->count())->toBe(0);
});

it('validates required rest stop profile fields', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->postJson('/api/rest-stop', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'city',
            'address',
            'post_code',
            'works_from',
            'works_to',
        ]);
});

it('validates rest stop profile field values', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->postJson('/api/rest-stop', [
        'city' => str_repeat('B', 256),
        'address' => str_repeat('C', 256),
        'post_code' => str_repeat('D', 256),
        'works_from' => '8:00',
        'works_to' => 'evening',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'city',
            'address',
            'post_code',
            'works_from',
            'works_to',
        ]);
});
