<?php

namespace App\Services;

use App\Contracts\RouteStopServiceContract;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RouteStopService implements RouteStopServiceContract
{
    /**
     * @return Collection<int, RouteStop>
     *
     * @throws RouteNotFoundException
     */
    public function forRoute(int $routeId): Collection
    {
        $route = DispatcherRoute::query()->find($routeId);

        if (! $route) {
            throw new RouteNotFoundException;
        }

        return $route->routeStops()
            ->with([
                'services' => fn ($query) => $query->orderBy('services.id'),
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<int, array{service_id: int, quantity: int}>  $services
     *
     * @throws RouteNotFoundException
     * @throws RouteNotOwnedByDispatcherException
     */
    public function createForUser(
        User $user,
        int $routeId,
        string $location,
        ?string $description,
        int $numberOfTrucks,
        int $numberOfDrivers,
        array $services
    ): RouteStop {
        $route = DispatcherRoute::query()
            ->whereKey($routeId)
            ->first();

        if (! $route) {
            throw new RouteNotFoundException;
        }

        if ($route->dispatcher()->where('user_id', $user->id)->doesntExist()) {
            throw new RouteNotOwnedByDispatcherException;
        }

        return DB::transaction(function () use ($route, $location, $description, $numberOfTrucks, $numberOfDrivers, $services): RouteStop {
            $routeStop = $route->routeStops()->create([
                'location' => $location,
                'description' => $description,
                'number_of_trucks' => $numberOfTrucks,
                'number_of_drivers' => $numberOfDrivers,
            ]);

            $routeStop->services()->attach($this->serviceQuantities($services));

            return $routeStop->load([
                'services' => fn ($query) => $query->orderBy('services.id'),
            ]);
        });
    }

    /**
     * @param  array<int, array{service_id: int, quantity: int}>  $services
     */
    public function syncServicesForRouteStop(
        RouteStop $routeStop,
        array $services
    ): RouteStop {
        return DB::transaction(function () use ($routeStop, $services): RouteStop {
            $routeStop->services()->sync($this->serviceQuantities($services));

            return $routeStop->refresh()->load([
                'services' => fn ($query) => $query->orderBy('services.id'),
            ]);
        });
    }

    /**
     * @param  array<int, array{service_id: int, quantity: int}>  $services
     * @return array<int, array{quantity: int}>
     */
    private function serviceQuantities(array $services): array
    {
        $serviceQuantities = [];

        foreach ($services as $service) {
            $serviceQuantities[(int) $service['service_id']] = [
                'quantity' => (int) $service['quantity'],
            ];
        }

        return $serviceQuantities;
    }
}
