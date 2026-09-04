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

        $user->save();

        return $user->refresh();
    }
}
