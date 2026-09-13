<?php

namespace App\Contracts;

use App\Exceptions\RouteStopNotFoundException;
use App\Models\RestStop;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
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

    /**
     * @return LengthAwarePaginator<int, RouteStopBid>
     */
    public function forRestStop(
        RestStop $restStop,
        ?string $status = null,
        ?string $from = null,
        int $perPage = 15,
        int $page = 1
    ): LengthAwarePaginator;

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
