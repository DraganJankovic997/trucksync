<?php

namespace App\Contracts;

use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface RouteServiceContract
{
    /**
     * @return Collection<int, DispatcherRoute>|null
     */
    public function forDispatcher(int $dispatcherId): ?Collection;

    public function createForUser(
        User $user,
        string $origin,
        string $destination,
        ?string $plannedTravelDetails,
        int $convoySize,
        string $startDate,
        string $endDate
    ): ?DispatcherRoute;

    public function closeForUser(User $user, int $routeId): ?DispatcherRoute;
}
