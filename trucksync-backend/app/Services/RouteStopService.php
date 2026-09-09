<?php

namespace App\Services;

use App\Contracts\RouteStopServiceContract;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Exceptions\RouteStopNotFoundException;
use App\Exceptions\RouteStopNotOwnedByDispatcherException;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RouteStopService implements RouteStopServiceContract
{
    /**
     * @var array<string, string>
     */
    private const UNFULFILLED_SORT_COLUMNS = [
        'id' => 'route_stops.id',
        'route_id' => 'route_stops.route_id',
        'location' => 'route_stops.location',
        'description' => 'route_stops.description',
        'stop_at' => 'route_stops.stop_at',
        'number_of_trucks' => 'route_stops.number_of_trucks',
        'number_of_drivers' => 'route_stops.number_of_drivers',
    ];

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
            ->withCount(['routeStopBids as bids_count'])
            ->orderBy('id')
            ->get();
    }

    public function findWithServices(int $routeStopId): ?RouteStop
    {
        return RouteStop::query()
            ->with([
                'services' => fn ($query) => $query->orderBy('services.id'),
            ])
            ->withCount(['routeStopBids as bids_count'])
            ->find($routeStopId);
    }

    /**
     * @return LengthAwarePaginator<int, RouteStop>
     */
    public function unfulfilled(
        ?string $search = null,
        int $perPage = 15,
        int $page = 1,
        string $sortKey = 'stop_at',
        string $sortOrder = 'desc'
    ): LengthAwarePaginator {
        return RouteStop::query()
            ->with([
                'route.dispatcher',
                'services' => fn ($query) => $query->orderBy('services.id'),
            ])
            ->withCount(['routeStopBids as bids_count'])
            ->whereNull('route_stops.fulfiled_at')
            ->when($search !== null, fn ($query) => $query
                ->where(fn ($query) => $query
                    ->whereLike('route_stops.location', '%'.$search.'%', false)
                    ->orWhereLike('route_stops.description', '%'.$search.'%', false)
                )
            )
            ->orderBy(self::UNFULFILLED_SORT_COLUMNS[$sortKey], $sortOrder)
            ->orderBy('route_stops.id')
            ->paginate(perPage: $perPage, page: $page);
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
        string $stopAt,
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

        return DB::transaction(function () use ($route, $location, $description, $stopAt, $numberOfTrucks, $numberOfDrivers, $services): RouteStop {
            $routeStop = $route->routeStops()->create([
                'location' => $location,
                'description' => $description,
                'stop_at' => $stopAt,
                'number_of_trucks' => $numberOfTrucks,
                'number_of_drivers' => $numberOfDrivers,
            ]);

            $routeStop->services()->attach($this->serviceQuantities($services));

            return $routeStop
                ->load([
                    'services' => fn ($query) => $query->orderBy('services.id'),
                ])
                ->loadCount(['routeStopBids as bids_count']);
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

            return $routeStop
                ->refresh()
                ->load([
                    'services' => fn ($query) => $query->orderBy('services.id'),
                ])
                ->loadCount(['routeStopBids as bids_count']);
        });
    }

    /**
     * @throws RouteStopNotFoundException
     * @throws RouteStopNotOwnedByDispatcherException
     */
    public function fulfillForUser(User $user, int $routeStopId, int $restStopId): RouteStop
    {
        return DB::transaction(function () use ($user, $routeStopId, $restStopId): RouteStop {
            $routeStop = RouteStop::query()
                ->with('route.dispatcher')
                ->find($routeStopId);

            if (! $routeStop) {
                throw new RouteStopNotFoundException;
            }

            if ($routeStop->route?->dispatcher?->user_id !== $user->id) {
                throw new RouteStopNotOwnedByDispatcherException(
                    'You cannot fulfill a route stop for a route you did not create.'
                );
            }

            $routeStop->fulfiled_by = $restStopId;
            $routeStop->fulfiled_at = now();
            $routeStop->save();

            $this->closeRouteIfReady($routeStop->route);

            return $routeStop
                ->refresh()
                ->load([
                    'services' => fn ($query) => $query->orderBy('services.id'),
                ])
                ->loadCount(['routeStopBids as bids_count']);
        });
    }

    private function closeRouteIfReady(DispatcherRoute $route): void
    {
        if ($route->closed_at !== null) {
            return;
        }

        $allRouteStopsFulfilled = $route->routeStops()->exists()
            && $route->routeStops()->whereNull('fulfiled_at')->doesntExist();
        $startDateHasPassed = $route->start_date->isPast();

        if (! $allRouteStopsFulfilled && ! $startDateHasPassed) {
            return;
        }

        $route->closed_at = now();
        $route->save();
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
