<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceContract;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'profile_type' => ['required', 'string', Rule::in(User::PROFILE_TYPES)],
        ]);

        try {
            $user = $this->authService->register(
                $validated['first_name'],
                $validated['last_name'],
                $validated['email'],
                $validated['password'],
                $validated['profile_type'],
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
                'user' => $this->userPayload($user),
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
            $token = $this->authService->authenticate(
                $validated['email'],
                $validated['password'],
            );

            return response()->json([
                'message' => 'Logged in successfully.',
                'data' => [
                    'token' => $token,
                ],
            ]);
        } catch (UserNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (InvalidCredentialsException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 401);
        } catch (Throwable $throwable) {
            logger()->error('Unable to log in user.', [
                'email' => $validated['email'],
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to log in.',
            ], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => $this->userPayload($request->user()),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->revokeCurrentToken($request->user());
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
     * @return array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'country' => $user->country,
            'phone_number' => $user->phone_number,
            'profile_type' => $user->profile_type,
        ];
    }
}
