<?php

namespace App\Services;

use App\Contracts\UserManagementServiceContract;
use App\Models\Dispatcher;
use App\Models\RestStop;

class UserManagementService implements UserManagementServiceContract
{
    public function profilesNeedingApproval(): array
    {
        return [
            'dispatchers' => Dispatcher::query()
                ->with('user')
                ->where('is_approved', false)
                ->orderBy('id')
                ->get(),
            'rest_stops' => RestStop::query()
                ->with('user')
                ->where('is_approved', false)
                ->orderBy('id')
                ->get(),
        ];
    }

    public function approveProfileForUser(int $userId): Dispatcher|RestStop|null
    {
        $profile = $this->findApprovableProfile($userId);

        if (! $profile) {
            return null;
        }

        $profile->is_approved = true;
        $profile->save();

        return $profile->refresh()->load('user');
    }

    private function findApprovableProfile(int $userId): Dispatcher|RestStop|null
    {
        $dispatcher = Dispatcher::query()
            ->with('user')
            ->where('user_id', $userId)
            ->first();

        if ($dispatcher) {
            return $dispatcher;
        }

        return RestStop::query()
            ->with('user')
            ->where('user_id', $userId)
            ->first();
    }
}
