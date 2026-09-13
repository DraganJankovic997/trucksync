<?php

namespace App\Contracts;

use App\Exceptions\RouteStopNotFoundException;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

interface BidServiceContract
{
    /**
     * @throws RouteStopNotFoundException
     * @throws ValidationException
     */
    public function upsertForRestStop(
        RestStop $restStop,
        int $routeStopId,
        string $originalPrice,
        string $price
    ): RouteStopBid;

    public function findForRestStopByRouteStop(RestStop $restStop, int $routeStopId): ?RouteStopBid;

    public function markRouteStopBidSelected(RouteStop $routeStop, int $restStopId): void;

    public function rejectUnselectedForRoute(DispatcherRoute $route): void;

    /**
     * @throws ValidationException
     */
    public function deleteForRestStopByRouteStop(RestStop $restStop, int $routeStopId): ?RouteStopBid;

    /**
     * @return Collection<int, RouteStopBid>
     */
    public function forRouteStop(RouteStop $routeStop): Collection;
}
