<?php

use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-03 12:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('shows the current driver route with all route stops and the next stop by date', function () {
    $dispatcher = createDispatcherForDriverDashboardRoutes(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $driverUser = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $driver = createDriverForDriverDashboardRoutes($dispatcher, $driverUser, 'CURRENT-123');
    $currentRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Belgrade warehouse',
        '2026-10-01',
        '2026-10-05'
    );
    $currentRoute->forceFill([
        'closed_at' => '2026-09-30 12:00:00',
    ])->save();
    $overlappingRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Overlapping route',
        '2026-10-02',
        '2026-10-04'
    );
    $unassignedRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Unassigned route',
        '2026-10-01',
        '2026-10-05'
    );
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $pastStop = createRouteStopForDriverDashboardRoutes(
        $currentRoute,
        'Morning stop',
        '2026-10-03 09:00:00'
    );
    $nextStop = createRouteStopForDriverDashboardRoutes(
        $currentRoute,
        'Afternoon stop',
        '2026-10-03 13:00:00',
        '2026-09-30 12:00:00'
    );
    $laterStop = createRouteStopForDriverDashboardRoutes(
        $currentRoute,
        'Next day stop',
        '2026-10-04 10:00:00'
    );
    $nextStop->services()->attach($fuel->id, [
        'quantity' => 200,
    ]);

    $currentRoute->drivers()->attach($driver->id, [
        'is_convoy_leader' => true,
    ]);
    $overlappingRoute->drivers()->attach($driver->id, [
        'is_convoy_leader' => false,
    ]);

    Sanctum::actingAs($driverUser);

    $response = $this->getJson('/api/driver/current-route')
        ->assertOk()
        ->assertJsonPath('data.route.id', $currentRoute->id)
        ->assertJsonPath('data.route.origin', 'Belgrade warehouse')
        ->assertJsonPath('data.route.closed_at', $currentRoute->closed_at->toJSON())
        ->assertJsonCount(3, 'data.route.route_stops')
        ->assertJsonPath('data.route.route_stops.0.id', $pastStop->id)
        ->assertJsonPath('data.route.route_stops.1.id', $nextStop->id)
        ->assertJsonPath('data.route.route_stops.2.id', $laterStop->id)
        ->assertJsonPath('data.next_route_stop.id', $nextStop->id)
        ->assertJsonPath('data.next_route_stop.fulfilled_at', $nextStop->fulfilled_at->toJSON())
        ->assertJsonPath('data.next_route_stop.services.0.id', $fuel->id)
        ->assertJsonPath('data.next_route_stop.services.0.quantity', 200);

    expect(collect($response->json('data.route.route_stops'))->pluck('route_id')->unique()->all())
        ->toBe([$currentRoute->id])
        ->and($response->json('data.route.id'))->not->toBe($overlappingRoute->id)
        ->and($response->json('data.route.id'))->not->toBe($unassignedRoute->id);
});

it('returns null current route data when the driver has no route active today', function () {
    $dispatcher = createDispatcherForDriverDashboardRoutes(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $driverUser = User::factory()->create([
        'profile_type' => 'driver',
    ]);

    createDriverForDriverDashboardRoutes($dispatcher, $driverUser, 'NO-CURRENT-123');

    Sanctum::actingAs($driverUser);

    $this->getJson('/api/driver/current-route')
        ->assertOk()
        ->assertJsonPath('data.route', null)
        ->assertJsonPath('data.next_route_stop', null);
});

it('returns null next stop when all current route stops are in the past', function () {
    $dispatcher = createDispatcherForDriverDashboardRoutes(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $driverUser = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $driver = createDriverForDriverDashboardRoutes($dispatcher, $driverUser, 'PAST-STOPS-123');
    $currentRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Belgrade warehouse',
        '2026-10-01',
        '2026-10-05'
    );
    createRouteStopForDriverDashboardRoutes(
        $currentRoute,
        'Morning stop',
        '2026-10-03 09:00:00'
    );
    $currentRoute->drivers()->attach($driver->id, [
        'is_convoy_leader' => false,
    ]);

    Sanctum::actingAs($driverUser);

    $this->getJson('/api/driver/current-route')
        ->assertOk()
        ->assertJsonPath('data.route.id', $currentRoute->id)
        ->assertJsonPath('data.next_route_stop', null);
});

it('lists the next three future routes for the authenticated driver', function () {
    $dispatcher = createDispatcherForDriverDashboardRoutes(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $driverUser = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $driver = createDriverForDriverDashboardRoutes($dispatcher, $driverUser, 'UPCOMING-123');
    $currentRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Current route',
        '2026-10-01',
        '2026-10-05'
    );
    $firstUpcomingRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'First upcoming route',
        '2026-10-06',
        '2026-10-08'
    );
    $firstUpcomingRoute->forceFill([
        'closed_at' => '2026-09-30 12:00:00',
    ])->save();
    $secondUpcomingRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Second upcoming route',
        '2026-10-08',
        '2026-10-10'
    );
    $thirdUpcomingRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Third upcoming route',
        '2026-10-08',
        '2026-10-11'
    );
    $fourthUpcomingRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Fourth upcoming route',
        '2026-10-12',
        '2026-10-13'
    );
    $unassignedUpcomingRoute = createRouteForDriverDashboardRoutes(
        $dispatcher,
        'Unassigned upcoming route',
        '2026-10-04',
        '2026-10-06'
    );

    foreach ([
        $currentRoute,
        $firstUpcomingRoute,
        $secondUpcomingRoute,
        $thirdUpcomingRoute,
        $fourthUpcomingRoute,
    ] as $route) {
        $route->drivers()->attach($driver->id, [
            'is_convoy_leader' => false,
        ]);
    }

    Sanctum::actingAs($driverUser);

    $response = $this->getJson('/api/driver/routes/upcoming')
        ->assertOk()
        ->assertJsonCount(3, 'data.routes')
        ->assertJsonPath('data.routes.0.id', $firstUpcomingRoute->id)
        ->assertJsonPath('data.routes.0.origin', 'First upcoming route')
        ->assertJsonPath('data.routes.0.start_date', '2026-10-06')
        ->assertJsonPath('data.routes.1.id', $secondUpcomingRoute->id)
        ->assertJsonPath('data.routes.2.id', $thirdUpcomingRoute->id);

    expect(collect($response->json('data.routes'))->pluck('id')->all())
        ->not->toContain($currentRoute->id)
        ->not->toContain($fourthUpcomingRoute->id)
        ->not->toContain($unassignedUpcomingRoute->id);
});

it('limits upcoming driver routes with a validated query parameter', function () {
    $dispatcher = createDispatcherForDriverDashboardRoutes(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $driverUser = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $driver = createDriverForDriverDashboardRoutes($dispatcher, $driverUser, 'LIMIT-123');

    collect([
        ['First upcoming route', '2026-10-06', '2026-10-08'],
        ['Second upcoming route', '2026-10-08', '2026-10-10'],
        ['Third upcoming route', '2026-10-12', '2026-10-13'],
    ])->each(function (array $routeData) use ($dispatcher, $driver) {
        $route = createRouteForDriverDashboardRoutes(
            $dispatcher,
            $routeData[0],
            $routeData[1],
            $routeData[2]
        );

        $route->drivers()->attach($driver->id, [
            'is_convoy_leader' => false,
        ]);
    });

    Sanctum::actingAs($driverUser);

    $this->getJson('/api/driver/routes/upcoming?limit=2')
        ->assertOk()
        ->assertJsonCount(2, 'data.routes');

    $this->getJson('/api/driver/routes/upcoming?limit=11')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['limit']);
});

it('returns not found for driver dashboard routes when the driver profile does not exist', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/driver/current-route')
        ->assertNotFound()
        ->assertJsonPath('message', 'Driver profile not found.');

    $this->getJson('/api/driver/routes/upcoming')
        ->assertNotFound()
        ->assertJsonPath('message', 'Driver profile not found.');
});

it('requires a driver user for dashboard route endpoints', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->getJson('/api/driver/current-route')
        ->assertForbidden()
        ->assertJsonPath('message', 'Only driver users can view their routes.');

    $this->getJson('/api/driver/routes/upcoming')
        ->assertForbidden()
        ->assertJsonPath('message', 'Only driver users can view their routes.');
});

it('requires authentication for dashboard route endpoints', function () {
    $this->getJson('/api/driver/current-route')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');

    $this->getJson('/api/driver/routes/upcoming')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

function createDispatcherForDriverDashboardRoutes(User $user): Dispatcher
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

function createDriverForDriverDashboardRoutes(
    Dispatcher $dispatcher,
    User $user,
    string $licenseNumber
): Driver {
    return Driver::query()->create([
        'user_id' => $user->id,
        'dispatcher_id' => $dispatcher->id,
        'license_number' => $licenseNumber,
    ]);
}

function createRouteForDriverDashboardRoutes(
    Dispatcher $dispatcher,
    string $origin,
    string $startDate,
    string $endDate
): DispatcherRoute {
    return DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => $origin,
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);
}

function createRouteStopForDriverDashboardRoutes(
    DispatcherRoute $route,
    string $location,
    string $stopAt,
    ?string $fulfilledAt = null
): RouteStop {
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => $location,
        'description' => 'Driver dashboard stop.',
        'stop_at' => $stopAt,
        'fulfilled_at' => $fulfilledAt,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
}
