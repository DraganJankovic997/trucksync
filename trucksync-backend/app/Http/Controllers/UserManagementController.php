<?php

namespace App\Http\Controllers;

use App\Contracts\UserManagementServiceContract;
use App\Models\Dispatcher;
use App\Models\RestStop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UserManagementController extends Controller
{
    public function __construct(private readonly UserManagementServiceContract $userManagementService) {}

    public function approve(Request $request, int $userId): JsonResponse
    {
        try {
            $profile = $this->userManagementService->approveProfileForUser($userId);

            if (! $profile) {
                return response()->json([
                    'message' => 'Approvable profile not found.',
                ], 404);
            }

            return $this->approvalResponse($profile);
        } catch (Throwable $throwable) {
            logger()->error('Unable to approve profile.', [
                'admin_user_id' => $request->user()->id,
                'target_user_id' => $userId,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to approve profile.',
            ], 500);
        }
    }

    private function approvalResponse(Dispatcher|RestStop $profile): JsonResponse
    {
        return response()->json([
            'message' => 'Profile approved successfully.',
            'data' => [
                'approval' => [
                    'profile_id' => $profile->id,
                    'user_id' => $profile->user_id,
                    'profile_type' => $profile instanceof Dispatcher ? 'dispatcher' : 'rest_stop',
                    'is_approved' => $profile->is_approved,
                ],
            ],
        ]);
    }
}
