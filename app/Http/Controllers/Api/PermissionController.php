<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\ApiResponseService;
use App\Services\PermissionService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * 获取用户权限列表
     */
    public function getUserPermissions(Request $request): JsonResponse
    {
        try {
            $userId = $request->get('user_id', auth()->id());
            
            // 验证权限：只有管理员可以查看其他用户的权限
            if ($userId !== auth()->id() && !auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            $permissions = $this->permissionService->getUserPermissions($userId);
            $roles = $this->permissionService->getUserRoles($userId);
            $statistics = $this->permissionService->getUserPermissionStatistics($userId);

            $data = [
                'permissions' => $permissions,
                'roles' => $roles,
                'statistics' => $statistics,
            ];

            return ApiResponseService::success($data, '获取用户权限成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取用户权限失败: ' . $e->getMessage());
        }
    }

    /**
     * 分配角色给用户
     */
    public function assignRole(Request $request): JsonResponse
    {
        $validator = ValidationService::validate([
            'user_id' => 'required|integer|exists:users,id',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            $userId = $request->get('user_id');
            $roleId = $request->get('role_id');

            // 验证权限：只有管理员可以分配角色
            if (!auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            // 检查目标用户
            $targetUser = User::findOrFail($userId);
            
            // 检查是否可以管理目标用户
            if (!auth()->user()->canManageUser($targetUser)) {
                return ApiResponseService::forbidden('权限不足，无法管理该用户');
            }

            // 检查角色
            $role = Role::findOrFail($roleId);
            if (!$role->is_active) {
                return ApiResponseService::error('角色未激活', null, 400);
            }

            // 检查是否可以管理该角色
            if (!auth()->user()->canManageRole($role)) {
                return ApiResponseService::error('权限不足，无法管理该角色', null, 403);
            }

            $this->permissionService->assignRoleToUser($userId, $roleId);

            return ApiResponseService::success(null, '角色分配成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('分配角色失败: ' . $e->getMessage());
        }
    }

    /**
     * 移除用户角色
     */
    public function removeRole(Request $request): JsonResponse
    {
        $validator = ValidationService::validate([
            'user_id' => 'required|integer|exists:users,id',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            $userId = $request->get('user_id');
            $roleId = $request->get('role_id');

            // 验证权限：只有管理员可以移除角色
            if (!auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            // 检查目标用户
            $targetUser = User::findOrFail($userId);
            
            // 检查是否可以管理目标用户
            if (!auth()->user()->canManageUser($targetUser)) {
                return ApiResponseService::forbidden('权限不足，无法管理该用户');
            }

            $this->permissionService->removeRoleFromUser($userId, $roleId);

            return ApiResponseService::success(null, '角色移除成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('移除角色失败: ' . $e->getMessage());
        }
    }

    /**
     * 同步用户角色
     */
    public function syncUserRoles(Request $request): JsonResponse
    {
        $validator = ValidationService::validate([
            'user_id' => 'required|integer|exists:users,id',
            'role_ids' => 'required|array|min:0|max:10',
            'role_ids.*' => 'integer|exists:roles,id',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            $userId = $request->get('user_id');
            $roleIds = $request->get('role_ids');

            // 验证权限：只有管理员可以同步角色
            if (!auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            // 检查目标用户
            $targetUser = User::findOrFail($userId);
            
            // 检查是否可以管理目标用户
            if (!auth()->user()->canManageUser($targetUser)) {
                return ApiResponseService::forbidden('权限不足，无法管理该用户');
            }

            // 验证所有角色
            $roles = Role::whereIn('id', $roleIds)
                ->where('is_active', true)
                ->get();
            
            if ($roles->count() !== count($roleIds)) {
                return ApiResponseService::error('部分角色不存在或未激活', null, 400);
            }

            // 检查是否可以管理这些角色
            foreach ($roles as $role) {
                if (!auth()->user()->canManageRole($role)) {
                    return ApiResponseService::error('权限不足，无法管理角色: ' . $role->name, null, 403);
                }
            }

            $syncData = $this->permissionService->syncUserRoles($userId, $roleIds);

            return ApiResponseService::success($syncData, '用户角色同步成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('同步角色失败: ' . $e->getMessage());
        }
    }

    /**
     * 分配权限给用户
     */
    public function assignPermission(Request $request): JsonResponse
    {
        $validator = ValidationService::validate([
            'user_id' => 'required|integer|exists:users,id',
            'permission_id' => 'required|integer|exists:permissions,id',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            $userId = $request->get('user_id');
            $permissionId = $request->get('permission_id');

            // 验证权限：只有管理员可以分配权限
            if (!auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            // 检查目标用户
            $targetUser = User::findOrFail($userId);
            
            // 检查是否可以管理目标用户
            if (!auth()->user()->canManageUser($targetUser)) {
                return ApiResponseService::forbidden('权限不足，无法管理该用户');
            }

            $this->permissionService->assignPermissionToUser($userId, $permissionId);

            return ApiResponseService::success(null, '权限分配成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('分配权限失败: ' . $e->getMessage());
        }
    }

    /**
     * 移除用户权限
     */
    public function removePermission(Request $request): JsonResponse
    {
        $validator = ValidationService::validate([
            'user_id' => 'required|integer|exists:users,id',
            'permission_id' => 'required|integer|exists:permissions,id',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            $userId = $request->get('user_id');
            $permissionId = $request->get('permission_id');

            // 验证权限：只有管理员可以移除权限
            if (!auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            // 检查目标用户
            $targetUser = User::findOrFail($userId);
            
            // 检查是否可以管理目标用户
            if (!auth()->user()->canManageUser($targetUser)) {
                return ApiResponseService::forbidden('权限不足，无法管理该用户');
            }

            $this->permissionService->removePermissionFromUser($userId, $permissionId);

            return ApiResponseService::success(null, '权限移除成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('移除权限失败: ' . $e->getMessage());
        }
    }

    /**
     * 同步用户权限
     */
    public function syncUserPermissions(Request $request): JsonResponse
    {
        $validator = ValidationService::validate([
            'user_id' => 'required|integer|exists:users,id',
            'permission_ids' => 'required|array|min:0|max:50',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            $userId = $request->get('user_id');
            $permissionIds = $request->get('permission_ids');

            // 验证权限：只有管理员可以同步权限
            if (!auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            // 检查目标用户
            $targetUser = User::findOrFail($userId);
            
            // 检查是否可以管理目标用户
            if (!auth()->user()->canManageUser($targetUser)) {
                return ApiResponseService::forbidden('权限不足，无法管理该用户');
            }

            // 验证所有权限
            $permissions = Permission::whereIn('id', $permissionIds)
                ->where('is_active', true)
                ->get();
            
            if ($permissions->count() !== count($permissionIds)) {
                return ApiResponseService::error('部分权限不存在或未激活', null, 400);
            }

            $syncData = $this->permissionService->syncUserPermissions($userId, $permissionIds);

            return ApiResponseService::success($syncData, '用户权限同步成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('同步权限失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取角色列表
     */
    public function getRoles(Request $request): JsonResponse
    {
        try {
            $query = Role::query();

            // 筛选参数
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('level')) {
                $query->where('level', '<=', $request->integer('level'));
            }

            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $roles = $query->withCount('users')
                ->orderBy('level', 'desc')
                ->orderBy('name', 'asc')
                ->paginate($request->get('per_page', 20), ['*'], 'page', $request->get('page', 1));

            return ApiResponseService::paginated(
                $roles->getCollection()->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'description' => $role->description,
                        'level' => $role->level,
                        'is_active' => $role->is_active,
                        'permissions_count' => count($role->permissions ?? []),
                        'users_count' => $role->users_count,
                        'level_description' => $role->getLevelDescription(),
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ];
                }),
                $roles
            );

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取角色列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 创建角色
     */
    public function createRole(Request $request): JsonResponse
    {
        // 验证权限：只有管理员可以创建角色
        if (!auth()->user()->isAdmin()) {
            return ApiResponseService::forbidden('权限不足');
        }

        $validator = ValidationService::validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:roles,slug',
            'description' => 'nullable|string|max:1000',
            'level' => 'required|integer|min:1|max:100',
            'permissions' => 'nullable|array|max:100',
            'permissions.*' => 'string|max:100',
            'is_active' => 'boolean',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            $role = $this->permissionService->createRole($request->all());

            return ApiResponseService::success([
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'level' => $role->level,
                'permissions' => $role->permissions,
                'is_active' => $role->is_active,
                'created_at' => $role->created_at,
            ], '角色创建成功', 201);

        } catch (\Exception $e) {
            return ApiResponseService::serverError('创建角色失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新角色
     */
    public function updateRole(Request $request, $id): JsonResponse
    {
        // 验证权限：只有管理员可以更新角色
        if (!auth()->user()->isAdmin()) {
            return ApiResponseService::forbidden('权限不足');
        }

        $validator = ValidationService::validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array|max:100',
            'permissions.*' => 'string|max:100',
            'is_active' => 'boolean',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            $role = $this->permissionService->updateRole($id, $request->all());

            return ApiResponseService->success([
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'level' => $role->level,
                'permissions' => $role->permissions,
                'is_active' => $role->is_active,
                'updated_at' => $role->updated_at,
            ], '角色更新成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('更新角色失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除角色
     */
    public function deleteRole(Request $request, $id): JsonResponse
    {
        // 验证权限：只有超级管理员可以删除角色
        if (!auth()->user()->isSuperAdmin()) {
            return ApiResponseService::forbidden('权限不足');
        }

        try {
            $this->permissionService->deleteRole($id);

            return ApiResponseService::success(null, '角色删除成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('删除角色失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取权限列表
     */
    public function getPermissions(Request $request): JsonResponse
    {
        try {
            $query = Permission::query();

            // 筛选参数
            if ($request->has('group')) {
                $query->where('group', $request->get('group'));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $permissions = $query->withCount('roles')
                ->orderBy('group', 'asc')
                ->orderBy('name', 'asc')
                ->paginate($request->get('per_page', 20), ['*'], 'page', $request->get('page', 1));

            return ApiResponseService::paginated(
                $permissions->getCollection()->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'slug' => $permission->slug,
                        'description' => $permission->description,
                        'group' => $permission->group,
                        'is_active' => $permission->is_active,
                        'roles_count' => $permission->roles_count,
                        'created_at' => $permission->created_at,
                        'updated_at' => $permission->updated_at,
                    ];
                }),
                $permissions
            );

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取权限列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取权限统计
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $roleStats = $this->permissionService->getRoleStatistics();
            $permissionStats = $this->permissionService->getPermissionStatistics();

            $data = [
                'roles' => $roleStats,
                'permissions' => $permissionStats,
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_admins' => User::whereHas('roles', function ($query) {
                    $query->where('slug', 'admin')->orWhere('slug', 'super_admin');
                })->count(),
            ];

            return ApiResponseService::success($data, '获取统计信息成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取统计信息失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量分配角色
     */
    public function batchAssignRoles(Request $request): JsonResponse
    {
        $validator = ValidationService::validate([
            'user_ids' => 'required|array|min:1|max:100',
            'user_ids.*' => 'integer|exists:users,id',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            // 验证权限：只有管理员可以批量分配角色
            if (!auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            $userIds = $request->get('user_ids');
            $roleId = $request->get('role_id');

            $assignedCount = $this->permissionService->batchAssignRoles($userIds, $roleId);

            return ApiResponseService::success([
                'assigned_count' => $assignedCount,
                'total_count' => count($userIds),
            ], '批量分配角色完成');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('批量分配角色失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量移除角色
     */
    public function batchRemoveRoles(Request $request): JsonResponse
    {
        $validator = ValidationService::validate([
            'user_ids' => 'required|array|min:1|max:100',
            'user_ids.*' => 'integer|exists:users,id',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if (!$validator['valid']) {
            return ApiResponseService::validationError($validator['errors']);
        }

        try {
            // 验证权限：只有管理员可以批量移除角色
            if (!auth()->user()->isAdmin()) {
                return ApiResponseService::forbidden('权限不足');
            }

            $userIds = $request->get('user_ids');
            $roleId = $request->get('role_id');

            $removedCount = $this->permissionService->batchRemoveRoles($userIds, $roleId);

            return ApiResponseService::success([
                'removed_count' => $removedCount,
                'total_count' => count($userIds),
            ], '批量移除角色完成');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('批量移除角色失败: ' . $e->getMessage());
        }
    }

    /**
     * 初始化默认权限和角色
     */
    public function initializeDefaults(): JsonResponse
    {
        // 验证权限：只有超级管理员可以初始化
        if (!auth()->user()->isSuperAdmin()) {
            return ApiResponseService::forbidden('权限不足');
        }

        try {
            $this->permissionService->initializeDefaultPermissions();
            
            return ApiResponseService::success(null, '默认权限和角色初始化成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('初始化失败: ' . $e->getMessage());
        }
    }

    /**
     * 验证权限配置
     */
    public function validateConfig(): JsonResponse
    {
        try {
            $issues = $this->permissionService->validatePermissionConfig();
            
            if (empty($issues)) {
                return ApiResponseService::success(null, '权限配置验证通过');
            } else {
                return ApiResponseService::error('权限配置存在问题', [
                    'issues' => $issues
                ]);
            }

        } catch (\Exception $e) {
            return ApiResponseService::serverError('验证权限配置失败: ' . $e->getMessage());
        }
    }

    /**
     * 清除权限缓存
     */
    public function clearCache(): JsonResponse
    {
        // 验证权限：只有管理员可以清除缓存
        if (!auth()->user()->isAdmin()) {
            return ApiResponseService::forbidden('权限不足');
        }

        try {
            $this->permissionService->clearAllPermissionCache();

            return ApiResponseService::success(null, '权限缓存已清除');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('清除缓存失败: ' . $e->getMessage());
        }
    }
}