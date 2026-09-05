<?php

namespace App\Contracts;

use App\Models\RestStop;
use App\Models\RestStopService as RestStopServiceModel;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface RestStopServiceContract
{
    public function findForUser(User $user): ?RestStop;

    /**
     * @return Collection<int, Service>|null
     */
    public function servicesForRestStop(int $restStopId): ?Collection;

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

    public function removeServiceForUser(User $user, int $serviceId): ?RestStopServiceModel;
}
