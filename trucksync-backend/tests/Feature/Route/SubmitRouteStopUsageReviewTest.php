<?php

use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('submits a route stop usage review for the assigned convoy leader', function () {
    $usedAt = Carbon::parse('2026-10-06 12:34:56');
    Carbon::setTestNow($usedAt);

    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $dispatcher = createDispatcherForRouteStopUsageReviewEndpoint();
    $driver = createDriverForRouteStopUsageReviewEndpoint($dispatcher, $user, 'LEADER-123');
    $route = createRouteForRouteStopUsageReviewEndpoint($dispatcher);
    $restStop = createRestStopForRouteStopUsageReviewEndpoint();
    $routeStop = createRouteStopForRouteStopUsageReviewEndpoint($route, $restStop);

    $route->drivers()->attach($driver->id, [
        'is_convoy_leader' => true,
    ]);

    Sanctum::actingAs($user);

    $this->postJson("/api/driver/route-stop/{$routeStop->id}/usage-review", [
        'rating' => 4,
        'report' => 'Fuel was available, but showers were missing.',
        'is_report' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('message', 'Route stop usage review submitted successfully.')
        ->assertJsonPath('data.route_stop_usage.route_stop_id', $routeStop->id)
        ->assertJsonPath('data.route_stop_usage.driver_id', $driver->id)
        ->assertJsonPath('data.route_stop_usage.rest_stop_id', $restStop->id)
        ->assertJsonPath('data.route_stop_usage.used_at', $usedAt->toJSON())
        ->assertJsonPath('data.route_stop_usage.rating', 4)
        ->assertJsonPath('data.route_stop_usage.report', 'Fuel was available, but showers were missing.')
        ->assertJsonPath('data.route_stop_usage.is_report', true);

    $this->assertDatabaseHas('route_stop_usages', [
        'route_stop_id' => $routeStop->id,
        'driver_id' => $driver->id,
        'rest_stop_id' => $restStop->id,
        'used_at' => '2026-10-06 12:34:56',
        'rating' => 4,
        'report' => 'Fuel was available, but showers were missing.',
        'is_report' => true,
    ]);
});

it('updates an existing route stop usage review without changing used at', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-06 12:34:56'));

    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $dispatcher = createDispatcherForRouteStopUsageReviewEndpoint();
    $driver = createDriverForRouteStopUsageReviewEndpoint($dispatcher, $user, 'LEADER-123');
    $route = createRouteForRouteStopUsageReviewEndpoint($dispatcher);
    $restStop = createRestStopForRouteStopUsageReviewEndpoint();
    $routeStop = createRouteStopForRouteStopUsageReviewEndpoint($route, $restStop);

    $route->drivers()->attach($driver->id, [
        'is_convoy_leader' => true,
    ]);

    RouteStopUsage::query()->create([
        'route_stop_id' => $routeStop->id,
        'driver_id' => $driver->id,
        'rest_stop_id' => $restStop->id,
        'used_at' => '2026-10-02 11:20:00',
        'rating' => 2,
        'report' => 'Old note.',
        'is_report' => false,
    ]);

    Sanctum::actingAs($user);

    $this->postJson("/api/driver/route-stop/{$routeStop->id}/usage-review", [
        'rating' => 5,
        'report' => 'Updated report after admin follow-up.',
        'is_report' => true,
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Route stop usage review submitted successfully.')
        ->assertJsonPath('data.route_stop_usage.used_at', Carbon::parse('2026-10-02 11:20:00')->toJSON())
        ->assertJsonPath('data.route_stop_usage.rating', 5)
        ->assertJsonPath('data.route_stop_usage.report', 'Updated report after admin follow-up.')
        ->assertJsonPath('data.route_stop_usage.is_report', true);

    expect(RouteStopUsage::query()->where('route_stop_id', $routeStop->id)->count())->toBe(1);
    $this->assertDatabaseHas('route_stop_usages', [
        'route_stop_id' => $routeStop->id,
        'driver_id' => $driver->id,
        'rest_stop_id' => $restStop->id,
        'used_at' => '2026-10-02 11:20:00',
        'rating' => 5,
        'report' => 'Updated report after admin follow-up.',
        'is_report' => true,
    ]);
});

it('marks a route stop as used with only a rating', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-06 12:34:56'));

    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $dispatcher = createDispatcherForRouteStopUsageReviewEndpoint();
    $driver = createDriverForRouteStopUsageReviewEndpoint($dispatcher, $user, 'LEADER-123');
    $route = createRouteForRouteStopUsageReviewEndpoint($dispatcher);
    $restStop = createRestStopForRouteStopUsageReviewEndpoint();
    $routeStop = createRouteStopForRouteStopUsageReviewEndpoint($route, $restStop);

    $route->drivers()->attach($driver->id, [
        'is_convoy_leader' => true,
    ]);

    Sanctum::actingAs($user);

    $this->postJson("/api/driver/route-stop/{$routeStop->id}/usage-review", [
        'rating' => 3,
    ])
        ->assertCreated()
        ->assertJsonPath('data.route_stop_usage.rating', 3)
        ->assertJsonPath('data.route_stop_usage.report', null)
        ->assertJsonPath('data.route_stop_usage.is_report', false);

    $this->assertDatabaseHas('route_stop_usages', [
        'route_stop_id' => $routeStop->id,
        'driver_id' => $driver->id,
        'rest_stop_id' => $restStop->id,
        'used_at' => '2026-10-06 12:34:56',
        'rating' => 3,
        'report' => null,
        'is_report' => false,
    ]);
});

it('rejects usage reviews from assigned drivers who are not convoy leaders', function () {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $dispatcher = createDispatcherForRouteStopUsageReviewEndpoint();
    $driver = createDriverForRouteStopUsageReviewEndpoint($dispatcher, $user, 'DRIVER-123');
    $route = createRouteForRouteStopUsageReviewEndpoint($dispatcher);
    $restStop = createRestStopForRouteStopUsageReviewEndpoint();
    $routeStop = createRouteStopForRouteStopUsageReviewEndpoint($route, $restStop);

    $route->drivers()->attach($driver->id, [
        'is_convoy_leader' => false,
    ]);

    Sanctum::actingAs($user);

    $this->postJson("/api/driver/route-stop/{$routeStop->id}/usage-review", [
        'rating' => 4,
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only the convoy leader assigned to this route can submit a route stop usage review.');

    expect(RouteStopUsage::query()->count())->toBe(0);
});

it('rejects route stop usage reviews when the route stop has no selected rest stop', function () {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $dispatcher = createDispatcherForRouteStopUsageReviewEndpoint();
    $driver = createDriverForRouteStopUsageReviewEndpoint($dispatcher, $user, 'LEADER-123');
    $route = createRouteForRouteStopUsageReviewEndpoint($dispatcher);
    $routeStop = createRouteStopForRouteStopUsageReviewEndpoint($route);

    $route->drivers()->attach($driver->id, [
        'is_convoy_leader' => true,
    ]);

    Sanctum::actingAs($user);

    $this->postJson("/api/driver/route-stop/{$routeStop->id}/usage-review", [
        'rating' => 4,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_stop_id'])
        ->assertJsonPath(
            'errors.route_stop_id.0',
            'Route stop must have a selected rest stop before it can be reviewed.'
        );

    expect(RouteStopUsage::query()->count())->toBe(0);
});

it('returns not found when submitting a review for a missing route stop', function () {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $dispatcher = createDispatcherForRouteStopUsageReviewEndpoint();
    createDriverForRouteStopUsageReviewEndpoint($dispatcher, $user, 'LEADER-123');

    Sanctum::actingAs($user);

    $this->postJson('/api/driver/route-stop/999/usage-review', [
        'rating' => 4,
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Route stop not found.');
});

it('returns not found when the authenticated driver profile does not exist', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->postJson('/api/driver/route-stop/1/usage-review', [
        'rating' => 4,
    ])
        ->assertNotFound()
        ->assertJsonPath('message', 'Driver profile not found.');
});

it('requires a driver user to submit route stop usage reviews', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->postJson('/api/driver/route-stop/1/usage-review', [
        'rating' => 10,
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Only driver users can submit route stop usage reviews.');
});

it('requires authentication to submit route stop usage reviews', function () {
    $this->postJson('/api/driver/route-stop/1/usage-review')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('validates route stop usage review payloads', function () {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);

    createDriverForRouteStopUsageReviewEndpoint(createDispatcherForRouteStopUsageReviewEndpoint(), $user, 'LEADER-123');

    Sanctum::actingAs($user);

    $this->postJson('/api/driver/route-stop/1/usage-review', [
        'rating' => 6,
        'report' => str_repeat('a', RouteStopUsage::MAX_REPORT_LENGTH + 1),
        'is_report' => 'yes-please',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rating', 'report', 'is_report']);
});

it('requires a rating for route stop usage reviews', function () {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);

    createDriverForRouteStopUsageReviewEndpoint(createDispatcherForRouteStopUsageReviewEndpoint(), $user, 'LEADER-123');

    Sanctum::actingAs($user);

    $this->postJson('/api/driver/route-stop/1/usage-review', [
        'report' => 'Clean and quick service.',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rating']);
});

it('requires report text when the review is flagged as a report', function () {
    $user = User::factory()->create([
        'profile_type' => 'driver',
    ]);

    createDriverForRouteStopUsageReviewEndpoint(createDispatcherForRouteStopUsageReviewEndpoint(), $user, 'LEADER-123');

    Sanctum::actingAs($user);

    $this->postJson('/api/driver/route-stop/1/usage-review', [
        'rating' => 4,
        'is_report' => true,
        'report' => '   ',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['report'])
        ->assertJsonPath('errors.report.0', 'A report description is required when reporting a rest stop.');
});

function createDispatcherForRouteStopUsageReviewEndpoint(): Dispatcher
{
    return Dispatcher::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'dispatcher',
        ])->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => fake()->unique()->bothify('REG-####'),
    ]);
}

function createDriverForRouteStopUsageReviewEndpoint(
    Dispatcher $dispatcher,
    User $user,
    string $licenseNumber
): Driver {
    return Driver::query()->create([
        'user_id' => $user->id,
        'dispatcher_id' => $dispatcher->id,
        'license_number' => $licenseNumber,
        'is_dispatcher_approved' => true,
    ]);
}

function createRouteForRouteStopUsageReviewEndpoint(Dispatcher $dispatcher): DispatcherRoute
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

function createRestStopForRouteStopUsageReviewEndpoint(): RestStop
{
    return RestStop::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'rest_stop',
        ])->id,
        'city' => 'Vienna',
        'address' => 'Ring Road 12',
        'post_code' => '1010',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}

function createRouteStopForRouteStopUsageReviewEndpoint(
    DispatcherRoute $route,
    ?RestStop $restStop = null
): RouteStop {
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'stop_at' => '2026-10-02 10:30:00',
        'fulfilled_at' => $restStop ? '2026-10-02 11:00:00' : null,
        'fulfilled_by' => $restStop?->id,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
}
