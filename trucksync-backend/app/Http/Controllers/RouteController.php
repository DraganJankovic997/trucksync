<?php

namespace App\Http\Controllers;

use App\Contracts\RouteServiceContract;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RouteController extends Controller
{
    public function __construct(private readonly RouteServiceContract $routeService) {}

    public function index(Request $request, int $dispatcherId): JsonResponse
    {
        try {
            $routes = $this->routeService->forDispatcher($dispatcherId);

            if (! $routes) {
                return response()->json([
                    'message' => 'Dispatcher not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'routes' => $routes
                        ->map(fn (DispatcherRoute $route): array => $this->routePayload($route))
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch dispatcher routes.', [
                'user_id' => $request->user()->id,
                'dispatcher_id' => $dispatcherId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch routes.',
            ], 500);
        }
    }

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
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['required', 'date', 'after:today', 'after_or_equal:start_date'],
        ]);

        try {
            $route = $this->routeService->createForUser(
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

    public function show(int $route_id): JsonResponse
    {
        try {
            $route = $this->routeService->findWithStops($route_id);

            if (! $route) {
                return response()->json([
                    'message' => 'Route not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'route' => $this->routeWithStopsPayload($route),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch route.', [
                'route_id' => $route_id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch route.',
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
            $route = $this->routeService->closeForUser(
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

    /**
     * @return array{id: int, dispatcher_id: int, origin: string, destination: string, planned_travel_details: string|null, convoy_size: int, start_date: string, end_date: string, closed_at: string|null, route_stops: array<int, array{id: int, route_id: int, location: string|null, description: string|null, number_of_trucks: int, number_of_drivers: int, services: array<int, array{id: int, name: string, measurement_unit: string|null, quantity: int}>}>}
     */
    private function routeWithStopsPayload(DispatcherRoute $route): array
    {
        return [
            ...$this->routePayload($route),
            'route_stops' => $route
                ->routeStops
                ->map(fn (RouteStop $routeStop): array => $this->routeStopPayload($routeStop))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{id: int, route_id: int, location: string|null, description: string|null, number_of_trucks: int, number_of_drivers: int, services: array<int, array{id: int, name: string, measurement_unit: string|null, quantity: int}>}
     */
    private function routeStopPayload(RouteStop $routeStop): array
    {
        return [
            'id' => $routeStop->id,
            'route_id' => $routeStop->route_id,
            'location' => $routeStop->location,
            'description' => $routeStop->description,
            'number_of_trucks' => $routeStop->number_of_trucks,
            'number_of_drivers' => $routeStop->number_of_drivers,
            'services' => $routeStop
                ->services
                ->map(fn (Service $service): array => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'measurement_unit' => $service->measurement_unit,
                    'quantity' => $service->pivot->quantity,
                ])
                ->values()
                ->all(),
        ];
    }
}
