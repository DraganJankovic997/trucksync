<?php

use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a route stop with needed services without authentication', function () {
    $route = createRouteForShowRouteStopEndpoint();
    $fuel = Service::query()->create([
        'name' => 'Fuel',
        'measurement_unit' => 'liter',
    ]);
    $tireReplacement = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'piece',
    ]);
    $routeStop = RouteStop::query()->create([
        'route_id' => $route->id,
        'location' => 'Vienna fuel stop',
        'description' => 'Refuel and inspect tires before crossing into Germany.',
        'stop_at' => '2026-10-02 10:30:00',
        'number_of_trucks' => 3,
        'number_of_drivers' => 4,
    ]);

    $routeStop->services()->attach([
        $fuel->id => ['quantity' => 200],
        $tireReplacement->id => ['quantity' => 2],
    ]);
    $firstRestStop = createRestStopForShowRouteStopEndpointBid(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));
    $secondRestStop = createRestStopForShowRouteStopEndpointBid(User::factory()->create([
        'profile_type' => 'rest_stop',
    ]));

    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $firstRestStop->id,
        'original_price' => '300.00',
        'price' => '250.00',
    ]);
    RouteStopBid::query()->create([
        'route_stop_id' => $routeStop->id,
        'rest_stop_id' => $secondRestStop->id,
        'original_price' => '325.00',
        'price' => '275.00',
    ]);

    $this->getJson("/api/route-stop/{$routeStop->id}")
        ->assertOk()
        ->assertJsonPath('data.route_stop.id', $routeStop->id)
        ->assertJsonPath('data.route_stop.route_id', $route->id)
        ->assertJsonPath('data.route_stop.location', 'Vienna fuel stop')
        ->assertJsonPath('data.route_stop.description', 'Refuel and inspect tires before crossing into Germany.')
        ->assertJsonPath('data.route_stop.stop_at', $routeStop->stop_at->toJSON())
        ->assertJsonPath('data.route_stop.fulfiled_at', null)
        ->assertJsonPath('data.route_stop.fulfiled_by', null)
        ->assertJsonPath('data.route_stop.number_of_trucks', 3)
        ->assertJsonPath('data.route_stop.number_of_drivers', 4)
        ->assertJsonPath('data.route_stop.bids_count', 2)
        ->assertJsonCount(2, 'data.route_stop.services')
        ->assertJsonPath('data.route_stop.services.0.id', $fuel->id)
        ->assertJsonPath('data.route_stop.services.0.name', 'Fuel')
        ->assertJsonPath('data.route_stop.services.0.measurement_unit', 'liter')
        ->assertJsonPath('data.route_stop.services.0.quantity', 200)
        ->assertJsonPath('data.route_stop.services.1.id', $tireReplacement->id)
        ->assertJsonPath('data.route_stop.services.1.name', 'Tire replacement')
        ->assertJsonPath('data.route_stop.services.1.measurement_unit', 'piece')
        ->assertJsonPath('data.route_stop.services.1.quantity', 2);
});

it('returns not found when showing a missing route stop', function () {
    $this->getJson('/api/route-stop/999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Route stop not found.');
});

function createRouteForShowRouteStopEndpoint(): DispatcherRoute
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

function createRestStopForShowRouteStopEndpointBid(User $user): RestStop
{
    return RestStop::query()->create([
        'user_id' => $user->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
}
