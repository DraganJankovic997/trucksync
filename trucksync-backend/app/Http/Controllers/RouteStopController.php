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
use Illuminate\Validation\ValidationException;
use Throwable;

class RouteStopController extends Controller
{
    /**
     * @var list<string>
     */
    private const UNFULFILLED_SORT_KEYS = [
        'id',
        'route_id',
        'location',
        'description',
        'stop_at',
        'number_of_trucks',
        'number_of_drivers',
    ];

    public function __construct(private readonly RouteStopServiceContract $routeStopService) {}

    public function show(int $routeStopId): JsonResponse
    {
        try {
            $routeStop = $this->routeStopService->findWithServices($routeStopId);

            if (! $routeStop) {
                return response()->json([
                    'message' => 'Route stop not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'route_stop' => $this->routeStopPayload($routeStop),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch route stop.', [
                'route_stop_id' => $routeStopId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch route stop.',
            ], 500);
        }
    }

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

    public function indexUnfulfilled(Request $request): JsonResponse
    {
        $queryParameters = $this->unfulfilledRouteStopsQueryParameters($request);

        validator($queryParameters, [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'page' => ['required', 'integer', 'min:1'],
            'per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'sortBy\.key' => ['required', 'string', Rule::in(self::UNFULFILLED_SORT_KEYS)],
            'sortBy\.order' => ['required', 'string', Rule::in(['asc', 'desc'])],
        ])->validate();

        try {
            $routeStops = $this->routeStopService->unfulfilled(
                $queryParameters['search'],
                (int) $queryParameters['per_page'],
                (int) $queryParameters['page'],
                $queryParameters['sortBy.key'],
                $queryParameters['sortBy.order'],
            );
            $routeStops->appends($request->query());

            return response()->json([
                'data' => [
                    'route_stops' => $routeStops->getCollection()
                        ->map(fn (RouteStop $routeStop): array => $this->routeStopPayloadWithDispatcher($routeStop))
                        ->values()
                        ->all(),
                ],
                'links' => [
                    'first' => $routeStops->url(1),
                    'last' => $routeStops->url($routeStops->lastPage()),
                    'prev' => $routeStops->previousPageUrl(),
                    'next' => $routeStops->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $routeStops->currentPage(),
                    'from' => $routeStops->firstItem(),
                    'last_page' => $routeStops->lastPage(),
                    'path' => $routeStops->path(),
                    'per_page' => $routeStops->perPage(),
                    'to' => $routeStops->lastItem(),
                    'total' => $routeStops->total(),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch unfulfilled route stops.', [
                'user_id' => $request->user()->id,
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
            'stop_at' => ['required', 'date', 'after:now'],
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
                $validated['stop_at'],
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
        } catch (ValidationException $exception) {
            throw $exception;
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
        } catch (ValidationException $exception) {
            throw $exception;
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

    public function fulfill(Request $request, int $routeStopId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can fulfill route stops.',
            ], 403);
        }

        $validated = $request->validate([
            'rest_stop_id' => ['required', 'integer', 'min:1', Rule::exists('rest_stops', 'id')],
        ]);

        try {
            $routeStop = $this->routeStopService->fulfillForUser(
                $authenticatedUser,
                $routeStopId,
                $validated['rest_stop_id'],
            );

            return response()->json([
                'message' => 'Route stop fulfilled successfully.',
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
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            logger()->error('Unable to fulfill route stop.', [
                'user_id' => $authenticatedUser->id,
                'route_stop_id' => $routeStopId,
                'rest_stop_id' => $validated['rest_stop_id'],
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fulfill route stop.',
            ], 500);
        }
    }

    /**
     * @return array{id: int, route_id: int, location: string|null, description: string|null, stop_at: string, fulfiled_at: string|null, fulfiled_by: int|null, accepted_bid_price: string|null, number_of_trucks: int, number_of_drivers: int, bids_count: int, services: array<int, array{id: int, name: string, measurement_unit: string|null, quantity: int}>}
     */
    private function routeStopPayload(RouteStop $routeStop): array
    {
        return [
            'id' => $routeStop->id,
            'route_id' => $routeStop->route_id,
            'location' => $routeStop->location,
            'description' => $routeStop->description,
            'stop_at' => $routeStop->stop_at->toJSON(),
            'fulfiled_at' => $routeStop->fulfiled_at?->toJSON(),
            'fulfiled_by' => $routeStop->fulfiled_by,
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

    /**
     * @return array{id: int, route_id: int, dispatcher_company_name: string|null, location: string|null, description: string|null, stop_at: string, fulfiled_at: string|null, fulfiled_by: int|null, accepted_bid_price: string|null, number_of_trucks: int, number_of_drivers: int, bids_count: int, services: array<int, array{id: int, name: string, measurement_unit: string|null, quantity: int}>}
     */
    private function routeStopPayloadWithDispatcher(RouteStop $routeStop): array
    {
        return [
            ...$this->routeStopPayload($routeStop),
            'dispatcher_company_name' => $routeStop->route?->dispatcher?->company_name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function unfulfilledRouteStopsQueryParameters(Request $request): array
    {
        $queryParameters = $request->query();
        $sortBy = $queryParameters['sortBy'] ?? null;

        $queryParameters['sortBy.key'] ??= $queryParameters['sortBy_key'] ?? null;
        $queryParameters['sortBy.order'] ??= $queryParameters['sortBy_order'] ?? null;

        if (is_array($sortBy)) {
            $queryParameters['sortBy.key'] ??= $sortBy['key'] ?? null;
            $queryParameters['sortBy.order'] ??= $sortBy['order'] ?? null;
        }

        if (! array_key_exists('search', $queryParameters) || $queryParameters['search'] === null) {
            $queryParameters['search'] = null;
        } elseif (is_string($queryParameters['search'])) {
            $queryParameters['search'] = trim($queryParameters['search']);
            $queryParameters['search'] = $queryParameters['search'] !== '' ? $queryParameters['search'] : null;
        }

        $queryParameters['page'] ??= 1;
        $queryParameters['per_page'] ??= 15;

        if (! array_key_exists('sortBy.key', $queryParameters) || $queryParameters['sortBy.key'] === null) {
            $queryParameters['sortBy.key'] = 'stop_at';
        } elseif (is_string($queryParameters['sortBy.key'])) {
            $queryParameters['sortBy.key'] = strtolower(trim($queryParameters['sortBy.key']));
            $queryParameters['sortBy.key'] = $queryParameters['sortBy.key'] !== '' ? $queryParameters['sortBy.key'] : 'stop_at';
        }

        if (! array_key_exists('sortBy.order', $queryParameters) || $queryParameters['sortBy.order'] === null) {
            $queryParameters['sortBy.order'] = 'desc';
        } elseif (is_string($queryParameters['sortBy.order'])) {
            $queryParameters['sortBy.order'] = strtolower(trim($queryParameters['sortBy.order']));
            $queryParameters['sortBy.order'] = $queryParameters['sortBy.order'] !== '' ? $queryParameters['sortBy.order'] : 'desc';
        }

        return $queryParameters;
    }

    private function pricePayload(mixed $price): ?string
    {
        if ($price === null) {
            return null;
        }

        return number_format((float) $price, 2, '.', '');
    }
}
