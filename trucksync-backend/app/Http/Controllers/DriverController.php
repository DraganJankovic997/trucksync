<?php

namespace App\Http\Controllers;

use App\Contracts\DriverServiceContract;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class DriverController extends Controller
{
    public function __construct(private readonly DriverServiceContract $driverService) {}

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
}
