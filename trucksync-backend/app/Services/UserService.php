<?php

namespace App\Services;

use App\Contracts\UserServiceContract;
use App\Models\User;

class UserService implements UserServiceContract
{
    public function update(
        User $user,
        string $firstName,
        string $lastName,
        string $email,
        string $country,
        string $phoneNumber,
    ): User {
        $user->fill([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'country' => $country,
            'phone_number' => $phoneNumber,
        ]);

        $hasProfileChanges = $user->isDirty();

        $user->save();

        if ($hasProfileChanges) {
            $this->resetProfileApproval($user);
        }

        return $user->refresh();
    }

    private function resetProfileApproval(User $user): void
    {
        if ($user->profile_type === 'dispatcher') {
            $user->dispatcher()->update([
                'is_approved' => false,
            ]);
        }

        if ($user->profile_type === 'rest_stop') {
            $user->restStop()->update([
                'is_approved' => false,
            ]);
        }
    }
}
