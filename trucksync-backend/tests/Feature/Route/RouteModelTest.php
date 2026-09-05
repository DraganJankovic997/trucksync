<?php

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores routes for a dispatcher', function () {
    $dispatcher = Dispatcher::query()->create([
        'user_id' => User::factory()->create([
            'profile_type' => 'dispatcher',
        ])->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'REG-1234',
    ]);

    $route = DispatcherRoute::query()->create([
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ]);

    expect(Schema::getColumnListing('routes'))->toBe([
        'id',
        'dispatcher_id',
        'origin',
        'destination',
        'planned_travel_details',
        'convoy_size',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ])
        ->and($dispatcher->routes()->first()->is($route))->toBeTrue()
        ->and($route->dispatcher->is($dispatcher))->toBeTrue()
        ->and($route->start_date->toDateString())->toBe('2026-10-01')
        ->and($route->end_date->toDateString())->toBe('2026-10-05');

    $this->assertDatabaseHas('routes', [
        'id' => $route->id,
        'dispatcher_id' => $dispatcher->id,
        'origin' => 'Belgrade warehouse',
        'destination' => 'Berlin logistics hub',
        'planned_travel_details' => 'Take the A3 corridor and stop near Vienna.',
        'convoy_size' => 3,
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-05',
    ]);
});
