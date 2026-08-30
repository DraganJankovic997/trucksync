<?php

namespace App\Services;

use App\Contracts\AuthServiceContract;
use App\Models\User;

class AuthService implements AuthServiceContract
{
    public function register(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
    ): User {
        return User::query()->create([
            'first_name' => trim($firstName),
            'last_name' => trim($lastName),
            'email' => strtolower($email),
            'password' => $password,
        ]);
    }
}
