<?php

namespace App\Contracts;

use App\Models\Driver;
use App\Models\User;

interface DriverServiceContract
{
    public function upsertForUser(
        User $user,
        string $licenseNumber,
        ?int $dispatcherId
    ): Driver;
}
