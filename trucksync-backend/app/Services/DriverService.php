<?php

namespace App\Services;

use App\Contracts\DriverServiceContract;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DriverService implements DriverServiceContract
{
    /**
     * @return Collection<int, Driver>|null
     *
     * @throws RouteNotFoundException
     * @throws RouteNotOwnedByDispatcherException
     */
    public function forDispatcherUser(User $user, ?int $availableForRouteId = null): ?Collection
    {
        $dispatcher = Dispatcher::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $dispatcher) {
            return null;
        }

        return $dispatcher->drivers()
            ->with('user')
            ->orderBy('drivers.id')
            ->get()
            ->when(
                $availableForRouteId !== null,
                fn (Collection $drivers): Collection => $this->markAvailabilityForRoute(
                    $drivers,
                    $dispatcher,
                    $availableForRouteId
                )
            );
    }

    public function findForUser(User $user): ?Driver
    {
        return Driver::query()
            ->where('user_id', $user->id)
            ->first();
    }

    public function upsertForUser(
        User $user,
        string $licenseNumber,
        ?int $dispatcherId
    ): Driver {
        return Driver::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'license_number' => $licenseNumber,
                'dispatcher_id' => $dispatcherId,
            ],
        )->refresh();
    }

    /**
     * @param  Collection<int, Driver>  $drivers
     * @return Collection<int, Driver>
     *
     * @throws RouteNotFoundException
     * @throws RouteNotOwnedByDispatcherException
     */
    private function markAvailabilityForRoute(
        Collection $drivers,
        Dispatcher $dispatcher,
        int $routeId
    ): Collection {
        $route = DispatcherRoute::query()
            ->whereKey($routeId)
            ->first();

        if (! $route) {
            throw new RouteNotFoundException;
        }

        if ((int) $route->dispatcher_id !== (int) $dispatcher->id) {
            throw new RouteNotOwnedByDispatcherException(
                'You cannot view driver availability for a route you did not create.'
            );
        }

        $driverIds = $drivers->pluck('id');

        if ($driverIds->isEmpty()) {
            return $drivers;
        }

        $busyDriverIds = Driver::query()
            ->whereIn('drivers.id', $driverIds)
            ->whereHas('routes', fn ($query) => $query
                ->where('routes.id', '<>', $route->id)
                ->whereDate('routes.start_date', '<=', $route->end_date->toDateString())
                ->whereDate('routes.end_date', '>=', $route->start_date->toDateString()))
            ->pluck('drivers.id')
            ->map(fn (int|string $driverId): int => (int) $driverId)
            ->flip();

        $drivers->each(fn (Driver $driver) => $driver->setAttribute(
            'is_available',
            ! $busyDriverIds->has((int) $driver->id)
        ));

        return $drivers;
    }
}
