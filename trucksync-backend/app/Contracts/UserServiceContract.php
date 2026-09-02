<?php

namespace App\Contracts;

use App\Models\User;

interface UserServiceContract
{
    public function update(
        User $user,
        string $firstName,
        string $lastName,
        string $email,
        string $country,
        string $phoneNumber,
        string $profileType,
    ): User;
}
