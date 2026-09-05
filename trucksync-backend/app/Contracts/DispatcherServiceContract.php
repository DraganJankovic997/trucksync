<?php

namespace App\Contracts;

use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface DispatcherServiceContract
{
    /**
     * @return Collection<int, Dispatcher>
     */
    public function all(): Collection;

    public function findForUser(User $user): ?Dispatcher;

    /**
     * @return Collection<int, DispatcherRoute>|null
     */
    public function routesForDispatcher(int $dispatcherId): ?Collection;

    public function upsertForUser(
        User $user,
        string $companyName,
        string $city,
        string $address,
        string $postCode,
        string $registrationNumber
    ): Dispatcher;

    public function createRouteForUser(
        User $user,
        string $origin,
        string $destination,
        ?string $plannedTravelDetails,
        int $convoySize,
        string $startDate,
        string $endDate
    ): ?DispatcherRoute;

    public function closeRouteForUser(User $user, int $routeId): ?DispatcherRoute;
}
