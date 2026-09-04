<?php

namespace App\Http\Controllers;

use App\Contracts\DispatcherServiceContract;
use App\Models\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class DispatcherController extends Controller
{
    public function __construct(private readonly DispatcherServiceContract $dispatcherService) {}

    public function store(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can create or update dispatcher profiles.',
            ], 403);
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255', Rule::exists('countries', 'name')],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'post_code' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:255'],
        ]);

        try {
            $dispatcher = $this->dispatcherService->upsertForUser(
                $authenticatedUser,
                trim($validated['company_name']),
                trim($validated['country']),
                trim($validated['city']),
                trim($validated['address']),
                trim($validated['post_code']),
                trim($validated['registration_number']),
            );

            return response()->json([
                'message' => $dispatcher->wasRecentlyCreated
                    ? 'Dispatcher profile created successfully.'
                    : 'Dispatcher profile updated successfully.',
                'data' => [
                    'dispatcher' => $this->dispatcherPayload($dispatcher),
                ],
            ], $dispatcher->wasRecentlyCreated ? 201 : 200);
        } catch (Throwable $throwable) {
            logger()->error('Unable to save dispatcher profile.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to save dispatcher profile.',
            ], 500);
        }
    }

    /**
     * @return array{id: int, user_id: int, company_name: string, country: string, city: string, address: string, post_code: string, registration_number: string}
     */
    private function dispatcherPayload(Dispatcher $dispatcher): array
    {
        return [
            'id' => $dispatcher->id,
            'user_id' => $dispatcher->user_id,
            'company_name' => $dispatcher->company_name,
            'country' => $dispatcher->country,
            'city' => $dispatcher->city,
            'address' => $dispatcher->address,
            'post_code' => $dispatcher->post_code,
            'registration_number' => $dispatcher->registration_number,
        ];
    }
}
