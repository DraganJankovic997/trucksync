<?php

namespace Database\Seeders;

use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\RestStop;
use App\Models\RestStopService as RestStopServiceModel;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoRouteSeeder extends Seeder
{
    private const TEST_DISPATCHER_EMAIL = 'dispatcher@trucksync.com';

    private const BIDDED_ROUTE_COUNT = 4;

    private const CONVOY_SIZE = 3;

    private const TEST_DRIVER_EMAILS = [
        'driver@trucksync.com',
        'driver1@trucksync.com',
        'driver2@trucksync.com',
        'driver3@trucksync.com',
        'driver4@trucksync.com',
        'driver5@trucksync.com',
        'driver6@trucksync.com',
    ];

    private const ROUTES = [
        ['Belgrade Logistics Yard', 'Vienna Distribution Hub'],
        ['Novi Sad Freight Terminal', 'Budapest Cross Dock'],
        ['Nis South Depot', 'Sofia Logistics Park'],
        ['Zagreb Cargo Gate', 'Munich Trade Terminal'],
        ['Ljubljana Transit Yard', 'Prague North Warehouse'],
        ['Belgrade River Port', 'Thessaloniki Freight Hub'],
        ['Skopje Dry Port', 'Tirana Distribution Yard'],
        ['Sarajevo Cargo Center', 'Graz Logistics Campus'],
        ['Timisoara Load Bay', 'Bratislava Delivery Hub'],
        ['Podgorica Depot', 'Split Coastal Terminal'],
    ];

    private const ROUTE_DRIVER_INDEXES = [
        [0, 4, 5],
        [1, 2, 3],
        [2, 4, 6],
        [3, 0, 5],
        [0, 4, 6],
        [1, 2, 5],
        [2, 4, 5],
        [3, 0, 6],
        [0, 4, 5],
        [1, 2, 6],
    ];

    private const ROUTE_DURATIONS_IN_DAYS = [7, 6, 6, 5, 5, 5, 5, 5, 5, 5];

    /**
     * Seed routes, route stops, driver allocations, and bids for the test dispatcher.
     */
    public function run(): void
    {
        $dispatcher = $this->testDispatcher();
        $drivers = $this->testDispatcherDrivers($dispatcher);
        $services = $this->requiredServices();
        $bidRestStops = $this->bidRestStops();
        $nextMonth = now()->addMonthNoOverflow()->startOfMonth()->startOfDay();

        foreach (self::ROUTES as $index => [$origin, $destination]) {
            $routeDuration = self::ROUTE_DURATIONS_IN_DAYS[$index];
            $routeStartDate = $nextMonth->copy()->addDays($this->routeStartOffset($index));
            $routeEndDate = $routeStartDate->copy()->addDays($routeDuration - 1);
            $route = DispatcherRoute::query()->updateOrCreate(
                [
                    'dispatcher_id' => $dispatcher->id,
                    'origin' => $origin,
                    'destination' => $destination,
                ],
                [
                    'planned_travel_details' => sprintf(
                        'Demo convoy %02d from %s to %s.',
                        $index + 1,
                        $origin,
                        $destination
                    ),
                    'convoy_size' => self::CONVOY_SIZE,
                    'start_date' => $routeStartDate->toDateString(),
                    'end_date' => $routeEndDate->toDateString(),
                    'closed_at' => null,
                ]
            );

            $route->routeStops()->delete();
            $route->drivers()->sync($this->driverAssignments($drivers, $index));

            $routeStops = $this->createRouteStops($route, $routeStartDate, $routeEndDate, $services, $index);

            if ($index < self::BIDDED_ROUTE_COUNT) {
                $this->createBids($routeStops, $bidRestStops, $index);
            }
        }
    }

    private function testDispatcher(): Dispatcher
    {
        $user = User::query()
            ->where('email', self::TEST_DISPATCHER_EMAIL)
            ->firstOrFail();

        return Dispatcher::query()
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    /**
     * @return Collection<int, Driver>
     */
    private function testDispatcherDrivers(Dispatcher $dispatcher): Collection
    {
        $drivers = Driver::query()
            ->with('user')
            ->where('dispatcher_id', $dispatcher->id)
            ->whereHas('user', fn ($query) => $query->whereIn('email', self::TEST_DRIVER_EMAILS))
            ->get();

        if ($drivers->count() < 7) {
            throw new RuntimeException('The demo route seeder needs seven drivers assigned to the test dispatcher.');
        }

        $driversByEmail = $drivers->keyBy(fn (Driver $driver): string => $driver->user->email);

        return new Collection(array_map(
            fn (string $email): Driver => $driversByEmail->get($email),
            self::TEST_DRIVER_EMAILS
        ));
    }

    private function routeStartOffset(int $routeIndex): int
    {
        $startOffset = 0;
        $completedWaves = intdiv($routeIndex, 2);

        for ($waveIndex = 0; $waveIndex < $completedWaves; $waveIndex++) {
            $firstRouteIndex = $waveIndex * 2;
            $startOffset += max(
                self::ROUTE_DURATIONS_IN_DAYS[$firstRouteIndex],
                self::ROUTE_DURATIONS_IN_DAYS[$firstRouteIndex + 1]
            );
        }

        return $startOffset;
    }

    /**
     * @return array<string, Service>
     */
    private function requiredServices(): array
    {
        $serviceNames = [
            'Bed',
            'Breakfast',
            'Dinner',
            'Parking',
            'Oil change',
            'Fuel refill',
        ];
        $services = Service::query()
            ->whereIn('name', $serviceNames)
            ->get()
            ->keyBy('name');

        foreach ($serviceNames as $serviceName) {
            if (! $services->has($serviceName)) {
                throw new RuntimeException("Missing seeded service: {$serviceName}.");
            }
        }

        return $services->all();
    }

    /**
     * @return Collection<int, RestStop>
     */
    private function bidRestStops(): Collection
    {
        $restStops = RestStop::query()
            ->where('is_approved', true)
            ->orderBy('id')
            ->limit(3)
            ->get();

        if ($restStops->count() < 3) {
            throw new RuntimeException('The demo route seeder needs at least three approved rest stops.');
        }

        return $restStops;
    }

    /**
     * @param  Collection<int, Driver>  $drivers
     * @return array<int, array{is_convoy_leader: bool}>
     */
    private function driverAssignments(Collection $drivers, int $routeIndex): array
    {
        $driverIndexes = self::ROUTE_DRIVER_INDEXES[$routeIndex];
        $leaderIndex = $driverIndexes[0];
        $assignments = [];

        foreach ($driverIndexes as $driverIndex) {
            $driver = $drivers[$driverIndex];

            $assignments[$driver->id] = [
                'is_convoy_leader' => $driverIndex === $leaderIndex,
            ];
        }

        return $assignments;
    }

    /**
     * @param  array<string, Service>  $services
     * @return array<int, RouteStop>
     */
    private function createRouteStops(
        DispatcherRoute $route,
        mixed $routeStartDate,
        mixed $routeEndDate,
        array $services,
        int $routeIndex
    ): array {
        $numberOfTrucks = self::CONVOY_SIZE;
        $numberOfDrivers = self::CONVOY_SIZE;
        $templates = [
            [
                'location' => $route->origin.' staging stop',
                'description' => 'Overnight parking and dinner before departure.',
                'stop_at' => $routeStartDate->copy()->setTime(20, 0),
                'services' => [
                    'Dinner' => $numberOfDrivers,
                    'Parking' => $numberOfTrucks,
                ],
            ],
            [
                'location' => sprintf('Corridor rest stop %02d', $routeIndex + 1),
                'description' => 'Mid-route overnight stay with beds, dinner, breakfast, and truck parking.',
                'stop_at' => $routeStartDate->copy()->addDays(2)->setTime(21, 0),
                'services' => [
                    'Bed' => $numberOfDrivers,
                    'Breakfast' => $numberOfDrivers,
                    'Dinner' => $numberOfDrivers,
                    'Parking' => $numberOfTrucks,
                ],
            ],
            [
                'location' => $route->destination.' arrival service point',
                'description' => 'Arrival overnight parking and truck service before unloading.',
                'stop_at' => $routeEndDate->copy()->setTime(20, 0),
                'services' => [
                    'Parking' => $numberOfTrucks,
                    'Fuel refill' => $numberOfTrucks,
                    'Oil change' => ($routeIndex % self::CONVOY_SIZE) + 1,
                ],
            ],
        ];
        $routeStops = [];

        foreach ($templates as $template) {
            $routeStop = $route->routeStops()->create([
                'location' => $template['location'],
                'description' => $template['description'],
                'stop_at' => $template['stop_at'],
                'number_of_trucks' => $numberOfTrucks,
                'number_of_drivers' => $numberOfDrivers,
            ]);

            $routeStop->services()->attach($this->serviceQuantities($services, $template['services']));
            $routeStops[] = $routeStop;
        }

        return $routeStops;
    }

    /**
     * @param  array<string, Service>  $services
     * @param  array<string, int>  $quantities
     * @return array<int, array{quantity: int}>
     */
    private function serviceQuantities(array $services, array $quantities): array
    {
        $payload = [];

        foreach ($quantities as $serviceName => $quantity) {
            $payload[$services[$serviceName]->id] = [
                'quantity' => $quantity,
            ];
        }

        return $payload;
    }

    /**
     * @param  array<int, RouteStop>  $routeStops
     * @param  Collection<int, RestStop>  $restStops
     */
    private function createBids(array $routeStops, Collection $restStops, int $routeIndex): void
    {
        foreach ($routeStops as $stopIndex => $routeStop) {
            foreach ($restStops as $restStopIndex => $restStop) {
                $originalPrice = $this->originalBidPrice($routeStop, $restStop);

                RouteStopBid::query()->updateOrCreate(
                    [
                        'route_stop_id' => $routeStop->id,
                        'rest_stop_id' => $restStop->id,
                    ],
                    [
                        'original_price' => $originalPrice,
                        'price' => $this->discountedBidPrice($originalPrice, $routeIndex, $stopIndex, $restStopIndex),
                        'status' => RouteStopBid::STATUS_PENDING,
                    ]
                );
            }
        }
    }

    private function originalBidPrice(RouteStop $routeStop, RestStop $restStop): string
    {
        $total = $routeStop->routeStopServices()
            ->get()
            ->sum(function ($routeStopService) use ($restStop): float {
                $pricePerUnit = RestStopServiceModel::query()
                    ->where('rest_stop_id', $restStop->id)
                    ->where('service_id', $routeStopService->service_id)
                    ->value('price_per_unit') ?? 0;

                return (float) $pricePerUnit * $routeStopService->quantity;
            });

        return number_format($total, 2, '.', '');
    }

    private function discountedBidPrice(
        string $originalPrice,
        int $routeIndex,
        int $stopIndex,
        int $restStopIndex
    ): string {
        $discounts = [0.92, 0.94, 0.96, 0.98];
        $discount = $discounts[($routeIndex + $stopIndex + $restStopIndex) % count($discounts)];

        return number_format((float) $originalPrice * $discount, 2, '.', '');
    }
}
