<?php

namespace App\Http\Controllers;

use App\Contracts\UserManagementServiceContract;
use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UserManagementController extends Controller
{
    public function __construct(private readonly UserManagementServiceContract $userManagementService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $profiles = $this->userManagementService->profilesNeedingApproval();

            return response()->json([
                'data' => [
                    'dispatchers' => $profiles['dispatchers']
                        ->map(fn (Dispatcher $dispatcher): array => $this->dispatcherPayload($dispatcher))
                        ->values()
                        ->all(),
                    'rest_stops' => $profiles['rest_stops']
                        ->map(fn (RestStop $restStop): array => $this->restStopPayload($restStop))
                        ->values()
                        ->all(),
                ],
            ]);
        } catch (Throwable $throwable) {
            logger()->error('Unable to fetch profiles needing approval.', [
                'admin_user_id' => $request->user()->id,
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch profiles needing approval.',
            ], 500);
        }
    }

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
                    'user' => $this->userPayload($profile->user),
                ],
            ],
        ]);
    }

    /**
     * @return array{id: int, user_id: int, company_name: string, city: string, address: string, post_code: string, registration_number: string, is_approved: bool, user: array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}}
     */
    private function dispatcherPayload(Dispatcher $dispatcher): array
    {
        return [
            'id' => $dispatcher->id,
            'user_id' => $dispatcher->user_id,
            'company_name' => $dispatcher->company_name,
            'city' => $dispatcher->city,
            'address' => $dispatcher->address,
            'post_code' => $dispatcher->post_code,
            'registration_number' => $dispatcher->registration_number,
            'is_approved' => $dispatcher->is_approved,
            'user' => $this->userPayload($dispatcher->user),
        ];
    }

    /**
     * @return array{id: int, user_id: int, city: string, address: string, post_code: string, works_from: string, works_to: string, is_approved: bool, user: array{id: int, first_name: string|null, last_name: string|null, email: string, country: string|null, phone_number: string|null, profile_type: string|null}}
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
            'is_approved' => $restStop->is_approved,
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

    private function timePayload(string $time): string
    {
        return substr($time, 0, 5);
    }
}
