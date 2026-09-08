<?php

namespace App\Contracts;

use App\Exceptions\RouteStopNotFoundException;
use App\Models\RestStop;
use App\Models\RouteStopBid;

interface BidServiceContract
{
    /**
     * @throws RouteStopNotFoundException
     */
    public function upsertForRestStop(
        RestStop $restStop,
        int $routeStopId,
        string $originalPrice,
        string $price
    ): RouteStopBid;

    public function findForRestStopByRouteStop(RestStop $restStop, int $routeStopId): ?RouteStopBid;

    public function deleteForRestStopByRouteStop(RestStop $restStop, int $routeStopId): ?RouteStopBid;
}
