<?php

use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\RouteStopService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores route stops for a route', function () {
    $route = createRouteForRouteStopTest();
    $restStop = createRestStopForRouteStopTest();

    $routeStop = RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'stop_at' => '2026-10-02 10:30:00',
        'fulfiled_at' => '2026-10-02 11:15:00',
        'fulfiled_by' => $restStop->id,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);

    expect(Schema::getColumnListing('route_stops'))->toEqualCanonicalizing([
        'id',
        'route_id',
        'stop_at',
        'fulfiled_at',
        'fulfiled_by',
        'number_of_trucks',
        'number_of_drivers',
        'created_at',
        'updated_at',
        'location',
        'description',
    ])
        ->and($route->routeStops()->first()->is($routeStop))->toBeTrue()
        ->and($routeStop->route->is($route))->toBeTrue()
        ->and($routeStop->fulfiledBy->is($restStop))->toBeTrue()
        ->and($routeStop->location)->toBe('Vienna fuel stop')
        ->and($routeStop->description)->toBe('Refuel and inspect tires before crossing into Germany.')
        ->and($routeStop->stop_at->toDateTimeString())->toBe('2026-10-02 10:30:00')
        ->and($routeStop->fulfiled_at->toDateTimeString())->toBe('2026-10-02 11:15:00')
        ->and($routeStop->fulfiled_by)->toBe($restStop->id)
        ->and($routeStop->number_of_trucks)->toBe(3)
        ->and($routeStop->number_of_drivers)->toBe(4);

    $this->assertDatabaseHas('route_stops', [
        'id' => $routeStop->id,
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'stop_at' => '2026-10-02 10:30:00',
        'fulfiled_at' => '2026-10-02 11:15:00',
        'fulfiled_by' => $restStop->id,
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
});

it('stores needed services with quantity for a route stop', function () {
    $route = createRouteForRouteStopTest();
    $routeStop = RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => null,
        'stop_at' => '2026-10-02 10:30:00',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    $routeStopService = RouteStopService::query()->create([
        'route_stop_id' => $routeStop->id,
        'service_id' => $service->id,
        'quantity' => 2,
    ]);

    expect(Schema::getColumnListing('route_stop_services'))->toBe([
        'route_stop_id',
        'service_id',
        'quantity',
    ])
        ->and($routeStopService->routeStop->is($routeStop))->toBeTrue()
        ->and($routeStopService->service->is($service))->toBeTrue()
        ->and($routeStop->routeStopServices()->first()->is($routeStopService))->toBeTrue()
        ->and($service->routeStopServices()->first()->is($routeStopService))->toBeTrue()
        ->and($routeStop->services()->first()->is($service))->toBeTrue()
        ->and($service->routeStops()->first()->is($routeStop))->toBeTrue()
        ->and($routeStopService->quantity)->toBe(2);

    $this->assertDatabaseHas('route_stop_services', [
        'route_stop_id' => $routeStop->id,
        'service_id' => $service->id,
        'quantity' => 2,
    ]);
});

it('stores rest stop bids for a route stop', function () {
    $route = createRouteForRouteStopTest();
    $routeStop = RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => null,
        'stop_at' => '2026-10-02 10:30:00',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);
    $restStop = createRestStopForRouteStopTest();

    $routeStopBid = RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'price' => '250.75',
        'original_price' => '300.00',
    ]);

    expect(Schema::getColumnListing('route_stop_bids'))->toBe([
        'route_stop_id',
        'rest_stop_id',
        'price',
        'original_price',
    ])
        ->and($routeStopBid->routeStop->is($routeStop))->toBeTrue()
        ->and($routeStopBid->restStop->is($restStop))->toBeTrue()
        ->and($routeStop->routeStopBids()->first()->is($routeStopBid))->toBeTrue()
        ->and($restStop->routeStopBids()->first()->is($routeStopBid))->toBeTrue()
        ->and($routeStopBid->price)->toBe('250.75')
        ->and($routeStopBid->original_price)->toBe('300.00');

    $this->assertDatabaseHas('route_stop_bids', [
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $restStop->id,
        'price' => '250.75',
        'original_price' => '300.00',
    ]);
});

function createRouteForRouteStopTest(): DispatcherRoute
{
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

    return DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => null,
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ]);
}

function createRestStopForRouteStopTest(): RestStop
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
