<?php

use App\Models\RestStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns the authenticated users rest stop profile', function () {
    RestStop::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'rest_stop',
        ])->id,
        'city' => 'Novi Sad',
        'address' => 'Other Highway 5',
        'post_code' => '21000',
        'works_from' => '07:00',
        'works_to' => '20:00',
    ]);

    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = RestStop::query()->create([
        'user_id' => $user->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/rest-stop')
        ->assertOk()
        ->assertJsonPath('data.rest_stop.id', $restStop->id)
        ->assertJsonPath('data.rest_stop.user_id', $user->id)
        ->assertJsonMissingPath('data.rest_stop.country')
        ->assertJsonPath('data.rest_stop.city', 'Belgrade')
        ->assertJsonPath('data.rest_stop.address', 'Highway 1')
        ->assertJsonPath('data.rest_stop.post_code', '11000')
        ->assertJsonPath('data.rest_stop.works_from', '08:00')
        ->assertJsonPath('data.rest_stop.works_to', '22:00')
        ->assertJsonMissingPath('message');
});

it('requires authentication to view a rest stop profile', function () {
    $this->getJson('/api/rest-stop')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('returns not found when the authenticated user has no rest stop profile', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    $this->getJson('/api/rest-stop')
        ->assertNotFound()
        ->assertJsonPath('message', 'Rest stop profile not found.');
});
