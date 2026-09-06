<?php

namespace App\Http\Controllers;

use App\Contracts\RouteStopServiceContract;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\RouteNotOwnedByDispatcherException;
use App\Models\RouteStop;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class RouteStopController extends Controller
{
    public function __construct(private readonly RouteStopServiceContract $routeStopService) {}

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

    /**
     * @return array{id: int, route_id: int, number_of_trucks: int, number_of_drivers: int, services: array<int, array{id: int, name: string, measurement_unit: string|null, quantity: int}>}
     */
    private function routeStopPayload(RouteStop $routeStop): array
    {
        return [
            'id' => $routeStop->id,
            'route_id' => $routeStop->route_id,
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
