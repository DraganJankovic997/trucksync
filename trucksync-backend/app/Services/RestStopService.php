<?php

namespace App\Services;

use App\Contracts\RestStopServiceContract;
use App\Models\RestStop;
use App\Models\User;

class RestStopService implements RestStopServiceContract
{
    public function findForUser(User $user): ?RestStop
    {
        return RestStop::query()
            ->where('user_id', $user->id)
            ->first();
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
}
