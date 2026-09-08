<?php

namespace App\Services;

use App\Contracts\BidServiceContract;
use App\Exceptions\RouteStopNotFoundException;
use App\Models\RestStop;
use App\Models\RouteStop;
use App\Models\RouteStopBid;

class BidService implements BidServiceContract
{
    public function upsertForRestStop(
        RestStop $restStop,
        int $routeStopId,
        string $originalPrice,
        string $price
    ): RouteStopBid {
        if (RouteStop::query()->whereKey($routeStopId)->doesntExist()) {
            throw new RouteStopNotFoundException;
        }

        return RouteStopBid::query()->updateOrCreate(
            [
                'route_stop_id' => $routeStopId,
                'rest_stop_id' => $restStop->id,
            ],
            [
                'original_price' => $originalPrice,
                'price' => $price,
            ],
        );
    }

    public function findForRestStopByRouteStop(RestStop $restStop, int $routeStopId): ?RouteStopBid
    {
        return $this->bidForRestStop($restStop, $routeStopId);
    }

    public function deleteForRestStopByRouteStop(RestStop $restStop, int $routeStopId): ?RouteStopBid
    {
        $bid = $this->bidForRestStop($restStop, $routeStopId);

        if (! $bid) {
            return null;
        }

        RouteStopBid::query()
            ->where('route_stop_id', $routeStopId)
            ->where('rest_stop_id', $restStop->id)
            ->delete();

        return $bid;
    }

    private function bidForRestStop(RestStop $restStop, int $routeStopId): ?RouteStopBid
    {
        return RouteStopBid::query()
            ->where('route_stop_id', $routeStopId)
            ->where('rest_stop_id', $restStop->id)
            ->first();
    }
}
