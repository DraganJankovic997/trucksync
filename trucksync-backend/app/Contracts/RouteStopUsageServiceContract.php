<?php

namespace App\Contracts;

use App\Exceptions\RouteStopNotFoundException;
use App\Exceptions\RouteStopUsageNotAllowedException;
use App\Models\RouteStopUsage;
use App\Models\User;
use Illuminate\Validation\ValidationException;

interface RouteStopUsageServiceContract
{
    /**
     * @throws RouteStopNotFoundException
     * @throws RouteStopUsageNotAllowedException
     * @throws ValidationException
     */
    public function submitReviewForUser(
        User $user,
        int $routeStopId,
        ?int $rating,
        ?string $report,
        bool $isReport
    ): ?RouteStopUsage;
}
