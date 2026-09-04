<?php

namespace App\Contracts;

use App\Models\Dispatcher;
use App\Models\User;

interface DispatcherServiceContract
{
    public function upsertForUser(
        User $user,
        string $companyName,
        string $country,
        string $city,
        string $address,
        string $postCode,
        string $registrationNumber
    ): Dispatcher;
}
