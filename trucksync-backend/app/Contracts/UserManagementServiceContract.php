<?php

namespace App\Contracts;

use App\Models\Dispatcher;
use App\Models\RestStop;
use Illuminate\Database\Eloquent\Collection;

interface UserManagementServiceContract
{
    /**
     * @return array{dispatchers: Collection<int, Dispatcher>, rest_stops: Collection<int, RestStop>}
     */
    public function profilesNeedingApproval(): array;

    public function approveProfileForUser(int $userId): Dispatcher|RestStop|null;
}
