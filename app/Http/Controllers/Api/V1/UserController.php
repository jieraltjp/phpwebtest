<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Application\Services\UserApplicationService;
use App\Application\DTOs\CreateUserDTO;
use App\Application\DTOs\UpdateUserDTO;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class UserController extends Controller
{
    public function __construct(
        private UserApplicationService $userApplicationService,
        private ApiResponseService $apiResponseService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['role', 'status', 'search']);
        
        try {
            if (isset($filters['search'])) {
                $users = $this->userApplicationService->searchUsers($filters['search']);
            } elseif (isset($filters['role'])) {
                $users = $this->userApplicationService->getUsersByRole($filters['role']);
            } elseif (isset($filters['status'])) {
                $users = $this->userApplicationService->getUsersByStatus($filters['status']);
            } else {
                $users = $this->userApplicationService->getActiveUsers();
            }

            $userArrays = array_map(fn($user) => $user->toArray(), $users);

            return $this->apiResponseService->success($userArrays, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to retrieve users: ' . $e->getMessage(), [], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|min:3|max:50',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|string|in:admin,purchase_manager,sales_representative,customer,supplier'
        ]);

        try {
            $createUserDTO = CreateUserDTO::fromArray([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => bcrypt($data['password']),
                'role' => $data['role'] ?? 'customer'
            ]);

            $user = $this->userApplicationService->createUser($createUserDTO);

            return $this->apiResponseService->success(
                $user->toArray(),
                'User created successfully',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseService->error($e->getMessage(), [], 422);
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to create user: ' . $e->getMessage(), [], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userApplicationService->getUserById($id);
            
            if (!$user) {
                return $this->apiResponseService->error('User not found', [], 404);
            }

            return $this->apiResponseService->success($user->toArray());
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to retrieve user: ' . $e->getMessage(), [], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'email' => 'sometimes|email|max:255',
            'username' => 'sometimes|string|min:3|max:50',
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string|in:admin,purchase_manager,sales_representative,customer,supplier',
            'status' => 'sometimes|string|in:active,inactive,suspended,pending_verification'
        ]);

        try {
            $updateData = [];
            
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            if (isset($data['username'])) {
                $updateData['username'] = $data['username'];
            }
            if (isset($data['password'])) {
                $updateData['password_hash'] = bcrypt($data['password']);
            }
            if (isset($data['role'])) {
                $updateData['role'] = $data['role'];
            }
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }

            $updateUserDTO = UpdateUserDTO::fromArray($updateData);
            $user = $this->userApplicationService->updateUser($id, $updateUserDTO);

            return $this->apiResponseService->success($user->toArray(), 'User updated successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseService->error($e->getMessage(), [], 422);
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to update user: ' . $e->getMessage(), [], 500);
        }
    }

    public function changeRole(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'role' => 'required|string|in:admin,purchase_manager,sales_representative,customer,supplier',
            'changed_by' => 'sometimes|string'
        ]);

        try {
            $user = $this->userApplicationService->changeUserRole(
                $id,
                $data['role'],
                $data['changed_by'] ?? null
            );

            return $this->apiResponseService->success($user->toArray(), 'User role updated successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseService->error($e->getMessage(), [], 422);
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to update user role: ' . $e->getMessage(), [], 500);
        }
    }

    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:active,inactive,suspended,pending_verification',
            'reason' => 'sometimes|string|max:255'
        ]);

        try {
            $user = $this->userApplicationService->changeUserStatus(
                $id,
                $data['status'],
                $data['reason'] ?? null
            );

            return $this->apiResponseService->success($user->toArray(), 'User status updated successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseService->error($e->getMessage(), [], 422);
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to update user status: ' . $e->getMessage(), [], 500);
        }
    }

    public function checkEmailAvailability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:255'
        ]);

        try {
            $isAvailable = $this->userApplicationService->isEmailAvailable($data['email']);

            return $this->apiResponseService->success([
                'email' => $data['email'],
                'available' => $isAvailable
            ]);
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to check email availability: ' . $e->getMessage(), [], 500);
        }
    }

    public function checkUsernameAvailability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|min:3|max:50'
        ]);

        try {
            $isAvailable = $this->userApplicationService->isUsernameAvailable($data['username']);

            return $this->apiResponseService->success([
                'username' => $data['username'],
                'available' => $isAvailable
            ]);
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to check username availability: ' . $e->getMessage(), [], 500);
        }
    }

    public function getUserRiskScore(int $id): JsonResponse
    {
        try {
            $riskScore = $this->userApplicationService->getUserRiskScore($id);

            return $this->apiResponseService->success([
                'user_id' => $id,
                'risk_score' => $riskScore,
                'risk_level' => $this->getRiskLevel($riskScore)
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseService->error($e->getMessage(), [], 404);
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to calculate risk score: ' . $e->getMessage(), [], 500);
        }
    }

    public function checkUserPermission(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'action' => 'required|string'
        ]);

        try {
            $canPerform = $this->userApplicationService->canUserPerformAction($id, $data['action']);

            return $this->apiResponseService->success([
                'user_id' => $id,
                'action' => $data['action'],
                'can_perform' => $canPerform
            ]);
        } catch (\Exception $e) {
            return $this->apiResponseService->error('Failed to check user permission: ' . $e->getMessage(), [], 500);
        }
    }

    private function getRiskLevel(int $riskScore): string
    {
        return match (true) {
            $riskScore >= 80 => 'high',
            $riskScore >= 50 => 'medium',
            $riskScore >= 20 => 'low',
            default => 'minimal'
        };
    }
}