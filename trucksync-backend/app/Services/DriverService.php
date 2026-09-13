<?php

namespace App\Services;

use App\Contracts\DriverServiceContract;
use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DriverService implements DriverServiceContract
{
    /**
     * @return Collection<int, Driver>|null
     */
    public function forDispatcherUser(User $user): ?Collection
    {
        $dispatcher = Dispatcher::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $dispatcher) {
            return null;
        }

        return $dispatcher->drivers()
            ->with('user')
            ->orderBy('drivers.id')
            ->get();
    }

    public function findForUser(User $user): ?Driver
    {
        return Driver::query()
            ->where('user_id', $user->id)
            ->first();
    }

    public function upsertForUser(
        User $user,
        string $licenseNumber,
        ?int $dispatcherId
    ): Driver {
        return Driver::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'license_number' => $licenseNumber,
                'dispatcher_id' => $dispatcherId,
            ],
        )->refresh();
    }
}
