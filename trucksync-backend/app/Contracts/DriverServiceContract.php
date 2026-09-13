<?php

namespace App\Contracts;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface DriverServiceContract
{
    /**
     * @return Collection<int, Driver>|null
     */
    public function forDispatcherUser(User $user): ?Collection;

    public function findForUser(User $user): ?Driver;

    public function upsertForUser(
        User $user,
        string $licenseNumber,
        ?int $dispatcherId
    ): Driver;
}
