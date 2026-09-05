<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('closes a route for the authenticated dispatcher owner', function () {
    $closedAt = Carbon::parse('2026-10-06 12:34:56');
    Carbon::setTestNow($closedAt);

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = createDispatcherForCloseRouteUser($user);
    $route = createRouteForCloseRouteDispatcher($dispatcher);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/dispatcher/route/close/{$route->id}");

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Route closed successfully.')
        ->assertJsonPath('data.route.id', $route->id)
        ->assertJsonPath('data.route.dispatcher_id', $dispatcher->id)
        ->assertJsonPath('data.route.origin', 'Belgrade warehouse')
        ->assertJsonPath('data.route.destination', 'Berlin logistics hub');

    expect($response->json('data.route.closed_at'))->not->toBeNull()
        ->and($route->refresh()->closed_at->toDateTimeString())->toBe('2026-10-06 12:34:56');

    $this->assertDatabaseHas('routes', [
        'id' => $route->id,
        'dispatcher_id' => $dispatcher->id,
        'closed_at' => '2026-10-06 12:34:56',
    ]);
});

it('does not close a route owned by another dispatcher', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-06 12:34:56'));

    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForCloseRouteUser($user);

    $otherDispatcher = createDispatcherForCloseRouteUser(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $route = createRouteForCloseRouteDispatcher($otherDispatcher);

    Sanctum::actingAs($user);

    $this->postJson("/api/dispatcher/route/close/{$route->id}")
        ->assertNotFound()
        ->assertJsonPath('message', 'Route not found.');

    expect($route->refresh()->closed_at)->toBeNull();
});

it('requires authentication to close a route', function () {
    $this->postJson('/api/dispatcher/route/close/1')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('forbids non-dispatcher users from closing routes', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/dispatcher/route/close/1')
        ->assertForbidden()
        ->assertJsonPath('message', 'Only dispatcher users can close routes.');
});

it('returns not found when closing a missing route', function () {
    $user = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    createDispatcherForCloseRouteUser($user);

    Sanctum::actingAs($user);

    $this->postJson('/api/dispatcher/route/close/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Route not found.');
});

function createDispatcherForCloseRouteUser(User $user): Dispatcher
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

function createRouteForCloseRouteDispatcher(Dispatcher $dispatcher): DispatcherRoute
{
    return DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ]);
}
