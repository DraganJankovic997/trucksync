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
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('lists route stop usage ratings for an admin user ordered by usage date descending', function () {
    $admin = createAdminUserForRouteStopUsageRatings();
    $firstRestStop = createRestStopForRouteStopUsageRatings();
    $secondRestStop = createRestStopForRouteStopUsageRatings();
    $oldUsage = createRouteStopUsageForAdminRatings($firstRestStop, '2026-10-01 08:00:00', 3);
    $middleUsage = createRouteStopUsageForAdminRatings($secondRestStop, '2026-10-02 08:00:00', 4);
    $newUsage = createRouteStopUsageForAdminRatings($firstRestStop, '2026-10-03 08:00:00', 5, true);

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/ratings')
        ->assertOk()
        ->assertJsonCount(3, 'data.route_stop_usages')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.from', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.to', 3)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('links.prev', null)
        ->assertJsonPath('links.next', null)
        ->assertJsonPath('data.route_stop_usages.0.id', $newUsage->id)
        ->assertJsonPath('data.route_stop_usages.0.rest_stop_id', $firstRestStop->id)
        ->assertJsonPath('data.route_stop_usages.0.used_at', Carbon::parse('2026-10-03 08:00:00')->toJSON())
        ->assertJsonPath('data.route_stop_usages.0.rating', 5)
        ->assertJsonPath('data.route_stop_usages.0.is_report', true)
        ->assertJsonPath('data.route_stop_usages.1.id', $middleUsage->id)
        ->assertJsonPath('data.route_stop_usages.1.rest_stop_id', $secondRestStop->id)
        ->assertJsonPath('data.route_stop_usages.2.id', $oldUsage->id);
});

it('filters route stop usage ratings by rest stop id for an admin user', function () {
    $admin = createAdminUserForRouteStopUsageRatings();
    $restStop = createRestStopForRouteStopUsageRatings();
    $otherRestStop = createRestStopForRouteStopUsageRatings();
    $oldUsage = createRouteStopUsageForAdminRatings($restStop, '2026-10-01 08:00:00', 3);
    $newUsage = createRouteStopUsageForAdminRatings($restStop, '2026-10-03 08:00:00', 5);
    $otherUsage = createRouteStopUsageForAdminRatings($otherRestStop, '2026-10-04 08:00:00', 1);

    Sanctum::actingAs($admin);

    $this->getJson("/api/admin/ratings?rest_stop_id={$restStop->id}")
        ->assertOk()
        ->assertJsonCount(2, 'data.route_stop_usages')
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.route_stop_usages.0.id', $newUsage->id)
        ->assertJsonPath('data.route_stop_usages.0.rest_stop_id', $restStop->id)
        ->assertJsonPath('data.route_stop_usages.1.id', $oldUsage->id)
        ->assertJsonMissing([
            'id' => $otherUsage->id,
        ]);
});

it('filters route stop usage ratings to reports when requested by an admin user', function () {
    $admin = createAdminUserForRouteStopUsageRatings();
    $restStop = createRestStopForRouteStopUsageRatings();
    $otherRestStop = createRestStopForRouteStopUsageRatings();
    $oldReport = createRouteStopUsageForAdminRatings($restStop, '2026-10-01 08:00:00', 2, true);
    $review = createRouteStopUsageForAdminRatings($restStop, '2026-10-02 08:00:00', 5);
    $newReport = createRouteStopUsageForAdminRatings($otherRestStop, '2026-10-03 08:00:00', 1, true);

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/ratings?is_report=true')
        ->assertOk()
        ->assertJsonCount(2, 'data.route_stop_usages')
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.route_stop_usages.0.id', $newReport->id)
        ->assertJsonPath('data.route_stop_usages.0.is_report', true)
        ->assertJsonPath('data.route_stop_usages.1.id', $oldReport->id)
        ->assertJsonPath('data.route_stop_usages.1.is_report', true)
        ->assertJsonMissing([
            'id' => $review->id,
        ]);
});

it('returns all route stop usage ratings when the report filter is false', function () {
    $admin = createAdminUserForRouteStopUsageRatings();
    $restStop = createRestStopForRouteStopUsageRatings();
    $report = createRouteStopUsageForAdminRatings($restStop, '2026-10-01 08:00:00', 2, true);
    $review = createRouteStopUsageForAdminRatings($restStop, '2026-10-02 08:00:00', 5);

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/ratings?is_report=false')
        ->assertOk()
        ->assertJsonCount(2, 'data.route_stop_usages')
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.route_stop_usages.0.id', $review->id)
        ->assertJsonPath('data.route_stop_usages.1.id', $report->id);
});

it('paginates route stop usage ratings while preserving rest stop filters', function () {
    $admin = createAdminUserForRouteStopUsageRatings();
    $restStop = createRestStopForRouteStopUsageRatings();
    $olderUsage = createRouteStopUsageForAdminRatings($restStop, '2026-10-01 08:00:00', 3);
    $newerUsage = createRouteStopUsageForAdminRatings($restStop, '2026-10-02 08:00:00', 4);

    Sanctum::actingAs($admin);

    $response = $this->getJson("/api/admin/ratings?rest_stop_id={$restStop->id}&per_page=1&page=1")
        ->assertOk()
        ->assertJsonCount(1, 'data.route_stop_usages')
        ->assertJsonPath('data.route_stop_usages.0.id', $newerUsage->id)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.from', 1)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.to', 1)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('links.prev', null);

    expect($response->json('links.next'))->toContain("rest_stop_id={$restStop->id}");

    $this->getJson("/api/admin/ratings?rest_stop_id={$restStop->id}&per_page=1&page=2")
        ->assertOk()
        ->assertJsonCount(1, 'data.route_stop_usages')
        ->assertJsonPath('data.route_stop_usages.0.id', $olderUsage->id)
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.from', 2)
        ->assertJsonPath('meta.to', 2)
        ->assertJsonPath('links.next', null);
});

it('paginates route stop usage report filters while preserving query parameters', function () {
    $admin = createAdminUserForRouteStopUsageRatings();
    $restStop = createRestStopForRouteStopUsageRatings();
    $olderUsage = createRouteStopUsageForAdminRatings($restStop, '2026-10-01 08:00:00', 3, true);
    $newerUsage = createRouteStopUsageForAdminRatings($restStop, '2026-10-02 08:00:00', 4, true);
    createRouteStopUsageForAdminRatings($restStop, '2026-10-03 08:00:00', 5);

    Sanctum::actingAs($admin);

    $response = $this->getJson("/api/admin/ratings?rest_stop_id={$restStop->id}&is_report=true&per_page=1&page=1")
        ->assertOk()
        ->assertJsonCount(1, 'data.route_stop_usages')
        ->assertJsonPath('data.route_stop_usages.0.id', $newerUsage->id)
        ->assertJsonPath('meta.total', 2);

    expect($response->json('links.next'))
        ->toContain("rest_stop_id={$restStop->id}")
        ->toContain('is_report=true');

    $this->getJson("/api/admin/ratings?rest_stop_id={$restStop->id}&is_report=true&per_page=1&page=2")
        ->assertOk()
        ->assertJsonCount(1, 'data.route_stop_usages')
        ->assertJsonPath('data.route_stop_usages.0.id', $olderUsage->id)
        ->assertJsonPath('meta.total', 2);
});

it('validates admin rating query parameters', function () {
    $admin = createAdminUserForRouteStopUsageRatings();

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/ratings?rest_stop_id=999&is_report=maybe&page=0&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rest_stop_id', 'is_report', 'page', 'per_page']);
});

it('forbids non-admin users from listing route stop usage ratings', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/admin/ratings')
        ->assertForbidden()
        ->assertJsonPath('message', 'User does not have the right roles.');
});

it('requires authentication to list route stop usage ratings', function () {
    $this->getJson('/api/admin/ratings')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

function createAdminUserForRouteStopUsageRatings(): User
{
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin'));

    return $admin;
}

function createRestStopForRouteStopUsageRatings(): RestStop
{
    return RestStop::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'rest_stop',
        ])->id,
        'city' => fake()->city(),
        'address' => fake()->streetAddress(),
        'post_code' => fake()->postcode(),
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}

function createRouteStopUsageForAdminRatings(
    RestStop $restStop,
    string $usedAt,
    int $rating,
    bool $isReport = false
): RouteStopUsage {
    $dispatcher = Dispatcher::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'dispatcher',
        ])->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => fake()->unique()->bothify('REG-####'),
    ]);
    $route = DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => null,
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ]);
    $routeStop = RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => null,
        'stop_at' => '2026-10-02 10:30:00',
        'fulfilled_at' => '2026-10-02 11:00:00',
        'fulfilled_by' => $restStop->id,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
    $driver = Driver::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'driver',
        ])->id,
        'dispatcher_id' => $dispatcher->id,
        'license_number' => fake()->unique()->bothify('DRV-####'),
        'is_dispatcher_approved' => true,
    ]);

    return RouteStopUsage::query()->create([
        'route_stop_id' => $routeStop->id,
        'driver_id' => $driver->id,
        'rest_stop_id' => $restStop->id,
        'used_at' => $usedAt,
        'rating' => $rating,
        'report' => $isReport ? 'Fuel was available, but showers were missing.' : null,
        'is_report' => $isReport,
    ]);
}
