<?php

namespace App\Http\Controllers;

use App\Contracts\RouteServiceContract;
use App\Exceptions\InvalidRouteDriverAssignmentException;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Models\Driver;
use App\Models\Route as DispatcherRoute;
use App\Models\RouteStop;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

    public function indexForDriver(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'driver') {
            return response()->json([
                'message' => 'Only driver users can view their routes.',
            ], 403);
        }

        try {
            $routes = $this->routeService->forDriverUser($authenticatedUser);

            if (! $routes) {
                return response()->json([
                    'message' => 'Driver profile not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'routes' => $routes
                        ->map(fn (DispatcherRoute $route): array => $this->routeWithStopsPayload($route))
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch driver routes.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch routes.',
            ], 500);
        }
    }

    public function currentForDriver(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'driver') {
            return response()->json([
                'message' => 'Only driver users can view their routes.',
            ], 403);
        }

        try {
            $routes = $this->routeService->currentForDriverUser($authenticatedUser);

            if (! $routes) {
                return response()->json([
                    'message' => 'Driver profile not found.',
                ], 404);
            }

            $route = $routes->first();
            $nextRouteStop = $route?->routeStops
                ->first(fn (RouteStop $routeStop): bool => $routeStop->stop_at->greaterThanOrEqualTo(now()));

            return response()->json([
                'data' => [
                    'route' => $route ? $this->routeWithStopsPayload($route) : null,
                    'next_route_stop' => $nextRouteStop ? $this->routeStopPayload($nextRouteStop) : null,
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch current driver route.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch current route.',
            ], 500);
        }
    }

    public function upcomingForDriver(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'driver') {
            return response()->json([
                'message' => 'Only driver users can view their routes.',
            ], 403);
        }

        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);
        $limit = isset($validated['limit']) ? (int) $validated['limit'] : 3;

        try {
            $routes = $this->routeService->upcomingForDriverUser($authenticatedUser, $limit);

            if (! $routes) {
                return response()->json([
                    'message' => 'Driver profile not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'routes' => $routes
                        ->map(fn (DispatcherRoute $route): array => $this->routeSummaryPayload($route))
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch upcoming driver routes.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch upcoming routes.',
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

    public function syncDrivers(Request $request, int $routeId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can assign drivers to routes.',
            ], 403);
        }

        $validated = $request->validate([
            'drivers' => ['present', 'array'],
            'drivers.*.driver_id' => ['required', 'integer', 'distinct', 'min:1', 'exists:drivers,id'],
            'drivers.*.is_convoy_leader' => ['required', 'boolean'],
        ]);

        $driverAssignments = $this->driverAssignmentsPayload($validated['drivers']);
        $convoyLeaderCount = collect($driverAssignments)
            ->where('is_convoy_leader', true)
            ->count();

        if ($convoyLeaderCount > 1) {
            throw ValidationException::withMessages([
                'drivers' => 'Only one driver can be the convoy leader.',
            ]);
        }

        try {
            $route = $this->routeService->syncDriversForUser(
                $authenticatedUser,
                $routeId,
                $driverAssignments,
            );

            if (! $route) {
                return response()->json([
                    'message' => 'Dispatcher profile not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Route drivers updated successfully.',
                'data' => [
                    'route' => $this->routeWithStopsPayload($route),
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
        } catch (InvalidRouteDriverAssignmentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [
                    'drivers' => [
                        $exception->getMessage(),
                    ],
                ],
            ], 422);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            logger()->error('Unable to update route drivers.', [
                'user_id' => $authenticatedUser->id,
                'route_id' => $routeId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to update route drivers.',
            ], 500);
        }
    }

    /**
     * @return array{id: int, dispatcher_id: int, origin: string, destination: string, planned_travel_details: string|null, convoy_size: int, start_date: string, end_date: string, closed_at: string|null, drivers: array<int, array{id: int, user_id: int, dispatcher_id: int|null, license_number: string, is_dispatcher_approved: bool, is_convoy_leader: bool, user: array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}}>}
     */
    private function routePayload(DispatcherRoute $route): array
    {
        $route->loadMissing('drivers.user');

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
            'drivers' => $route->drivers
                ->map(fn (Driver $driver): array => $this->routeDriverPayload($driver))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{id: int, dispatcher_id: int, origin: string, destination: string, convoy_size: int, start_date: string, end_date: string}
     */
    private function routeSummaryPayload(DispatcherRoute $route): array
    {
        return [
            'id' => $route->id,
            'dispatcher_id' => $route->dispatcher_id,
            'origin' => $route->origin,
            'destination' => $route->destination,
            'convoy_size' => $route->convoy_size,
            'start_date' => $route->start_date->toDateString(),
            'end_date' => $route->end_date->toDateString(),
        ];
    }

    /**
     * @return array{id: int, dispatcher_id: int, origin: string, destination: string, planned_travel_details: string|null, convoy_size: int, start_date: string, end_date: string, closed_at: string|null, drivers: array<int, array{id: int, user_id: int, dispatcher_id: int|null, license_number: string, is_dispatcher_approved: bool, is_convoy_leader: bool, user: array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}}>, route_stops: array<int, array{id: int, route_id: int, location: string|null, description: string|null, stop_at: string, fulfilled_at: string|null, fulfilled_by: int|null, accepted_bid_price: string|null, number_of_trucks: int, number_of_drivers: int, bids_count: int, services: array<int, array{id: int, name: string, measurement_unit: string|null, quantity: int}>}>}
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
     * @return array{id: int, route_id: int, location: string|null, description: string|null, stop_at: string, fulfilled_at: string|null, fulfilled_by: int|null, accepted_bid_price: string|null, number_of_trucks: int, number_of_drivers: int, bids_count: int, services: array<int, array{id: int, name: string, measurement_unit: string|null, quantity: int}>}
     */
    private function routeStopPayload(RouteStop $routeStop): array
    {
        return [
            'id' => $routeStop->id,
            'route_id' => $routeStop->route_id,
            'location' => $routeStop->location,
            'description' => $routeStop->description,
            'stop_at' => $routeStop->stop_at->toJSON(),
            'fulfilled_at' => $routeStop->fulfilled_at?->toJSON(),
            'fulfilled_by' => $routeStop->fulfilled_by,
            'accepted_bid_price' => $this->pricePayload($routeStop->accepted_bid_price),
            'number_of_trucks' => $routeStop->number_of_trucks,
            'number_of_drivers' => $routeStop->number_of_drivers,
            'bids_count' => (int) ($routeStop->bids_count ?? $routeStop->routeStopBids()->count()),
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

    private function pricePayload(mixed $price): ?string
    {
        if ($price === null) {
            return null;
        }

        return number_format((float) $price, 2, '.', '');
    }

    /**
     * @param  array<int, array{driver_id: int|string, is_convoy_leader: bool|int|string}>  $drivers
     * @return array<int, array{driver_id: int, is_convoy_leader: bool}>
     */
    private function driverAssignmentsPayload(array $drivers): array
    {
        return collect($drivers)
            ->map(fn (array $driver): array => [
                'driver_id' => (int) $driver['driver_id'],
                'is_convoy_leader' => filter_var(
                    $driver['is_convoy_leader'],
                    FILTER_VALIDATE_BOOLEAN
                ),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, user_id: int, dispatcher_id: int|null, license_number: string, is_dispatcher_approved: bool, is_convoy_leader: bool, user: array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}}
     */
    private function routeDriverPayload(Driver $driver): array
    {
        return [
            'id' => $driver->id,
            'user_id' => $driver->user_id,
            'dispatcher_id' => $driver->dispatcher_id,
            'license_number' => $driver->license_number,
            'is_dispatcher_approved' => $driver->is_dispatcher_approved,
            'is_convoy_leader' => (bool) $driver->pivot?->is_convoy_leader,
            'user' => $this->userPayload($driver->user),
        ];
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
