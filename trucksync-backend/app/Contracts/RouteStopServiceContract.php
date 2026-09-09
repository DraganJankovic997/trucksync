<?php

namespace App\Contracts;

use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Exceptions\RouteStopNotFoundException;
use App\Exceptions\RouteStopNotOwnedByDispatcherException;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

interface RouteStopServiceContract
{
    /**
     * @return Collection<int, RouteStop>
     *
     * @throws RouteNotFoundException
     */
    public function forRoute(int $routeId): Collection;

    public function findWithServices(int $routeStopId): ?RouteStop;

    /**
     * @return LengthAwarePaginator<int, RouteStop>
     */
    public function unfulfilled(
        ?string $search = null,
        int $perPage = 15,
        int $page = 1,
        string $sortKey = 'stop_at',
        string $sortOrder = 'desc'
    ): LengthAwarePaginator;

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
    ): RouteStop;

    /**
     * @param  array<int, array{service_id: int, quantity: int}>  $services
     */
    public function syncServicesForRouteStop(
        RouteStop $routeStop,
        array $services
    ): RouteStop;

    /**
     * @throws RouteStopNotFoundException
     * @throws RouteStopNotOwnedByDispatcherException
     * @throws ValidationException
     */
    public function fulfillForUser(User $user, int $routeStopId, int $restStopId): RouteStop;
}
