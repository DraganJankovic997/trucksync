<?php

use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists only routes assigned to the authenticated driver with route stops and services', function () {
    $dispatcher = createDispatcherForListDriverRoutes(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $driverUser = User::factory()->create([
        'profile_type' => 'driver',
    ]);
    $driver = createDriverForListDriverRoutes($dispatcher, $driverUser, 'DRIVER-123');
    $otherDriver = createDriverForListDriverRoutes($dispatcher, null, 'OTHER-456');
    $assignedRoute = createRouteForListDriverRoutes(
        $dispatcher,
        'Belgrade warehouse',
        '2026-09-13',
        '2026-09-16'
    );
    $futureAssignedRoute = createRouteForListDriverRoutes(
        $dispatcher,
        'Vienna warehouse',
        '2026-10-01',
        '2026-10-05'
    );
    $unassignedRoute = createRouteForListDriverRoutes(
        $dispatcher,
        'Unassigned route',
        '2026-09-14',
        '2026-09-17'
    );
    $otherDriverRoute = createRouteForListDriverRoutes(
        $dispatcher,
        'Other driver route',
        '2026-09-15',
        '2026-09-18'
    );
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $inspection = Service::query()->create([
        'name' => 'Inspection',
        'measurement_unit' => null,
    ]);
    $laterStop = createRouteStopForListDriverRoutes(
        $assignedRoute,
        'Munich overnight stop',
        'Rest before the final leg.',
        '2026-09-14 21:00:00',
        2,
        3
    );
    $earlierStop = createRouteStopForListDriverRoutes(
        $assignedRoute,
        'Vienna fuel stop',
        'Refuel before crossing into Germany.',
        '2026-09-14 10:30:00',
        3,
        4
    );

    $earlierStop->services()->attach([
        $fuel->id => ['quantity' => 200],
        $inspection->id => ['quantity' => 1],
    ]);
    $laterStop->services()->attach([
        $fuel->id => ['quantity' => 120],
    ]);
    $assignedRoute->drivers()->attach([
        $driver->id => ['is_convoy_leader' => true],
        $otherDriver->id => ['is_convoy_leader' => false],
    ]);
    $futureAssignedRoute->drivers()->attach($driver->id, [
        'is_convoy_leader' => false,
    ]);
    $otherDriverRoute->drivers()->attach($otherDriver->id, [
        'is_convoy_leader' => true,
    ]);

    Sanctum::actingAs($driverUser);

    $response = $this->getJson('/api/driver/routes')
        ->assertOk()
        ->assertJsonCount(2, 'data.routes')
        ->assertJsonPath('data.routes.0.id', $assignedRoute->id)
        ->assertJsonPath('data.routes.0.origin', 'Belgrade warehouse')
        ->assertJsonPath('data.routes.0.destination', 'Berlin logistics hub')
        ->assertJsonPath('data.routes.0.route_stops.0.id', $earlierStop->id)
        ->assertJsonPath('data.routes.0.route_stops.0.location', 'Vienna fuel stop')
        ->assertJsonPath('data.routes.0.route_stops.0.description', 'Refuel before crossing into Germany.')
        ->assertJsonPath('data.routes.0.route_stops.0.stop_at', $earlierStop->stop_at->toJSON())
        ->assertJsonPath('data.routes.0.route_stops.0.number_of_trucks', 3)
        ->assertJsonPath('data.routes.0.route_stops.0.number_of_drivers', 4)
        ->assertJsonPath('data.routes.0.route_stops.0.bids_count', 0)
        ->assertJsonPath('data.routes.0.route_stops.0.services.0.id', $fuel->id)
        ->assertJsonPath('data.routes.0.route_stops.0.services.0.quantity', 200)
        ->assertJsonPath('data.routes.0.route_stops.0.services.1.id', $inspection->id)
        ->assertJsonPath('data.routes.0.route_stops.0.services.1.quantity', 1)
        ->assertJsonPath('data.routes.0.route_stops.1.id', $laterStop->id)
        ->assertJsonPath('data.routes.0.drivers.0.id', $driver->id)
        ->assertJsonPath('data.routes.0.drivers.0.is_convoy_leader', true)
        ->assertJsonPath('data.routes.0.drivers.1.id', $otherDriver->id)
        ->assertJsonPath('data.routes.0.drivers.1.is_convoy_leader', false)
        ->assertJsonPath('data.routes.1.id', $futureAssignedRoute->id);

    $routeIds = collect($response->json('data.routes'))->pluck('id')->all();

    expect($routeIds)
        ->not->toContain($unassignedRoute->id)
        ->not->toContain($otherDriverRoute->id);
});

it('returns an empty list when the authenticated driver has no assigned routes', function () {
    $dispatcher = createDispatcherForListDriverRoutes(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));
    $driverUser = User::factory()->create([
        'profile_type' => 'driver',
    ]);

    createDriverForListDriverRoutes($dispatcher, $driverUser, 'DRIVER-123');
    createRouteForListDriverRoutes(
        $dispatcher,
        'Unassigned route',
        '2026-09-14',
        '2026-09-17'
    );

    Sanctum::actingAs($driverUser);

    $this->getJson('/api/driver/routes')
        ->assertOk()
        ->assertJsonPath('data.routes', []);
});

it('returns not found when the authenticated driver profile does not exist', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'driver',
    ]));

    $this->getJson('/api/driver/routes')
        ->assertNotFound()
        ->assertJsonPath('message', 'Driver profile not found.');
});

it('requires a driver user to list assigned routes', function () {
    Sanctum::actingAs(User::factory()->create([
        'profile_type' => 'dispatcher',
    ]));

    $this->getJson('/api/driver/routes')
        ->assertForbidden()
        ->assertJsonPath('message', 'Only driver users can view their routes.');
});

it('requires authentication to list assigned driver routes', function () {
    $this->getJson('/api/driver/routes')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

function createDispatcherForListDriverRoutes(User $user): Dispatcher
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

function createDriverForListDriverRoutes(
    Dispatcher $dispatcher,
    ?User $user,
    string $licenseNumber
): Driver {
    $driverUser = $user ?? User::factory()->create([
        'profile_type' => 'driver',
    ]);

    return Driver::query()->create([
        'user_id' => $driverUser->id,
        'dispatcher_id' => $dispatcher->id,
        'license_number' => $licenseNumber,
    ]);
}

function createRouteForListDriverRoutes(
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

function createRouteStopForListDriverRoutes(
    DispatcherRoute $route,
    string $location,
    ?string $description,
    string $stopAt,
    int $numberOfTrucks,
    int $numberOfDrivers
): RouteStop {
    return RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => $location,
        'description' => $description,
        'stop_at' => $stopAt,
        'number_of_trucks' => $numberOfTrucks,
        'number_of_drivers' => $numberOfDrivers,
    ]);
}
