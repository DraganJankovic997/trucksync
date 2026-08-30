<?php

namespace App\Contracts;

use App\Models\User;

interface AuthServiceContract
{
    public function register(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
    ): User;
}
