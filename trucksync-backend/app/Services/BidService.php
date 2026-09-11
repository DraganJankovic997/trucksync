<?php

namespace App\Services;

use App\Contracts\BidServiceContract;
use App\Exceptions\RouteStopNotFoundException;
use App\Models\RestStop;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class BidService implements BidServiceContract
{
    public function upsertForRestStop(
        RestStop $restStop,
        int $routeStopId,
        string $originalPrice,
        string $price
    ): RouteStopBid {
        $routeStop = RouteStop::query()
            ->with('route')
            ->find($routeStopId);

        if (! $routeStop) {
            throw new RouteStopNotFoundException;
        }

        if ($routeStop->route?->closed_at !== null) {
            throw ValidationException::withMessages([
                'route_stop_id' => 'You cannot bid on a closed route.',
            ]);
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
        $bid = RouteStopBid::query()
            ->with('routeStop.route')
            ->where('route_stop_id', $routeStopId)
            ->where('rest_stop_id', $restStop->id)
            ->first();

        if (! $bid) {
            return null;
        }

        if ($bid->routeStop?->route?->closed_at !== null) {
            throw ValidationException::withMessages([
                'route_stop_id' => 'You cannot delete a bid on a closed route.',
            ]);
        }

        RouteStopBid::query()
            ->where('route_stop_id', $routeStopId)
            ->where('rest_stop_id', $restStop->id)
            ->delete();

        return $bid;
    }

    /**
     * @return Collection<int, RouteStopBid>
     */
    public function forRouteStop(RouteStop $routeStop): Collection
    {
        return $routeStop
            ->routeStopBids()
            ->with('restStop.user')
            ->orderBy('rest_stop_id')
            ->get();
    }

    private function bidForRestStop(RestStop $restStop, int $routeStopId): ?RouteStopBid
    {
        return RouteStopBid::query()
            ->where('route_stop_id', $routeStopId)
            ->where('rest_stop_id', $restStop->id)
            ->first();
    }
}
