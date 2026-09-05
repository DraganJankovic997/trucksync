<?php

namespace App\Contracts;

use App\Models\Dispatcher;
use App\Models\RestStop;

interface UserManagementServiceContract
{
    public function approveProfileForUser(int $userId): Dispatcher|RestStop|null;
}
