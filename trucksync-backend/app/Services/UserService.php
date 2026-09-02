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
        string $profileType,
    ): User {
        $user->fill([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'country' => $country,
            'phone_number' => $phoneNumber,
            'profile_type' => $profileType,
        ]);

        $user->save();

        return $user->refresh();
    }
}
