<?php

namespace App\Services;

use App\Contracts\AuthServiceContract;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
            'email' => strtolower(trim($email)),
            'password' => $password,
        ]);
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = User::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function issueToken(User $user): string
    {
        return $user->createToken('token')->plainTextToken;
    }

    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
