<?php

namespace App\Http\Controllers;

use App\Contracts\DriverServiceContract;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class DriverController extends Controller
{
    public function __construct(private readonly DriverServiceContract $driverService) {}

    public function indexForDispatcher(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can view drivers.',
            ], 403);
        }

        $validated = $request->validate([
            'available_for_route' => ['nullable', 'integer', 'min:1', Rule::exists('routes', 'id')],
        ]);
        $availableForRouteId = isset($validated['available_for_route'])
            ? (int) $validated['available_for_route']
            : null;

        try {
            $drivers = $this->driverService->forDispatcherUser(
                $authenticatedUser,
                $availableForRouteId
            );

            if ($drivers === null) {
                return response()->json([
                    'message' => 'Dispatcher profile not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'drivers' => $drivers
                        ->map(fn (Driver $driver): array => $this->driverWithUserPayload($driver))
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (RouteNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (RouteNotOwnedByDispatcherException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch dispatcher drivers.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch drivers.',
            ], 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $driver = $this->driverService->findForUser($request->user());

            if (! $driver) {
                return response()->json([
                    'message' => 'Driver profile not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'driver' => $this->driverPayload($driver),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch driver profile.', [
                'user_id' => $request->user()->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch driver profile.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'driver') {
            return response()->json([
                'message' => 'Only driver users can create or update driver profiles.',
            ], 403);
        }

        $request->merge([
            'license_number' => is_string($request->input('license_number'))
                ? trim($request->input('license_number'))
                : $request->input('license_number'),
        ]);

        $currentDriver = $authenticatedUser->driver;

        $validated = $request->validate([
            'license_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('drivers', 'license_number')->ignore($currentDriver?->id),
            ],
            'dispatcher_id' => ['nullable', 'integer', Rule::exists('dispatchers', 'id')],
        ]);

        try {
            $driver = $this->driverService->upsertForUser(
                $authenticatedUser,
                $validated['license_number'],
                $validated['dispatcher_id'] ?? null,
            );

            return response()->json([
                'message' => $driver->wasRecentlyCreated
                    ? 'Driver profile created successfully.'
                    : 'Driver profile updated successfully.',
                'data' => [
                    'driver' => $this->driverPayload($driver),
                ],
            ], $driver->wasRecentlyCreated ? 201 : 200);
        } catch (Throwable $throwable) {
            logger()->error('Unable to save driver profile.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to save driver profile.',
            ], 500);
        }
    }

    /**
     * @return array{id: int, user_id: int, dispatcher_id: int|null, license_number: string, is_dispatcher_approved: bool}
     */
    private function driverPayload(Driver $driver): array
    {
        return [
            'id' => $driver->id,
            'user_id' => $driver->user_id,
            'dispatcher_id' => $driver->dispatcher_id,
            'license_number' => $driver->license_number,
            'is_dispatcher_approved' => $driver->is_dispatcher_approved,
        ];
    }

    /**
     * @return array{id: int, user_id: int, dispatcher_id: int|null, license_number: string, is_dispatcher_approved: bool, user: array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}}
     */
    private function driverWithUserPayload(Driver $driver): array
    {
        $payload = [
            ...$this->driverPayload($driver),
            'user' => $this->userPayload($driver->user),
        ];

        if ($driver->getAttribute('is_available') !== null) {
            $payload['is_available'] = (bool) $driver->getAttribute('is_available');
        }

        return $payload;
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
