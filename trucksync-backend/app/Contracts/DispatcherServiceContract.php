<?php

namespace App\Contracts;

use App\Models\Dispatcher;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface DispatcherServiceContract
{
    /**
     * @return Collection<int, Dispatcher>
     */
    public function all(): Collection;

    public function findForUser(User $user): ?Dispatcher;

    public function upsertForUser(
        User $user,
        string $companyName,
        string $city,
        string $address,
        string $postCode,
        string $registrationNumber
    ): Dispatcher;
}
