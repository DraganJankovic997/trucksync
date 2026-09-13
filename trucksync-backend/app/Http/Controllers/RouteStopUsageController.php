<?php

namespace App\Http\Controllers;

use App\Contracts\RouteStopUsageServiceContract;
use App\Exceptions\RouteStopNotFoundException;
use App\Exceptions\RouteStopUsageNotAllowedException;
use App\Models\RouteStopUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class RouteStopUsageController extends Controller
{
    public function __construct(private readonly RouteStopUsageServiceContract $routeStopUsageService) {}

    public function indexForAdmin(Request $request): JsonResponse
    {
        $queryParameters = $this->adminRatingsQueryParameters($request);

        validator($queryParameters, [
            'rest_stop_id' => ['sometimes', 'nullable', 'integer', 'min:1', Rule::exists('rest_stops', 'id')],
            'page' => ['required', 'integer', 'min:1'],
            'per_page' => ['required', 'integer', 'min:1', 'max:100'],
        ])->validate();

        try {
            $routeStopUsages = $this->routeStopUsageService->ratingsForAdmin(
                $this->optionalRestStopId($queryParameters),
                (int) $queryParameters['per_page'],
                (int) $queryParameters['page'],
            );
            $routeStopUsages->appends($request->query());

            return response()->json([
                'data' => [
                    'route_stop_usages' => $routeStopUsages->getCollection()
                        ->map(fn (RouteStopUsage $routeStopUsage): array => $this->routeStopUsagePayload($routeStopUsage))
                        ->values()
                        ->all(),
                ],
                'links' => [
                    'first' => $routeStopUsages->url(1),
                    'last' => $routeStopUsages->url($routeStopUsages->lastPage()),
                    'prev' => $routeStopUsages->previousPageUrl(),
                    'next' => $routeStopUsages->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $routeStopUsages->currentPage(),
                    'from' => $routeStopUsages->firstItem(),
                    'last_page' => $routeStopUsages->lastPage(),
                    'path' => $routeStopUsages->path(),
                    'per_page' => $routeStopUsages->perPage(),
                    'to' => $routeStopUsages->lastItem(),
                    'total' => $routeStopUsages->total(),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch route stop usage ratings.', [
                'user_id' => $request->user()->id,
                'rest_stop_id' => $queryParameters['rest_stop_id'] ?? null,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch route stop usage ratings.',
            ], 500);
        }
    }

    public function store(Request $request, int $routeStopId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'driver') {
            return response()->json([
                'message' => 'Only driver users can submit route stop usage reviews.',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:'.RouteStopUsage::MIN_RATING, 'max:'.RouteStopUsage::MAX_RATING],
            'report' => ['sometimes', 'nullable', 'string', 'max:'.RouteStopUsage::MAX_REPORT_LENGTH],
            'is_report' => ['sometimes', 'boolean'],
        ]);

        $report = $this->nullableTrimmedString($validated['report'] ?? null);
        $isReport = filter_var($validated['is_report'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($isReport && $report === null) {
            throw ValidationException::withMessages([
                'report' => 'A report description is required when reporting a rest stop.',
            ]);
        }

        try {
            $routeStopUsage = $this->routeStopUsageService->submitReviewForUser(
                $authenticatedUser,
                $routeStopId,
                (int) $validated['rating'],
                $report,
                $isReport,
            );

            if (! $routeStopUsage) {
                return response()->json([
                    'message' => 'Driver profile not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Route stop usage review submitted successfully.',
                'data' => [
                    'route_stop_usage' => $this->routeStopUsagePayload($routeStopUsage),
                ],
            ], $routeStopUsage->wasRecentlyCreated ? 201 : 200);
        } catch (RouteStopNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (RouteStopUsageNotAllowedException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            logger()->error('Unable to submit route stop usage review.', [
                'user_id' => $authenticatedUser->id,
                'route_stop_id' => $routeStopId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to submit route stop usage review.',
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function adminRatingsQueryParameters(Request $request): array
    {
        $queryParameters = $request->query();

        $queryParameters['page'] ??= 1;
        $queryParameters['per_page'] ??= 15;

        return $queryParameters;
    }

    /**
     * @param  array<string, mixed>  $queryParameters
     */
    private function optionalRestStopId(array $queryParameters): ?int
    {
        if (
            ! array_key_exists('rest_stop_id', $queryParameters)
            || $queryParameters['rest_stop_id'] === null
            || $queryParameters['rest_stop_id'] === ''
        ) {
            return null;
        }

        return (int) $queryParameters['rest_stop_id'];
    }

    /**
     * @return array{id: int, route_stop_id: int, driver_id: int, rest_stop_id: int|null, used_at: string, rating: int, report: string|null, is_report: bool, created_at: string|null, updated_at: string|null}
     */
    private function routeStopUsagePayload(RouteStopUsage $routeStopUsage): array
    {
        return [
            'id' => $routeStopUsage->id,
            'route_stop_id' => $routeStopUsage->route_stop_id,
            'driver_id' => $routeStopUsage->driver_id,
            'rest_stop_id' => $routeStopUsage->rest_stop_id,
            'used_at' => $routeStopUsage->used_at->toJSON(),
            'rating' => $routeStopUsage->rating,
            'report' => $routeStopUsage->report,
            'is_report' => $routeStopUsage->is_report,
            'created_at' => $routeStopUsage->created_at?->toJSON(),
            'updated_at' => $routeStopUsage->updated_at?->toJSON(),
        ];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
