<?php

namespace App\Services;

use App\Contracts\AuthServiceContract;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceContract
{
    public function register(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $profileType,
    ): User {
        return User::query()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'profile_type' => $profileType,
        ]);
    }

    public function authenticate(string $email, string $password): string
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            throw new UserNotFoundException;
        }

        if (! Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        return $this->issueToken($user);
    }

    private function issueToken(User $user): string
    {
        return $user->createToken('token')->plainTextToken;
    }

    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
