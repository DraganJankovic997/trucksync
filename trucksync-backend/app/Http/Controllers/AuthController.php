<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly AuthServiceContract $authService) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = $this->authService->register(
                $validated['first_name'],
                $validated['last_name'],
                $validated['email'],
                $validated['password'],
            );
        } catch (Throwable $throwable) {
            logger()->error('Unable to register user.', [
                'email' => $validated['email'],
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to create account.',
            ], 500);
        }

        return response()->json([
            'message' => 'Account created successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
            ],
        ], 201);
    }
}
