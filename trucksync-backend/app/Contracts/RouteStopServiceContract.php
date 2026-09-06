<?php

namespace App\Contracts;

use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Models\RouteStop;
use App\Models\User;

interface RouteStopServiceContract
{
    /**
     * @param  array<int, array{service_id: int, quantity: int}>  $services
     *
     * @throws RouteNotFoundException
     * @throws RouteNotOwnedByDispatcherException
     */
    public function createForUser(
        User $user,
        int $routeId,
        int $numberOfTrucks,
        int $numberOfDrivers,
        array $services
    ): RouteStop;
}
