<?php

namespace App\Http\Controllers;

use App\Contracts\UserServiceContract;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class UserController extends Controller
{
    public function __construct(private readonly UserServiceContract $userService) {}

    public function update(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($authenticatedUser->id),
            ],
            'country' => ['required', 'string', 'max:255', Rule::exists('countries', 'name')],
            'phone_number' => ['required', 'string', 'max:30', 'min:10'],
            'profile_type' => ['required', 'string', Rule::in(['driver', 'dispatcher', 'rest_stop'])],
        ]);

        try {
            $user = $this->userService->update(
                $authenticatedUser,
                trim($validated['first_name']),
                trim($validated['last_name']),
                strtolower(trim($validated['email'])),
                trim($validated['country']),
                trim($validated['phone_number']),
                trim($validated['profile_type']),
            );

            return response()->json([
                'message' => 'User updated successfully.',
                'data' => [
                    'user' => $this->userPayload($user),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to update user.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to update user.',
            ], 500);
        }
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
