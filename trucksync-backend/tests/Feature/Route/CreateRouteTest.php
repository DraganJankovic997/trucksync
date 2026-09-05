<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a route for the authenticated dispatcher profile', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForRouteUser($user);
    $otherDispatcher = createDispatcherForRouteUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/dispatcher/route', [
        'dispatcher_id' => $otherDispatcher->id,
        'origin' => '  Belgrade warehouse  ',
        'destination' => '  Berlin logistics hub  ',
        'planned_travel_details' => '  Take the A3 corridor and stop near Vienna.  ',
        'convoy_size' => 3,
        'start_date' => '  2026-10-01  ',
        'end_date' => '  2026-10-05  ',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Route created successfully.')
        ->assertJsonPath('data.route.dispatcher_id', $dispatcher->id)
        ->assertJsonPath('data.route.origin', 'Belgrade warehouse')
        ->assertJsonPath('data.route.destination', 'Berlin logistics hub')
        ->assertJsonPath('data.route.planned_travel_details', 'Take the A3 corridor and stop near Vienna.')
        ->assertJsonPath('data.route.convoy_size', 3)
        ->assertJsonPath('data.route.start_date', '2026-10-01')
        ->assertJsonPath('data.route.end_date', '2026-10-05')
        ->assertJsonPath('data.route.closed_at', null);

    $this->assertDatabaseHas('routes', [
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
        'closed_at' => null,
    ]);

    $this->assertDatabaseMissing('routes', [
        'dispatcher_id' => $otherDispatcher->id,
        'origin' => 'Belgrade warehouse',
    ]);
});

it('creates a route without planned travel details', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForRouteUser($user);

    Sanctum::actingAs($user);

    $this->postJson('/api/dispatcher/route', [
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'convoy_size' => 2,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ])
        ->assertCreated()
        ->assertJsonPath('data.route.dispatcher_id', $dispatcher->id)
        ->assertJsonPath('data.route.planned_travel_details', null);
});

it('requires authentication to create a route', function () {
    $this->postJson('/api/dispatcher/route', [
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'convoy_size' => 2,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-dispatcher users before validating the request', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/dispatcher/route', [])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can create routes.');

    expect(DispatcherRoute::query()->count())->toBe(0);
});

it('returns not found when the authenticated dispatcher has no dispatcher profile', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->postJson('/api/dispatcher/route', [
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'convoy_size' => 2,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Dispatcher profile not found.');
});

it('validates required route fields', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForRouteUser($user);

    Sanctum::actingAs($user);

    $this->postJson('/api/dispatcher/route', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'origin',
            'destination',
            'convoy_size',
            'start_date',
            'end_date',
        ])
        ->assertJsonMissingValidationErrors(['planned_travel_details']);
});

it('validates route field values', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForRouteUser($user);

    Sanctum::actingAs($user);

    $this->postJson('/api/dispatcher/route', [
        'origin' => ['Belgrade warehouse'],
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => ['Use A3 corridor'],
        'convoy_size' => 0,
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-01',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'origin',
            'planned_travel_details',
            'convoy_size',
            'end_date',
        ]);
});

function createDispatcherForRouteUser(User $user): Dispatcher
{
    return Dispatcher::query()->create([
        'user_id' => $user->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => fake()->unique()->bothify('REG-####'),
    ]);
}
