<?php

namespace App\Contracts;

use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface DriverServiceContract
{
    /**
     * @return Collection<int, Driver>|null
     *
     * @throws RouteNotFoundException
     * @throws RouteNotOwnedByDispatcherException
     */
    public function forDispatcherUser(User $user, ?int $availableForRouteId = null): ?Collection;

    public function findForUser(User $user): ?Driver;

    public function upsertForUser(
        User $user,
        string $licenseNumber,
        ?int $dispatcherId
    ): Driver;
}
