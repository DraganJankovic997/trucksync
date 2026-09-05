<?php

namespace App\Services;

use App\Contracts\DispatcherServiceContract;
use App\Models\Dispatcher;
use App\Models\Route as DispatcherRoute;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DispatcherService implements DispatcherServiceContract
{
    /**
     * @return Collection<int, Dispatcher>
     */
    public function all(): Collection
    {
        return Dispatcher::query()
            ->orderBy('id')
            ->get();
    }

    public function findForUser(User $user): ?Dispatcher
    {
        return Dispatcher::query()
            ->where('user_id', $user->id)
            ->first();
    }

    public function upsertForUser(
        User $user,
        string $companyName,
        string $city,
        string $address,
        string $postCode,
        string $registrationNumber
    ): Dispatcher {
        $dispatcher = Dispatcher::query()->firstOrNew([
            'user_id' => $user->id,
        ]);

        $dispatcher->fill([
            'company_name' => $companyName,
            'city' => $city,
            'address' => $address,
            'post_code' => $postCode,
            'registration_number' => $registrationNumber,
        ]);

        if (! $dispatcher->exists || $dispatcher->isDirty()) {
            $dispatcher->is_approved = false;
        }

        $dispatcher->save();

        return $dispatcher->refresh();
    }

    public function createRouteForUser(
        User $user,
        string $origin,
        string $destination,
        ?string $plannedTravelDetails,
        int $convoySize,
        string $startDate,
        string $endDate
    ): ?DispatcherRoute {
        $dispatcher = $this->findForUser($user);

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
}
