<?php

namespace App\Http\Controllers;

use App\Contracts\RestStopServiceContract;
use App\Models\RestStop;
use App\Models\RestStopService as RestStopServiceModel;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class RestStopController extends Controller
{
    public function __construct(private readonly RestStopServiceContract $restStopService) {}

    public function indexServices(int $id): JsonResponse
    {
        try {
            $services = $this->restStopService->servicesForRestStop($id);

            if (! $services) {
                return response()->json([
                    'message' => 'Rest stop not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'services' => $services
                        ->map(fn (Service $service): array => $this->servicePayload($service))
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch rest stop services.', [
                'rest_stop_id' => $id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch rest stop services.',
            ], 500);
        }
    }

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
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'post_code' => ['required', 'string', 'max:255'],
            'works_from' => ['required', 'date_format:H:i'],
            'works_to' => ['required', 'date_format:H:i'],
        ]);

        try {
            $restStop = $this->restStopService->upsertForUser(
                $authenticatedUser,
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

    public function storeService(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'rest_stop') {
            return response()->json([
                'message' => 'Only rest stop users can add rest stop services.',
            ], 403);
        }

        $validated = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
        ]);

        try {
            $restStopService = $this->restStopService->addServiceForUser(
                $authenticatedUser,
                $validated['service_id'],
            );

            if (! $restStopService) {
                return response()->json([
                    'message' => 'Rest stop profile not found.',
                ], 404);
            }

            return response()->json([
                'message' => $restStopService->wasRecentlyCreated
                    ? 'Rest stop service added successfully.'
                    : 'Rest stop service already exists.',
                'data' => [
                    'rest_stop_service' => $this->restStopServicePayload($restStopService),
                ],
            ], $restStopService->wasRecentlyCreated ? 201 : 200);
        } catch (Throwable $throwable) {
            logger()->error('Unable to add rest stop service.', [
                'user_id' => $authenticatedUser->id,
                'service_id' => $validated['service_id'],
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to add rest stop service.',
            ], 500);
        }
    }

    public function destroyService(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'rest_stop') {
            return response()->json([
                'message' => 'Only rest stop users can remove rest stop services.',
            ], 403);
        }

        $validated = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
        ]);

        try {
            if (! $this->restStopService->findForUser($authenticatedUser)) {
                return response()->json([
                    'message' => 'Rest stop profile not found.',
                ], 404);
            }

            $restStopService = $this->restStopService->removeServiceForUser(
                $authenticatedUser,
                $validated['service_id'],
            );

            if (! $restStopService) {
                return response()->json([
                    'message' => 'Rest stop service not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Rest stop service removed successfully.',
                'data' => [
                    'rest_stop_service' => $this->restStopServicePayload($restStopService),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to remove rest stop service.', [
                'user_id' => $authenticatedUser->id,
                'service_id' => $validated['service_id'],
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to remove rest stop service.',
            ], 500);
        }
    }

    /**
     * @return array{id: int, user_id: int, city: string, address: string, post_code: string, works_from: string, works_to: string}
     */
    private function restStopPayload(RestStop $restStop): array
    {
        return [
            'id' => $restStop->id,
            'user_id' => $restStop->user_id,
            'city' => $restStop->city,
            'address' => $restStop->address,
            'post_code' => $restStop->post_code,
            'works_from' => $this->timePayload($restStop->works_from),
            'works_to' => $this->timePayload($restStop->works_to),
        ];
    }

    private function timePayload(string $time): string
    {
        return substr($time, 0, 5);
    }

    /**
     * @return array{rest_stop_id: int, service_id: int}
     */
    private function restStopServicePayload(RestStopServiceModel $restStopService): array
    {
        return [
            'rest_stop_id' => $restStopService->rest_stop_id,
            'service_id' => $restStopService->service_id,
        ];
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
