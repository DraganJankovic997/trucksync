<?php

namespace App\Contracts;

use App\Exceptions\RouteStopNotFoundException;
use App\Exceptions\RouteStopUsageNotAllowedException;
use App\Models\RouteStopUsage;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

interface RouteStopUsageServiceContract
{
    /**
     * @return LengthAwarePaginator<int, RouteStopUsage>
     */
    public function ratingsForAdmin(
        ?int $restStopId = null,
        int $perPage = 15,
        int $page = 1
    ): LengthAwarePaginator;

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
    ): ?RouteStopUsage;
}
