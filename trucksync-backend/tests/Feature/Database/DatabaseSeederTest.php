<?php

use App\Models\Country;
use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('seeds demo data for local testing', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-13 12:00:00'));

    try {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $testDispatcher = Dispatcher::query()
            ->whereRelation('user', 'email', 'dispatcher@trucksync.com')
            ->firstOrFail();
        $testRestStop = RestStop::query()
            ->whereRelation('user', 'email', 'reststop@trucksync.com')
            ->firstOrFail();
        $testDriver = Driver::query()
            ->whereRelation('user', 'email', 'driver@trucksync.com')
            ->firstOrFail();

        expect(Country::query()->count())->toBeGreaterThan(20)
            ->and(Country::query()->where('code', 'RS')->where('name', 'Serbia')->exists())->toBeTrue()
            ->and(User::query()->count())->toBe(34)
            ->and(Dispatcher::query()->count())->toBe(11)
            ->and(Driver::query()->count())->toBe(11)
            ->and(RestStop::query()->count())->toBe(11)
            ->and($testDispatcher->is_approved)->toBeTrue()
            ->and($testRestStop->is_approved)->toBeTrue()
            ->and($testDriver->dispatcher_id)->toBe($testDispatcher->id)
            ->and($testDriver->is_dispatcher_approved)->toBeTrue()
            ->and(Dispatcher::query()->where('is_approved', false)->count())->toBe(2)
            ->and(RestStop::query()->where('is_approved', false)->count())->toBe(2);

        assertSeededServices();
        assertRestStopServiceOfferings();
        assertSeededRoutes($testDispatcher);
        assertSeededDriverAssignments($testDispatcher);
        assertSeededBids();
    } finally {
        Carbon::setTestNow();
    }
});

function assertSeededServices(): void
{
    $expectedUnits = [
        'Bed' => 'driver',
        'Breakfast' => 'driver',
        'Dinner' => 'driver',
        'Parking' => 'truck',
        'Oil change' => 'truck',
        'Fuel refill' => 'truck',
    ];

    expect(Service::query()->whereIn('name', array_keys($expectedUnits))->count())->toBe(6);

    foreach ($expectedUnits as $name => $measurementUnit) {
        expect(Service::query()->where('name', $name)->value('measurement_unit'))->toBe($measurementUnit);
    }
}

function assertRestStopServiceOfferings(): void
{
    foreach (RestStop::query()->orderBy('id')->get() as $restStop) {
        $serviceNames = $restStop->services()
            ->pluck('name')
            ->all();

        expect($serviceNames)
            ->toContain('Bed')
            ->toContain('Breakfast')
            ->toContain('Dinner')
            ->toContain('Parking');
    }

    expect(RestStop::query()->whereHas(
        'services',
        fn ($query) => $query->where('name', 'Oil change')
    )->count())->toBe(5)
        ->and(RestStop::query()->whereHas(
            'services',
            fn ($query) => $query->where('name', 'Fuel refill')
        )->count())->toBe(5)
        ->and(DB::table('rest_stop_services')->count())->toBe(54);
}

function assertSeededRoutes(Dispatcher $testDispatcher): void
{
    $nextMonthStart = Carbon::parse('2026-10-01')->startOfDay();
    $nextMonthEnd = Carbon::parse('2026-10-31')->endOfDay();
    $routes = $testDispatcher->routes()
        ->with('routeStops.routeStopServices')
        ->orderBy('start_date')
        ->get();

    expect($routes)->toHaveCount(10)
        ->and(DispatcherRoute::query()->count())->toBe(10)
        ->and(RouteStop::query()->count())->toBe(30);

    foreach ($routes as $route) {
        $durationInDays = (int) $route->start_date->diffInDays($route->end_date) + 1;
        $overnightStopCount = $route->routeStops
            ->filter(fn (RouteStop $routeStop): bool => in_array((int) $routeStop->stop_at->format('H'), [20, 21], true))
            ->count();

        expect($route->routeStops)->toHaveCount(3)
            ->and($durationInDays)->toBeGreaterThanOrEqual(5)
            ->and($durationInDays)->toBeLessThanOrEqual(10)
            ->and($route->start_date->greaterThanOrEqualTo($nextMonthStart))->toBeTrue()
            ->and($route->end_date->lessThanOrEqualTo($nextMonthEnd))->toBeTrue()
            ->and($overnightStopCount)->toBeGreaterThanOrEqual(2);

        foreach ($route->routeStops as $routeStop) {
            expect($routeStop->routeStopServices)->not->toBeEmpty()
                ->and($routeStop->stop_at->greaterThanOrEqualTo($route->start_date->copy()->startOfDay()))->toBeTrue()
                ->and($routeStop->stop_at->lessThanOrEqualTo($route->end_date->copy()->endOfDay()))->toBeTrue();
        }
    }
}

function assertSeededDriverAssignments(Dispatcher $testDispatcher): void
{
    $drivers = Driver::query()
        ->where('dispatcher_id', $testDispatcher->id)
        ->orderBy('id')
        ->get();
    $leaderCounts = DB::table('driver_route')
        ->select('driver_id', DB::raw('COUNT(*) as leader_count'))
        ->where('is_convoy_leader', true)
        ->groupBy('driver_id')
        ->pluck('leader_count', 'driver_id');

    expect($drivers)->toHaveCount(7)
        ->and($drivers->every(fn (Driver $driver): bool => $driver->is_dispatcher_approved))->toBeTrue()
        ->and(DB::table('driver_route')->count())->toBe(30)
        ->and(DB::table('driver_route')->where('is_convoy_leader', true)->count())->toBe(10)
        ->and($leaderCounts)->toHaveCount(4);

    foreach ($leaderCounts as $leaderCount) {
        expect((int) $leaderCount)->toBeGreaterThanOrEqual(2);
    }

    $leaderDriverIds = $leaderCounts->keys()
        ->map(fn (int|string $driverId): int => (int) $driverId);
    $supportOnlyDriverIds = $drivers->pluck('id')->diff($leaderDriverIds);

    expect($supportOnlyDriverIds)->toHaveCount(3);

    foreach ($drivers as $driver) {
        $routes = $driver->routes()
            ->orderBy('start_date')
            ->get();
        $previousEndDate = null;

        foreach ($routes as $route) {
            if ($previousEndDate !== null) {
                expect($route->start_date->greaterThan($previousEndDate))->toBeTrue();
            }

            $previousEndDate = $route->end_date;
        }
    }
}

function assertSeededBids(): void
{
    expect(DispatcherRoute::query()->whereHas('routeStops.routeStopBids')->count())->toBe(4)
        ->and(RouteStopBid::query()->count())->toBe(36)
        ->and(RouteStopBid::query()->where('status', RouteStopBid::STATUS_PENDING)->count())->toBe(36);
}
