<?php

namespace App\Http\Controllers;

use App\Contracts\BidServiceContract;
use App\Exceptions\RouteStopNotFoundException;
use App\Models\RouteStopBid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class BidController extends Controller
{
    public function __construct(private readonly BidServiceContract $bidService) {}

    public function store(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser->profile_type !== 'rest_stop') {
            return response()->json([
                'message' => 'Only rest stop users can create bids.',
            ], 403);
        }

        $validated = $request->validate([
            'route_stop_id' => ['required', 'integer', 'min:1'],
            'original_price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:2'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:2'],
        ]);

        try {
            $bid = $this->bidService->createForUser(
                $authenticatedUser,
                $validated['route_stop_id'],
                (string) $validated['original_price'],
                (string) $validated['price'],
            );

            if (! $bid) {
                return response()->json([
                    'message' => 'Rest stop profile not found.',
                ], 404);
            }

            return response()->json([
                'message' => $bid->wasRecentlyCreated
                    ? 'Bid created successfully.'
                    : 'Bid already exists.',
                'data' => [
                    'bid' => $this->bidPayload($bid),
                ],
            ], $bid->wasRecentlyCreated ? 201 : 200);
        } catch (RouteStopNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
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

    /**
     * @return array{route_stop_id: int, rest_stop_id: int, original_price: string, price: string}
     */
    private function bidPayload(RouteStopBid $bid): array
    {
        return [
            'route_stop_id' => $bid->route_stop_id,
            'rest_stop_id' => $bid->rest_stop_id,
            'original_price' => $this->pricePayload($bid->original_price),
            'price' => $this->pricePayload($bid->price),
        ];
    }

    private function pricePayload(mixed $price): string
    {
        return number_format((float) $price, 2, '.', '');
    }
}
