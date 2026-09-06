<?php

namespace App\Http\Controllers;

use App\Contracts\RouteStopServiceContract;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Exceptions\RouteStopNotFoundException;
use App\Exceptions\RouteStopNotOwnedByDispatcherException;
use App\Models\RouteStop;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class RouteStopController extends Controller
{
    public function __construct(private readonly RouteStopServiceContract $routeStopService) {}

    public function index(int $route_id): JsonResponse
    {
        try {
            $routeStops = $this->routeStopService->forRoute($route_id);

            return response()->json([
                'data' => [
                    'route_stops' => $routeStops
                        ->map(fn (RouteStop $routeStop): array => $this->routeStopPayload($routeStop))
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (RouteNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch route stops.', [
                'route_id' => $route_id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch route stops.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can create route stops.',
            ], 403);
        }

        $validated = $request->validate([
            'route_id' => ['required', 'integer', 'min:1'],
            'location' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string'],
            'number_of_trucks' => ['required', 'integer', 'min:1'],
            'number_of_drivers' => ['required', 'integer', 'min:1'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer', 'distinct', Rule::exists('services', 'id')],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $routeStop = $this->routeStopService->createForUser(
                $authenticatedUser,
                $validated['route_id'],
                trim($validated['location']),
                isset($validated['description']) ? trim($validated['description']) : null,
                $validated['number_of_trucks'],
                $validated['number_of_drivers'],
                $validated['services'],
            );

            return response()->json([
                'message' => 'Route stop created successfully.',
                'data' => [
                    'route_stop' => $this->routeStopPayload($routeStop),
                ],
            ], 201);
        } catch (RouteNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (RouteNotOwnedByDispatcherException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        } catch (Throwable $throwable) {
            logger()->error('Unable to create route stop.', [
                'user_id' => $authenticatedUser->id,
                'route_id' => $validated['route_id'],
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to create route stop.',
            ], 500);
        }
    }

    public function syncServices(Request $request, int $routeStopId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can update route stop services.',
            ], 403);
        }

        $validated = $request->validate([
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer', 'distinct', Rule::exists('services', 'id')],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $routeStop = RouteStop::query()
                ->with('route.dispatcher')
                ->find($routeStopId);

            if (! $routeStop) {
                throw new RouteStopNotFoundException;
            }

            if ($routeStop->route?->dispatcher?->user_id !== $authenticatedUser->id) {
                throw new RouteStopNotOwnedByDispatcherException;
            }

            $routeStop = $this->routeStopService->syncServicesForRouteStop(
                $routeStop,
                $validated['services'],
            );

            return response()->json([
                'message' => 'Route stop services updated successfully.',
                'data' => [
                    'route_stop' => $this->routeStopPayload($routeStop),
                ],
            ]);
        } catch (RouteStopNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (RouteStopNotOwnedByDispatcherException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        } catch (Throwable $throwable) {
            logger()->error('Unable to update route stop services.', [
                'user_id' => $authenticatedUser->id,
                'route_stop_id' => $routeStopId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to update route stop services.',
            ], 500);
        }
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
