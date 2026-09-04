<?php

namespace App\Services;

use App\Contracts\DriverServiceContract;
use App\Models\Driver;
use App\Models\User;

class DriverService implements DriverServiceContract
{
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
