<?php

namespace App\Contracts;

use App\Models\RestStop;
use App\Models\RestStopService as RestStopServiceModel;
use App\Models\User;

interface RestStopServiceContract
{
    public function findForUser(User $user): ?RestStop;

    public function upsertForUser(
        User $user,
        string $country,
        string $city,
        string $address,
        string $postCode,
        string $worksFrom,
        string $worksTo
    ): RestStop;

    public function addServiceForUser(User $user, int $serviceId): ?RestStopServiceModel;
}
