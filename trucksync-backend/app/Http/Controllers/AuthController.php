<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceContract;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        try {
            $user = $this->authService->authenticate(
                $validated['email'],
                $validated['password'],
            );

            if (! $user) {
                return $this->unauthenticatedResponse('The provided credentials are invalid.');
            }

            $token = $this->authService->issueToken($user);
        } catch (Throwable $throwable) {
            logger()->error('Unable to log in user.', [
                'email' => $validated['email'],
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to log in.',
            ], 500);
        }

        return response()->json([
            'message' => 'Logged in successfully.',
            'data' => [
                'token' => $token,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->unauthenticatedResponse();
        }

        return response()->json([
            'data' => [
                'user' => $this->userPayload($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->unauthenticatedResponse();
        }

        try {
            $this->authService->revokeCurrentToken($user);
        } catch (Throwable $throwable) {
            logger()->error('Unable to log out user.', [
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to log out.',
            ], 500);
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * @return array{id: int, first_name: string|null, last_name: string|null, email: string}
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ];
    }

    private function unauthenticatedResponse(string $message = 'Unauthenticated.'): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 401);
    }
}
