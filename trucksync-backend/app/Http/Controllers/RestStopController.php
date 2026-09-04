<?php

namespace App\Http\Controllers;

use App\Contracts\RestStopServiceContract;
use App\Models\RestStop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class RestStopController extends Controller
{
    public function __construct(private readonly RestStopServiceContract $restStopService) {}

    public function show(Request $request): JsonResponse
    {
        try {
            $restStop = $this->restStopService->findForUser($request->user());

            if (! $restStop) {
                return response()->json([
                    'message' => 'Rest stop profile not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'rest_stop' => $this->restStopPayload($restStop),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch rest stop profile.', [
                'user_id' => $request->user()->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch rest stop profile.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'rest_stop') {
            return response()->json([
                'message' => 'Only rest stop users can create or update rest stop profiles.',
            ], 403);
        }

        $validated = $request->validate([
            'country' => ['required', 'string', 'max:255', Rule::exists('countries', 'name')],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'post_code' => ['required', 'string', 'max:255'],
            'works_from' => ['required', 'date_format:H:i'],
            'works_to' => ['required', 'date_format:H:i'],
        ]);

        try {
            $restStop = $this->restStopService->upsertForUser(
                $authenticatedUser,
                trim($validated['country']),
                trim($validated['city']),
                trim($validated['address']),
                trim($validated['post_code']),
                $validated['works_from'],
                $validated['works_to'],
            );

            return response()->json([
                'message' => $restStop->wasRecentlyCreated
                    ? 'Rest stop profile created successfully.'
                    : 'Rest stop profile updated successfully.',
                'data' => [
                    'rest_stop' => $this->restStopPayload($restStop),
                ],
            ], $restStop->wasRecentlyCreated ? 201 : 200);
        } catch (Throwable $throwable) {
            logger()->error('Unable to save rest stop profile.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to save rest stop profile.',
            ], 500);
        }
    }

    /**
     * @return array{id: int, user_id: int, country: string, city: string, address: string, post_code: string, works_from: string, works_to: string}
     */
    private function restStopPayload(RestStop $restStop): array
    {
        return [
            'id' => $restStop->id,
            'user_id' => $restStop->user_id,
            'country' => $restStop->country,
            'city' => $restStop->city,
            'address' => $restStop->address,
            'post_code' => $restStop->post_code,
            'works_from' => $restStop->works_from,
            'works_to' => $restStop->works_to,
        ];
    }
}
