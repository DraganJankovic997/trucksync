<?php

namespace App\Services;

use App\Contracts\RestStopServiceContract;
use App\Models\RestStop;
use App\Models\RestStopService as RestStopServiceModel;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RestStopService implements RestStopServiceContract
{
    public function findForUser(User $user): ?RestStop
    {
        return RestStop::query()
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * @return Collection<int, Service>|null
     */
    public function servicesForRestStop(int $restStopId): ?Collection
    {
        $restStop = RestStop::query()->find($restStopId);

        if (! $restStop) {
            return null;
        }

        return $restStop
            ->services()
            ->orderBy('name')
            ->get();
    }

    public function upsertForUser(
        User $user,
        string $country,
        string $city,
        string $address,
        string $postCode,
        string $worksFrom,
        string $worksTo
    ): RestStop {
        return RestStop::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'country' => $country,
                'city' => $city,
                'address' => $address,
                'post_code' => $postCode,
                'works_from' => $worksFrom,
                'works_to' => $worksTo,
            ],
        )->refresh();
    }

    public function addServiceForUser(User $user, int $serviceId): ?RestStopServiceModel
    {
        $restStop = $this->findForUser($user);

        if (! $restStop) {
            return null;
        }

        return RestStopServiceModel::query()->firstOrCreate([
            'rest_stop_id' => $restStop->id,
            'service_id' => $serviceId,
        ]);
    }

    public function removeServiceForUser(User $user, int $serviceId): ?RestStopServiceModel
    {
        $restStop = $this->findForUser($user);

        if (! $restStop) {
            return null;
        }

        $restStopService = $restStop
            ->restStopServices()
            ->where('service_id', $serviceId)
            ->first();

        if (! $restStopService) {
            return null;
        }

        $restStop
            ->restStopServices()
            ->where('service_id', $serviceId)
            ->delete();

        return $restStopService;
    }
}
