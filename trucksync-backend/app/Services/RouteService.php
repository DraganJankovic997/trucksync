<?php

namespace App\Services;

use App\Contracts\BidServiceContract;
use App\Contracts\RouteServiceContract;
use App\Exceptions\InvalidRouteDriverAssignmentException;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RouteService implements RouteServiceContract
{
    public function __construct(
        private readonly BidServiceContract $bidService
    ) {}

    /**
     * @return Collection<int, DispatcherRoute>|null
     */
    public function forDispatcher(int $dispatcherId): ?Collection
    {
        $dispatcher = Dispatcher::query()->find($dispatcherId);

        if (! $dispatcher) {
            return null;
        }

        return $dispatcher->routes()
            ->with('drivers.user')
            ->orderByRaw('CASE WHEN closed_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return Collection<int, DispatcherRoute>|null
     */
    public function forDriverUser(User $user): ?Collection
    {
        $driver = $this->driverForUser($user);

        if (! $driver) {
            return null;
        }

        return DispatcherRoute::query()
            ->whereHas('drivers', fn ($query) => $query->whereKey($driver->id))
            ->with([
                'drivers.user',
                'routeStops' => fn ($query) => $query
                    ->withAcceptedBidPrice()
                    ->withCount(['routeStopBids as bids_count'])
                    ->orderBy('stop_at')
                    ->orderBy('id'),
                'routeStops.services' => fn ($query) => $query->orderBy('services.id'),
            ])
            ->orderByRaw('CASE WHEN closed_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();
    }

    public function createForUser(
        User $user,
        string $origin,
        string $destination,
        ?string $plannedTravelDetails,
        int $convoySize,
        string $startDate,
        string $endDate
    ): ?DispatcherRoute {
        $dispatcher = $this->dispatcherForUser($user);

        if (! $dispatcher) {
            return null;
        }

        $route = $dispatcher->routes()->create([
            'origin' => $origin,
            'destination' => $destination,
            'planned_travel_details' => $plannedTravelDetails,
            'convoy_size' => $convoySize,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $route->refresh();
    }

    public function closeForUser(User $user, int $routeId): ?DispatcherRoute
    {
        $dispatcher = $this->dispatcherForUser($user);

        if (! $dispatcher) {
            return null;
        }

        $route = $dispatcher->routes()
            ->whereKey($routeId)
            ->first();

        if (! $route) {
            return null;
        }

        return DB::transaction(function () use ($route): DispatcherRoute {
            $route->closed_at = now();
            $route->save();

            $this->bidService->rejectUnselectedForRoute($route);

            return $route->refresh();
        });
    }

    public function findWithStops(int $routeId): ?DispatcherRoute
    {
        return DispatcherRoute::query()
            ->with([
                'drivers.user',
                'routeStops' => fn ($query) => $query
                    ->withAcceptedBidPrice()
                    ->withCount(['routeStopBids as bids_count'])
                    ->orderBy('id'),
                'routeStops.services' => fn ($query) => $query->orderBy('services.id'),
            ])
            ->find($routeId);
    }

    /**
     * @param  array<int, array{driver_id: int, is_convoy_leader: bool}>  $driverAssignments
     *
     * @throws InvalidRouteDriverAssignmentException
     * @throws RouteNotFoundException
     * @throws RouteNotOwnedByDispatcherException
     * @throws ValidationException
     */
    public function syncDriversForUser(
        User $user,
        int $routeId,
        array $driverAssignments
    ): ?DispatcherRoute {
        $dispatcher = $this->dispatcherForUser($user);

        if (! $dispatcher) {
            return null;
        }

        $route = DispatcherRoute::query()
            ->whereKey($routeId)
            ->first();

        if (! $route) {
            throw new RouteNotFoundException;
        }

        if ((int) $route->dispatcher_id !== (int) $dispatcher->id) {
            throw new RouteNotOwnedByDispatcherException(
                'You cannot assign drivers to a route you did not create.'
            );
        }

        if ($route->closed_at !== null) {
            throw ValidationException::withMessages([
                'route_id' => 'You cannot assign drivers to a closed route.',
            ]);
        }

        $driverIds = collect($driverAssignments)
            ->pluck('driver_id')
            ->map(fn (int|string $driverId): int => (int) $driverId)
            ->values();

        if ($driverIds->isNotEmpty()) {
            $dispatcherDriverCount = Driver::query()
                ->where('dispatcher_id', $dispatcher->id)
                ->whereIn('id', $driverIds)
                ->count();

            if ($dispatcherDriverCount !== $driverIds->count()) {
                throw new InvalidRouteDriverAssignmentException;
            }

            $unavailableDriverCount = Driver::query()
                ->whereIn('drivers.id', $driverIds)
                ->whereHas('routes', fn ($query) => $query
                    ->where('routes.id', '<>', $route->id)
                    ->whereNull('routes.closed_at')
                    ->whereDate('routes.start_date', '<=', $route->end_date->toDateString())
                    ->whereDate('routes.end_date', '>=', $route->start_date->toDateString()))
                ->count();

            if ($unavailableDriverCount > 0) {
                throw new InvalidRouteDriverAssignmentException(
                    'Selected drivers must be available for this route schedule.'
                );
            }
        }

        return DB::transaction(function () use ($route, $driverAssignments): DispatcherRoute {
            $syncPayload = [];

            foreach ($driverAssignments as $driverAssignment) {
                $syncPayload[(int) $driverAssignment['driver_id']] = [
                    'is_convoy_leader' => (bool) $driverAssignment['is_convoy_leader'],
                ];
            }

            $route->drivers()->sync($syncPayload);

            $updatedRoute = $this->findWithStops($route->id);

            if (! $updatedRoute) {
                throw new RouteNotFoundException;
            }

            return $updatedRoute;
        });
    }

    private function dispatcherForUser(User $user): ?Dispatcher
    {
        return Dispatcher::query()
            ->where('user_id', $user->id)
            ->first();
    }

    private function driverForUser(User $user): ?Driver
    {
        return Driver::query()
            ->where('user_id', $user->id)
            ->first();
    }
}
