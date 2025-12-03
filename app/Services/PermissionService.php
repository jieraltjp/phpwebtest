<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    /**
     * 缓存键前缀
     */
    const CACHE_PREFIX = 'permissions:';
    
    /**
     * 默认缓存时间（秒）
     */
    const CACHE_TTL = 3600;

    /**
     * 为用户分配角色
     */
    public function assignRoleToUser(int $userId, int $roleId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $role = Role::findOrFail($roleId);

            // 检查角色是否激活
            if (!$role->is_active) {
                throw new \Exception('角色未激活');
            }

            // 检查用户是否已有该角色
            if ($user->roles()->where('role_id', $roleId)->exists()) {
                return true;
            }

            $result = $user->roles()->attach($roleId);
            
            // 清除用户权限缓存
            $this->clearUserPermissionCache($userId);
            
            Log::info("用户 {$userId} 被分配角色 {$roleId}");
            
            return $result;
        } catch (\Exception $e) {
            Log::error("分配角色失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 移除用户的角色
     */
    public function removeRoleFromUser(int $userId, int $roleId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $result = $user->roles()->detach($roleId);
            
            // 清除用户权限缓存
            $this->clearUserPermissionCache($userId);
            
            Log::info("用户 {$userId} 被移除角色 {$roleId}");
            
            return $result;
        } catch (\Exception $e) {
            Log::error("移除角色失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 同步用户角色
     */
    public function syncUserRoles(int $userId, array $roleIds): array
    {
        try {
            $user = User::findOrFail($userId);
            
            // 验证所有角色都存在且激活
            $roles = Role::whereIn('id', $roleIds)
                ->where('is_active', true)
                ->get();
            
            if ($roles->count() !== count($roleIds)) {
                throw new \Exception('部分角色不存在或未激活');
            }

            $result = $user->roles()->sync($roleIds);
            
            // 清除用户权限缓存
            $this->clearUserPermissionCache($userId);
            
            Log::info("用户 {$userId} 角色已同步: " . implode(', ', $roleIds));
            
            return $result;
        } catch (\Exception $e) {
            Log::error("同步角色失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 为用户分配权限
     */
    public function assignPermissionToUser(int $userId, int $permissionId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $permission = Permission::findOrFail($permissionId);

            // 检查权限是否激活
            if (!$permission->is_active) {
                throw new \Exception('权限未激活');
            }

            // 检查用户是否已有该权限
            if ($user->permissions()->where('permission_id', $permissionId)->exists()) {
                return true;
            }

            $result = $user->permissions()->attach($permissionId);
            
            // 清除用户权限缓存
            $this->clearUserPermissionCache($userId);
            
            Log::info("用户 {$userId} 被分配权限 {$permissionId}");
            
            return $result;
        } catch (\Exception $e) {
            Log::error("分配权限失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 移除用户的权限
     */
    public function removePermissionFromUser(int $userId, int $permissionId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $result = $user->permissions()->detach($permissionId);
            
            // 清除用户权限缓存
            $this->clearUserPermissionCache($userId);
            
            Log::info("用户 {$userId} 被移除权限 {$permissionId}");
            
            return $result;
        } catch (\Exception $e) {
            Log::error("移除权限失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 同步用户权限
     */
    public function syncUserPermissions(int $userId, array $permissionIds): array
    {
        try {
            $user = User::findOrFail($userId);
            
            // 验证所有权限都存在且激活
            $permissions = Permission::whereIn('id', $permissionIds)
                ->where('is_active', true)
                ->get();
            
            if ($permissions->count() !== count($permissionIds)) {
                throw new \Exception('部分权限不存在或未激活');
            }

            $result = $user->permissions()->sync($permissionIds);
            
            // 清除用户权限缓存
            $this->clearUserPermissionCache($userId);
            
            Log::info("用户 {$userId} 权限已同步: " . implode(', ', $permissionIds));
            
            return $result;
        } catch (\Exception $e) {
            Log::error("同步权限失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 检查用户权限
     */
    public function checkUserPermission(int $userId, string $permission): bool
    {
        $cacheKey = self::CACHE_PREFIX . "user:{$userId}:permissions";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $permission) {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            return $user->hasPermission($permission);
        });
    }

    /**
     * 获取用户所有权限
     */
    public function getUserPermissions(int $userId): array
    {
        $cacheKey = self::CACHE_PREFIX . "user:{$userId}:all_permissions";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return [];
            }

            return $user->getAllPermissions();
        });
    }

    /**
     * 获取用户角色
     */
    public function getUserRoles(int $userId): array
    {
        $cacheKey = self::CACHE_PREFIX . "user:{$userId}:roles";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return [];
            }

            return $user->roles()->with('permissions')->get()->toArray();
        });
    }

    /**
     * 创建角色
     */
    public function createRole(array $data): Role
    {
        try {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'level' => $data['level'] ?? 20,
                'permissions' => $data['permissions'] ?? [],
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 清除相关缓存
            $this->clearRoleCache();

            Log::info("角色已创建: {$role->name} (ID: {$role->id})");

            return $role;
        } catch (\Exception $e) {
            Log::error("创建角色失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 更新角色
     */
    public function updateRole(int $roleId, array $data): Role
    {
        try {
            $role = Role::findOrFail($roleId);
            $role->update($data);

            // 清除相关缓存
            $this->clearRoleCache();
            $this->clearUserCacheByRole($roleId);

            Log::info("角色已更新: {$role->name} (ID: {$role->id})");

            return $role;
        } catch (\Exception $e) {
            Log::error("更新角色失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除角色
     */
    public function deleteRole(int $roleId): bool
    {
        try {
            $role = Role::findOrFail($roleId);
            
            // 检查是否有用户使用该角色
            $userCount = $role->users()->count();
            if ($userCount > 0) {
                throw new \Exception("无法删除角色，仍有 {$userCount} 个用户使用该角色");
            }

            $roleName = $role->name;
            $result = $role->delete();

            // 清除相关缓存
            $this->clearRoleCache();

            Log::info("角色已删除: {$roleName} (ID: {$roleId})");

            return $result;
        } catch (\Exception $e) {
            Log::error("删除角色失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建权限
     */
    public function createPermission(array $data): Permission
    {
        try {
            $permission = Permission::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'group' => $data['group'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 清除相关缓存
            $this->clearPermissionCache();

            Log::info("权限已创建: {$permission->name} (ID: {$permission->id})");

            return $permission;
        } catch (\Exception $e) {
            Log::error("创建权限失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取角色统计
     */
    public function getRoleStatistics(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'role_statistics', self::CACHE_TTL, function () {
            return [
                'total_roles' => Role::count(),
                'active_roles' => Role::where('is_active', true)->count(),
                'inactive_roles' => Role::where('is_active', false)->count(),
                'roles_by_level' => Role::selectRaw('level, COUNT(*) as count')
                    ->groupBy('level')
                    ->orderBy('level', 'desc')
                    ->pluck('count', 'level')
                    ->toArray(),
            ];
        });
    }

    /**
     * 获取权限统计
     */
    public function getPermissionStatistics(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'permission_statistics', self::CACHE_TTL, function () {
            return [
                'total_permissions' => Permission::count(),
                'active_permissions' => Permission::where('is_active', true)->count(),
                'permissions_by_group' => Permission::selectRaw('`group`, COUNT(*) as count')
                    ->groupBy('group')
                    ->orderBy('count', 'desc')
                    ->pluck('count', 'group')
                    ->toArray(),
            ];
        });
    }

    /**
     * 获取用户权限统计
     */
    public function getUserPermissionStatistics(int $userId): array
    {
        return Cache::remember(self::CACHE_PREFIX . "user:{$userId}:statistics", self::CACHE_TTL, function () use ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return [];
            }

            return [
                'roles_count' => $user->roles()->count(),
                'direct_permissions' => $user->permissions()->count(),
                'total_permissions' => count($user->getAllPermissions()),
                'highest_role_level' => $user->roles()->max('level') ?? 0,
            ];
        });
    }

    /**
     * 批量分配角色
     */
    public function batchAssignRoles(array $userIds, int $roleId): int
    {
        try {
            $role = Role::findOrFail($roleId);
            
            if (!$role->is_active) {
                throw new \Exception('角色未激活');
            }

            $assignedCount = 0;
            foreach ($userIds as $userId) {
                try {
                    $this->assignRoleToUser($userId, $roleId);
                    $assignedCount++;
                } catch (\Exception $e) {
                    Log::warning("用户 {$userId} 分配角色失败: " . $e->getMessage());
                }
            }

            Log::info("批量分配角色 {$roleId} 完成，成功分配 {$assignedCount} 个用户");

            return $assignedCount;
        } catch (\Exception $e) {
            Log::error("批量分配角色失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 批量移除角色
     */
    public function batchRemoveRoles(array $userIds, int $roleId): int
    {
        try {
            $removedCount = 0;
            foreach ($userIds as $userId) {
                try {
                    $this->removeRoleFromUser($userId, $roleId);
                    $removedCount++;
                } catch (\Exception $e) {
                    Log::warning("用户 {$userId} 移除角色失败: " . $e->getMessage());
                }
            }

            Log::info("批量移除角色 {$roleId} 完成，成功移除 {$removedCount} 个用户");

            return $removedCount;
        } catch (\Exception $e) {
            Log::error("批量移除角色失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 清除用户权限缓存
     */
    private function clearUserPermissionCache(int $userId): void
    {
        $patterns = [
            self::CACHE_PREFIX . "user:{$userId}:*",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * 清除角色缓存
     */
    private function clearRoleCache(): void
    {
        $patterns = [
            self::CACHE_PREFIX . 'role_*',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * 清除权限缓存
     */
    private function clearPermissionCache(): void
    {
        $patterns = [
            self::CACHE_PREFIX . 'permission_*',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * 清除指定角色的用户缓存
     */
    private function clearUserCacheByRole(int $roleId): void
    {
        $userIds = DB::table('user_roles')
            ->where('role_id', $roleId)
            ->pluck('user_id')
            ->toArray();

        foreach ($userIds as $userId) {
            $this->clearUserPermissionCache($userId);
        }
    }

    /**
     * 清除所有权限相关缓存
     */
    public function clearAllPermissionCache(): void
    {
        $patterns = [
            self::CACHE_PREFIX . '*',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        Log::info('所有权限缓存已清除');
    }

    /**
     * 初始化默认权限和角色
     */
    public function initializeDefaultPermissions(): void
    {
        try {
            // 创建默认权限
            Permission::createDefaultPermissions();
            
            // 创建默认角色
            Role::createDefaultRoles();
            
            Log::info('默认权限和角色初始化完成');
        } catch (\Exception $e) {
            Log::error("初始化默认权限失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 验证权限配置
     */
    public function validatePermissionConfig(): array
    {
        $issues = [];

        // 检查是否有超级管理员
        $superAdminCount = Role::where('slug', 'super_admin')->count();
        if ($superAdminCount === 0) {
            $issues[] = '缺少超级管理员角色';
        }

        // 检查是否有默认用户角色
        $userRoleCount = Role::where('slug', 'user')->count();
        if ($userRoleCount === 0) {
            $issues[] = '缺少默认用户角色';
        }

        // 检查权限完整性
        $requiredPermissions = ['users.view', 'products.view', 'orders.view'];
        foreach ($requiredPermissions as $permission) {
            if (!Permission::where('slug', $permission)->exists()) {
                $issues[] = "缺少必需权限: {$permission}";
            }
        }

        return $issues;
    }
}