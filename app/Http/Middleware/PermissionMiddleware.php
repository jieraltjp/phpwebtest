<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        // 如果用户未认证，返回401
        if (!Auth::check()) {
            return ApiResponseService::unauthorized('请先登录');
        }

        $user = Auth::user();

        // 检查用户是否激活
        if (!$user->isActive()) {
            return ApiResponseService::forbidden('账户已被禁用');
        }

        // 如果没有指定权限，只检查登录状态
        if (!$permission) {
            return $next($request);
        }

        // 检查用户权限
        if (!$this->checkPermission($user, $permission)) {
            return ApiResponseService::forbidden('权限不足，无法访问此资源');
        }

        return $next($request);
    }

    /**
     * 检查用户权限
     */
    protected function checkPermission($user, $permission): bool
    {
        // 超级管理员拥有所有权限
        if ($user->isSuperAdmin()) {
            return true;
        }

        // 检查直接权限
        if ($user->hasPermission($permission)) {
            return true;
        }

        // 检查通配符权限
        if ($permission === '*') {
            return true;
        }

        // 检查模式匹配权限
        if (str_contains($permission, '*')) {
            $pattern = str_replace('*', '', $permission);
            $userPermissions = $user->getAllPermissions();
            
            foreach ($userPermissions as $userPermission) {
                if (str_starts_with($userPermission, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 检查角色权限
     */
    protected function checkRolePermission($user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * 检查管理员权限
     */
    protected function checkAdminPermission($user): bool
    {
        return $user->isAdmin();
    }

    /**
     * 检查超级管理员权限
     */
    protected function checkSuperAdminPermission($user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * 检查资源所有权
     */
    protected function checkOwnership($user, $resource): bool
    {
        // 如果是超级管理员，允许访问所有资源
        if ($user->isSuperAdmin()) {
            return true;
        }

        // 检查资源是否有user_id字段
        if (isset($resource->user_id)) {
            return $resource->user_id === $user->id;
        }

        // 检查资源是否有user_id字段（关联关系）
        if (method_exists($resource, 'user') && $resource->user) {
            return $resource->user->id === $user->id;
        }

        return false;
    }

    /**
     * 检查用户是否可以管理指定用户
     */
    protected function canManageUser($user, $targetUser): bool
    {
        return $user->canManageUser($targetUser);
    }

    /**
     * 检查用户是否可以管理指定角色
     */
    protected function canManageRole($user, $targetRole): bool
    {
        return $user->hasRole('super_admin') || 
               ($user->hasRole('admin') && $targetRole->level < 80);
    }

    /**
     * 获取权限错误信息
     */
    protected function getPermissionErrorMessage(string $permission): string
    {
        $permissionMessages = [
            'users.create' => '您没有创建用户的权限',
            'users.update' => '您没有更新用户的权限',
            'users.delete' => '您没有删除用户的权限',
            'products.create' => '您没有创建产品的权限',
            'products.update' => '您没有更新产品的权限',
            'products.delete' => '您没有删除产品的权限',
            'orders.create' => '您没有创建订单的权限',
            'orders.update' => '您没有更新订单的权限',
            'orders.delete' => '您没有删除订单的权限',
            'inquiries.create' => '您没有创建询价的权限',
            'inquiries.update' => '您没有更新询价的权限',
            'inquiries.delete' => '您没有删除询价的权限',
            'settings.update' => '您没有更新设置的权限',
            'reports.view' => '您没有查看报表的权限',
            'analytics.view' => '您没有查看分析的权限',
        ];

        return $permissionMessages[$permission] ?? '权限不足';
    }

    /**
     * 记录权限检查日志
     */
    protected function logPermissionCheck($user, $permission, $granted): void
    {
        $logData = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'permission' => $permission,
            'granted' => $granted,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        \Log::channel('permissions')->info('Permission check', $logData);
    }

    /**
     * 创建权限中间件实例
     */
    public static function require(string $permission): string
    {
        return static::class . ':' . $permission;
    }

    /**
     * 创建角色中间件实例
     */
    public static function requireRole(string $role): string
    {
        return static::class . ':role:' . $role;
    }

    /**
     * 创建管理员权限中间件实例
     */
    public static function requireAdmin(): string
    {
        return static::class . ':admin';
    }

    /**
     * 创建超级管理员权限中间件实例
     */
    public static function requireSuperAdmin(): string
    {
        return static::class . ':super_admin';
    }
}

/**
 * 权限中间件快捷方法
 */
if (!function_exists('permission')) {
    function permission(string $permission): string
    {
        return PermissionMiddleware::require($permission);
    }
}

if (!function_exists('role')) {
    function role(string $role): string
    {
        return PermissionMiddleware::requireRole($role);
    }
}

if (!function_exists('admin')) {
    function admin(): string
    {
        return PermissionMiddleware::requireAdmin();
    }
}

if (!function_exists('superAdmin')) {
    function superAdmin(): string
    {
        return PermissionMiddleware::requireSuperAdmin();
    }
}