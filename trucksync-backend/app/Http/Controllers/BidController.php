<?php

namespace App\Http\Controllers;

use App\Contracts\BidServiceContract;
use App\Contracts\RestStopServiceContract;
use App\Exceptions\RouteStopNotFoundException;
use App\Exceptions\RouteStopNotOwnedByDispatcherException;
use App\Models\RestStop;
use App\Models\RouteStop;
use App\Models\RouteStopBid;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BidController extends Controller
{
    public function __construct(
        private readonly BidServiceContract $bidService,
        private readonly RestStopServiceContract $restStopService
    ) {}

    public function indexForDispatcherRouteStop(Request $request, int $routeStopId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'dispatcher') {
            return response()->json([
                'message' => 'Only dispatcher users can view route stop bids.',
            ], 403);
        }

        try {
            $routeStop = RouteStop::query()
                ->with('route.dispatcher')
                ->find($routeStopId);

            if (! $routeStop) {
                throw new RouteStopNotFoundException;
            }

            if ($routeStop->route?->dispatcher?->user_id !== $authenticatedUser->id) {
                throw new RouteStopNotOwnedByDispatcherException(
                    'You cannot view bids for a route stop on a route you did not create.'
                );
            }

            return response()->json([
                'data' => [
                    'bids' => $this->bidService
                        ->forRouteStop($routeStop)
                        ->map(fn (RouteStopBid $bid): array => $this->bidWithRestStopPayload($bid))
                        ->values()
                        ->all(),
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
            logger()->error('Unable to fetch route stop bids.', [
                'user_id' => $authenticatedUser->id,
                'route_stop_id' => $routeStopId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch route stop bids.',
            ], 500);
        }
    }

    public function indexForRestStop(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'rest_stop') {
            return response()->json([
                'message' => 'Only rest stop users can view bids.',
            ], 403);
        }

        try {
            $restStop = $this->restStopService->findForUser($authenticatedUser);

            if (! $restStop) {
                return response()->json([
                    'message' => 'Rest stop profile not found.',
                ], 404);
            }

            $queryParameters = $this->restStopBidsQueryParameters($request);

            validator($queryParameters, [
                'status' => ['sometimes', 'nullable', 'string', Rule::in(RouteStopBid::STATUSES)],
                'page' => ['required', 'integer', 'min:1'],
                'per_page' => ['required', 'integer', 'min:1', 'max:100'],
            ])->validate();

            $bids = $this->bidService->forRestStop(
                $restStop,
                $queryParameters['status'] ?? null,
                (int) $queryParameters['per_page'],
                (int) $queryParameters['page'],
            );
            $bids->appends($request->query());

            return response()->json([
                'data' => [
                    'bids' => $bids->getCollection()
                        ->map(fn (RouteStopBid $bid): array => $this->bidPayload($bid))
                        ->values()
                        ->all(),
                ],
                'links' => [
                    'first' => $bids->url(1),
                    'last' => $bids->url($bids->lastPage()),
                    'prev' => $bids->previousPageUrl(),
                    'next' => $bids->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $bids->currentPage(),
                    'from' => $bids->firstItem(),
                    'last_page' => $bids->lastPage(),
                    'path' => $bids->path(),
                    'per_page' => $bids->perPage(),
                    'to' => $bids->lastItem(),
                    'total' => $bids->total(),
                ],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch rest stop bids.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch bids.',
            ], 500);
        }
    }

    public function show(Request $request, int $routeStopId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'rest_stop') {
            return response()->json([
                'message' => 'Only rest stop users can view bids.',
            ], 403);
        }

        try {
            $restStop = $this->restStopService->findForUser($authenticatedUser);

            if (! $restStop) {
                return response()->json([
                    'message' => 'Rest stop profile not found.',
                ], 404);
            }

            $bid = $this->bidService->findForRestStopByRouteStop(
                $restStop,
                $routeStopId,
            );

            if (! $bid) {
                return response()->json([
                    'message' => 'Bid not found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'bid' => $this->bidPayload($bid),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch bid.', [
                'user_id' => $authenticatedUser->id,
                'route_stop_id' => $routeStopId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch bid.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'rest_stop') {
            return response()->json([
                'message' => 'Only rest stop users can create bids.',
            ], 403);
        }

        try {
            $restStop = $this->restStopService->findForUser($authenticatedUser);
        } catch (Throwable $throwable) {
            logger()->error('Unable to resolve rest stop for bid creation.', [
                'user_id' => $authenticatedUser->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to create bid.',
            ], 500);
        }

        if (! $restStop) {
            return response()->json([
                'message' => 'Rest stop profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'route_stop_id' => ['required', 'integer', 'min:1'],
            'original_price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:2'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:2'],
        ]);

        try {
            $bid = $this->bidService->upsertForRestStop(
                $restStop,
                $validated['route_stop_id'],
                (string) $validated['original_price'],
                (string) $validated['price'],
            );

            return response()->json([
                'message' => $bid->wasRecentlyCreated
                    ? 'Bid created successfully.'
                    : 'Bid updated successfully.',
                'data' => [
                    'bid' => $this->bidPayload($bid),
                ],
            ], $bid->wasRecentlyCreated ? 201 : 200);
        } catch (RouteStopNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            logger()->error('Unable to create bid.', [
                'user_id' => $authenticatedUser->id,
                'route_stop_id' => $validated['route_stop_id'],
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to create bid.',
            ], 500);
        }
    }

    public function destroy(Request $request, int $routeStopId): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'rest_stop') {
            return response()->json([
                'message' => 'Only rest stop users can delete bids.',
            ], 403);
        }

        try {
            $restStop = $this->restStopService->findForUser($authenticatedUser);

            if (! $restStop) {
                return response()->json([
                    'message' => 'Rest stop profile not found.',
                ], 404);
            }

            $bid = $this->bidService->deleteForRestStopByRouteStop(
                $restStop,
                $routeStopId,
            );

            if (! $bid) {
                return response()->json([
                    'message' => 'Bid not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Bid deleted successfully.',
                'data' => [
                    'bid' => $this->bidPayload($bid),
                ],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            logger()->error('Unable to delete bid.', [
                'user_id' => $authenticatedUser->id,
                'route_stop_id' => $routeStopId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to delete bid.',
            ], 500);
        }
    }

    /**
     * @return array{route_stop_id: int, rest_stop_id: int, original_price: string, price: string, status: string, created_at: string|null, updated_at: string|null}
     */
    private function bidPayload(RouteStopBid $bid): array
    {
        return [
            'route_stop_id' => $bid->route_stop_id,
            'rest_stop_id' => $bid->rest_stop_id,
            'original_price' => $this->pricePayload($bid->original_price),
            'price' => $this->pricePayload($bid->price),
            'status' => $bid->status,
            'created_at' => $bid->created_at?->toJSON(),
            'updated_at' => $bid->updated_at?->toJSON(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function restStopBidsQueryParameters(Request $request): array
    {
        $queryParameters = $request->query();

        $queryParameters['page'] ??= 1;
        $queryParameters['per_page'] ??= 15;

        return $queryParameters;
    }

    /**
     * @return array{route_stop_id: int, rest_stop_id: int, original_price: string, price: string, status: string, created_at: string|null, updated_at: string|null, rest_stop: array{id: int, user_id: int, city: string, address: string, post_code: string, works_from: string, works_to: string, user: array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}}}
     */
    private function bidWithRestStopPayload(RouteStopBid $bid): array
    {
        return [
            ...$this->bidPayload($bid),
            'rest_stop' => $this->restStopPayload($bid->restStop),
        ];
    }

    /**
     * @return array{id: int, user_id: int, city: string, address: string, post_code: string, works_from: string, works_to: string, user: array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}}
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
            'user' => $this->userPayload($restStop->user),
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

    private function pricePayload(mixed $price): string
    {
        return number_format((float) $price, 2, '.', '');
    }

    private function timePayload(string $time): string
    {
        return substr($time, 0, 5);
    }
}
