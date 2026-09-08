<?php

namespace App\Services;

use App\Contracts\BidServiceContract;
use App\Exceptions\RouteStopNotFoundException;
use App\Models\RestStop;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\User;

class BidService implements BidServiceContract
{
    public function createForUser(
        User $user,
        int $routeStopId,
        string $originalPrice,
        string $price
    ): ?RouteStopBid {
        $restStop = $this->restStopForUser($user);

        if (! $restStop) {
            return null;
        }

        if (RouteStop::query()->whereKey($routeStopId)->doesntExist()) {
            throw new RouteStopNotFoundException;
        }

        return RouteStopBid::query()->firstOrCreate([
            'route_stop_id' => $routeStopId,
            'rest_stop_id' => $restStop->id,
        ], [
            'original_price' => $originalPrice,
            'price' => $price,
        ]);
    }

    private function restStopForUser(User $user): ?RestStop
    {
        return RestStop::query()
            ->where('user_id', $user->id)
            ->first();
    }
}
