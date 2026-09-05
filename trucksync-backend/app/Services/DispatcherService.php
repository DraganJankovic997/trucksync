<?php

namespace App\Services;

use App\Contracts\DispatcherServiceContract;
use App\Models\Dispatcher;
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
        return Dispatcher::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $companyName,
                'city' => $city,
                'address' => $address,
                'post_code' => $postCode,
                'registration_number' => $registrationNumber,
            ],
        )->refresh();
    }
}
