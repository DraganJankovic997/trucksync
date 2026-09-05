<?php

namespace App\Http\Controllers;

use App\Contracts\DispatcherServiceContract;
use App\Models\Route as DispatcherRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RouteController extends Controller
{
    public function __construct(private readonly DispatcherServiceContract $dispatcherService) {}

    public function store(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can create routes.',
            ], 403);
        }

        $validated = $request->validate([
            'origin' => ['required', 'string', 'min:1'],
            'destination' => ['required', 'string', 'min:1'],
            'planned_travel_details' => ['nullable', 'string'],
            'convoy_size' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            $route = $this->dispatcherService->createRouteForUser(
                $authenticatedUser,
                trim($validated['origin']),
                trim($validated['destination']),
                isset($validated['planned_travel_details']) ? trim($validated['planned_travel_details']) : null,
                $validated['convoy_size'],
                trim($validated['start_date']),
                trim($validated['end_date']),
            );

            if (! $route) {
                return response()->json([
                    'message' => 'Dispatcher profile not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Route created successfully.',
                'data' => [
                    'route' => $this->routePayload($route),
                ],
            ], 201);
        } catch (Throwable $throwable) {
            logger()->error('Unable to create dispatcher route.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to create route.',
            ], 500);
        }
    }

    public function close(Request $request, int $routeId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can close routes.',
            ], 403);
        }

        try {
            $route = $this->dispatcherService->closeRouteForUser(
                $authenticatedUser,
                $routeId,
            );

            if (! $route) {
                return response()->json([
                    'message' => 'Route not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Route closed successfully.',
                'data' => [
                    'route' => $this->routePayload($route),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to close dispatcher route.', [
                'user_id' => $authenticatedUser->id,
                'route_id' => $routeId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to close route.',
            ], 500);
        }
    }

    /**
     * @return array{id: int, dispatcher_id: int, origin: string, destination: string, planned_travel_details: string|null, convoy_size: int, start_date: string, end_date: string, closed_at: string|null}
     */
    private function routePayload(DispatcherRoute $route): array
    {
        return [
            'id' => $route->id,
            'dispatcher_id' => $route->dispatcher_id,
            'origin' => $route->origin,
            'destination' => $route->destination,
            'planned_travel_details' => $route->planned_travel_details,
            'convoy_size' => $route->convoy_size,
            'start_date' => $route->start_date->toDateString(),
            'end_date' => $route->end_date->toDateString(),
            'closed_at' => $route->closed_at?->toJSON(),
        ];
    }
}
