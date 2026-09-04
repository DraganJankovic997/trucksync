<?php

namespace App\Http\Controllers;

use App\Contracts\ServiceServiceContract;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceServiceContract $serviceService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $services = $this->serviceService->all();

            return response()->json([
                'data' => [
                    'services' => $services
                        ->map(fn (Service $service): array => $this->servicePayload($service))
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch services.', [
                'user_id' => $request->user()->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch services.',
            ], 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $service = $this->serviceService->find($id);

            if (! $service) {
                return response()->json([
                    'message' => 'Service not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'service' => $this->servicePayload($service),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch service.', [
                'user_id' => $request->user()->id,
                'service_id' => $id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch service.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('services', 'name')],
        ]);

        try {
            $service = $this->serviceService->create($validated['name']);

            return response()->json([
                'message' => 'Service created successfully.',
                'data' => [
                    'service' => $this->servicePayload($service),
                ],
            ], 201);
        } catch (Throwable $throwable) {
            logger()->error('Unable to create service.', [
                'user_id' => $request->user()->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to create service.',
            ], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $service = $this->serviceService->find($id);

            if (! $service) {
                return response()->json([
                    'message' => 'Service not found.',
                ], 404);
            }

            $this->serviceService->delete($service);

            return response()->json([
                'message' => 'Service deleted successfully.',
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to delete service.', [
                'user_id' => $request->user()->id,
                'service_id' => $id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to delete service.',
            ], 500);
        }
    }

    /**
     * @return array{id: int, name: string}
     */
    private function servicePayload(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
        ];
    }
}
