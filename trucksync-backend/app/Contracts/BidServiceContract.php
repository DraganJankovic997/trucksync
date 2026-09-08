<?php

namespace App\Contracts;

use App\Exceptions\RouteStopNotFoundException;
use App\Models\RouteStopBid;
use App\Models\User;

interface BidServiceContract
{
    /**
     * @throws RouteStopNotFoundException
     */
    public function createForUser(
        User $user,
        int $routeStopId,
        string $originalPrice,
        string $price
    ): ?RouteStopBid;
}
