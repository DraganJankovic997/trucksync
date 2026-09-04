<?php

use App\Models\Dispatcher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns the authenticated users dispatcher profile', function () {
    $otherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    Dispatcher::query()->create([
        'user_id' => $otherUser->id,
        'company_name' => 'Other Dispatch',
        'country' => 'Serbia',
        'city' => 'Novi Sad',
        'address' => 'Other Street 5',
        'post_code' => '21000',
        'registration_number' => 'OTHER-123',
    ]);

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = Dispatcher::query()->create([
        'user_id' => $user->id,
        'company_name' => 'Acme Dispatch',
        'country' => 'Serbia',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'REG-1234',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/dispatcher')
        ->assertOk()
        ->assertJsonPath('data.dispatcher.id', $dispatcher->id)
        ->assertJsonPath('data.dispatcher.user_id', $user->id)
        ->assertJsonPath('data.dispatcher.company_name', 'Acme Dispatch')
        ->assertJsonPath('data.dispatcher.country', 'Serbia')
        ->assertJsonPath('data.dispatcher.city', 'Belgrade')
        ->assertJsonPath('data.dispatcher.address', 'Main Street 1')
        ->assertJsonPath('data.dispatcher.post_code', '11000')
        ->assertJsonPath('data.dispatcher.registration_number', 'REG-1234');
});

it('requires authentication to view a dispatcher profile', function () {
    $this->getJson('/api/dispatcher')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('returns not found when the authenticated user has no dispatcher profile', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->getJson('/api/dispatcher')
        ->assertNotFound()
        ->assertJsonPath('message', 'Dispatcher profile not found.');
});
