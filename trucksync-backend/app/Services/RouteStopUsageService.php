<?php

namespace App\Services;

use App\Contracts\RouteStopUsageServiceContract;
use App\Exceptions\RouteStopNotFoundException;
use App\Exceptions\RouteStopUsageNotAllowedException;
use App\Models\Driver;
use App\Models\RouteStop;
use App\Models\RouteStopUsage;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RouteStopUsageService implements RouteStopUsageServiceContract
{
    /**
     * @return LengthAwarePaginator<int, RouteStopUsage>
     */
    public function ratingsForAdmin(
        ?int $restStopId = null,
        int $perPage = 15,
        int $page = 1
    ): LengthAwarePaginator {
        return RouteStopUsage::query()
            ->when($restStopId !== null, fn ($query) => $query->where('rest_stop_id', $restStopId))
            ->orderByDesc('used_at')
            ->orderByDesc('id')
            ->paginate(perPage: $perPage, page: $page);
    }

    /**
     * @throws RouteStopNotFoundException
     * @throws RouteStopUsageNotAllowedException
     * @throws ValidationException
     */
    public function submitReviewForUser(
        User $user,
        int $routeStopId,
        int $rating,
        ?string $report,
        bool $isReport
    ): ?RouteStopUsage {
        $driver = Driver::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $driver) {
            return null;
        }

        return DB::transaction(function () use ($driver, $routeStopId, $rating, $report, $isReport): RouteStopUsage {
            $routeStop = RouteStop::query()
                ->with('route')
                ->find($routeStopId);

            if (! $routeStop) {
                throw new RouteStopNotFoundException;
            }

            if (! $this->driverIsConvoyLeaderForRoute($driver, (int) $routeStop->route_id)) {
                throw new RouteStopUsageNotAllowedException;
            }

            if ($routeStop->fulfilled_by === null) {
                throw ValidationException::withMessages([
                    'route_stop_id' => 'Route stop must have a selected rest stop before it can be reviewed.',
                ]);
            }

            $routeStopUsage = RouteStopUsage::query()
                ->firstOrNew([
                    'route_stop_id' => $routeStop->id,
                ]);

            $routeStopUsage->fill([
                'driver_id' => $driver->id,
                'rest_stop_id' => $routeStop->fulfilled_by,
                'used_at' => $routeStopUsage->exists ? $routeStopUsage->used_at : now(),
                'rating' => $rating,
                'report' => $report,
                'is_report' => $isReport,
            ]);
            $routeStopUsage->save();

            return $routeStopUsage->refresh();
        });
    }

    private function driverIsConvoyLeaderForRoute(Driver $driver, int $routeId): bool
    {
        return $driver->routes()
            ->where('routes.id', $routeId)
            ->wherePivot('is_convoy_leader', true)
            ->exists();
    }
}
