<?php

namespace Database\Seeders;

use App\Models\RestStop;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    private const SERVICES = [
        'Bed' => 'driver',
        'Breakfast' => 'driver',
        'Dinner' => 'driver',
        'Parking' => 'truck',
        'Oil change' => 'truck',
        'Fuel refill' => 'truck',
    ];

    private const BASE_REST_STOP_SERVICES = [
        'Bed',
        'Breakfast',
        'Dinner',
        'Parking',
    ];

    private const EXTRA_REST_STOP_SERVICES = [
        'Oil change',
        'Fuel refill',
    ];

    /**
     * Seed the service catalog and attach services to demo rest stops.
     */
    public function run(): void
    {
        $services = [];

        foreach (self::SERVICES as $name => $measurementUnit) {
            $services[$name] = Service::query()->updateOrCreate(
                ['name' => $name],
                ['measurement_unit' => $measurementUnit]
            );
        }

        RestStop::query()
            ->orderBy('id')
            ->get()
            ->each(function (RestStop $restStop, int $index) use ($services): void {
                $serviceNames = self::BASE_REST_STOP_SERVICES;

                if ($index < 5) {
                    $serviceNames = [
                        ...$serviceNames,
                        ...self::EXTRA_REST_STOP_SERVICES,
                    ];
                }

                foreach ($serviceNames as $serviceName) {
                    DB::table('rest_stop_services')->updateOrInsert(
                        [
                            'rest_stop_id' => $restStop->id,
                            'service_id' => $services[$serviceName]->id,
                        ],
                        [
                            'price_per_unit' => $this->priceFor($serviceName, $index),
                        ]
                    );
                }
            });
    }

    private function priceFor(string $serviceName, int $index): string
    {
        $basePrices = [
            'Bed' => 32,
            'Breakfast' => 9,
            'Dinner' => 18,
            'Parking' => 14,
            'Oil change' => 180,
            'Fuel refill' => 145,
        ];

        $step = ($index % 4) * 2;

        return number_format($basePrices[$serviceName] + $step, 2, '.', '');
    }
}
