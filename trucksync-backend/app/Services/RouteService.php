<?php

namespace App\Services;

use App\Contracts\RouteServiceContract;
use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RouteService implements RouteServiceContract
{
    /**
     * @return Collection<int, DispatcherRoute>|null
     */
    public function forDispatcher(int $dispatcherId): ?Collection
    {
        $dispatcher = Dispatcher::query()->find($dispatcherId);

        if (! $dispatcher) {
            return null;
        }

        return $dispatcher->routes()
            ->orderByRaw('CASE WHEN closed_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('created_at')
            ->get();
    }

    public function createForUser(
        User $user,
        string $origin,
        string $destination,
        ?string $plannedTravelDetails,
        int $convoySize,
        string $startDate,
        string $endDate
    ): ?DispatcherRoute {
        $dispatcher = $this->dispatcherForUser($user);

        if (! $dispatcher) {
            return null;
        }

        $route = $dispatcher->routes()->create([
            'origin' => $origin,
            'destination' => $destination,
            'planned_travel_details' => $plannedTravelDetails,
            'convoy_size' => $convoySize,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $route->refresh();
    }

    public function closeForUser(User $user, int $routeId): ?DispatcherRoute
    {
        $dispatcher = $this->dispatcherForUser($user);

        if (! $dispatcher) {
            return null;
        }

        $route = $dispatcher->routes()
            ->whereKey($routeId)
            ->first();

        if (! $route) {
            return null;
        }

        $route->closed_at = now();
        $route->save();

        return $route->refresh();
    }

    public function findWithStops(int $routeId): ?DispatcherRoute
    {
        return DispatcherRoute::query()
            ->with([
                'routeStops' => fn ($query) => $query->orderBy('id'),
                'routeStops.services' => fn ($query) => $query->orderBy('services.id'),
            ])
            ->find($routeId);
    }

    private function dispatcherForUser(User $user): ?Dispatcher
    {
        return Dispatcher::query()
            ->where('user_id', $user->id)
            ->first();
    }
}
