<?php

namespace App\Services;

use App\Contracts\UserManagementServiceContract;
use App\Models\Dispatcher;
use App\Models\RestStop;

class UserManagementService implements UserManagementServiceContract
{
    public function approveProfileForUser(int $userId): Dispatcher|RestStop|null
    {
        $profile = $this->findApprovableProfile($userId);

        if (! $profile) {
            return null;
        }

        $profile->is_approved = true;
        $profile->save();

        return $profile->refresh();
    }

    private function findApprovableProfile(int $userId): Dispatcher|RestStop|null
    {
        $dispatcher = Dispatcher::query()
            ->where('user_id', $userId)
            ->first();

        if ($dispatcher) {
            return $dispatcher;
        }

        return RestStop::query()
            ->where('user_id', $userId)
            ->first();
    }
}
