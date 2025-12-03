<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'level',
        'permissions',
    ];

    /**
     * 应该被转换的属性
     */
    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'permissions' => 'array',
    ];

    /**
     * 获取角色的用户
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withTimestamps();
    }

    /**
     * 获取角色的权限
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * 检查角色是否有指定权限
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->permissions && is_array($this->permissions)) {
            return in_array($permission, $this->permissions);
        }
        
        return false;
    }

    /**
     * 检查角色是否有任一指定权限
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->permissions || !is_array($this->permissions)) {
            return false;
        }
        
        return !empty(array_intersect($permissions, $this->permissions));
    }

    /**
     * 检查角色是否有所有指定权限
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->permissions || !is_array($this->permissions)) {
            return false;
        }
        
        return empty(array_diff($permissions, $this->permissions));
    }

    /**
     * 添加权限到角色
     */
    public function addPermission(string $permission): bool
    {
        if (!$this->permissions) {
            $this->permissions = [];
        }
        
        if (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
            return $this->save();
        }
        
        return true;
    }

    /**
     * 从角色移除权限
     */
    public function removePermission(string $permission): bool
    {
        if (!$this->permissions || !is_array($this->permissions)) {
            return true;
        }
        
        $key = array_search($permission, $this->permissions);
        if ($key !== false) {
            unset($this->permissions[$key]);
            $this->permissions = array_values($this->permissions);
            return $this->save();
        }
        
        return true;
    }

    /**
     * 批量设置权限
     */
    public function setPermissions(array $permissions): bool
    {
        $this->permissions = $permissions;
        return $this->save();
    }

    /**
     * 获取可用的角色列表
     */
    public static function getAvailableRoles(): array
    {
        return [
            'super_admin' => '超级管理员',
            'admin' => '管理员',
            'manager' => '经理',
            'operator' => '操作员',
            'user' => '普通用户',
            'guest' => '访客',
        ];
    }

    /**
     * 获取默认角色
     */
    public static function getDefaultRole(): string
    {
        return 'user';
    }

    /**
     * 创建默认角色
     */
    public static function createDefaultRoles(): void
    {
        $roles = [
            [
                'name' => '超级管理员',
                'slug' => 'super_admin',
                'description' => '拥有系统所有权限',
                'level' => 100,
                'is_active' => true,
                'permissions' => ['*'],
            ],
            [
                'name' => '管理员',
                'slug' => 'admin',
                'description' => '拥有大部分管理权限',
                'level' => 80,
                'is_active' => true,
                'permissions' => [
                    'users.view', 'users.create', 'users.update',
                    'products.view', 'products.create', 'products.update',
                    'orders.view', 'orders.update', 'orders.create',
                    'inquiries.view', 'inquiries.update', 'inquiries.create',
                    'reports.view', 'analytics.view',
                    'settings.view', 'settings.update',
                ],
            ],
            [
                'name' => '经理',
                'slug' => 'manager',
                'description' => '拥有部门管理权限',
                'level' => 60,
                'is_active' => true,
                'permissions' => [
                    'users.view', 'products.view', 'orders.view', 'inquiries.view',
                    'orders.update', 'inquiries.update',
                    'reports.view', 'analytics.view',
                ],
            ],
            [
                'name' => '操作员',
                'slug' => 'operator',
                'description' => '拥有基本操作权限',
                'level' => 40,
                'is_active' => true,
                'permissions' => [
                    'products.view', 'orders.view', 'inquiries.view',
                    'orders.create', 'inquiries.create',
                ],
            ],
            [
                'name' => '普通用户',
                'slug' => 'user',
                'description' => '拥有基本用户权限',
                'level' => 20,
                'is_active' => true,
                'permissions' => [
                    'products.view', 'orders.view', 'inquiries.view',
                    'orders.create', 'inquiries.create',
                    'profile.view', 'profile.update',
                ],
            ],
            [
                'name' => '访客',
                'slug' => 'guest',
                'description' => '只读权限',
                'level' => 10,
                'is_active' => true,
                'permissions' => [
                    'products.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            self::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }

    /**
     * 检查角色是否可以管理指定角色
     */
    public function canManageRole(Role $targetRole): bool
    {
        return $this->level > $targetRole->level;
    }

    /**
     * 获取角色优先级描述
     */
    public function getLevelDescription(): string
    {
        $descriptions = [
            100 => '最高权限',
            80 => '高级管理',
            60 => '中级管理',
            40 => '基础操作',
            20 => '标准用户',
            10 => '只读访问',
        ];

        return $descriptions[$this->level] ?? '未知级别';
    }

    /**
     * 作用域：获取角色用户数量
     */
    public function getUserCount(): int
    {
        return $this->users()->count();
    }

    /**
     * 作用域：检查角色是否为管理员级别
     */
    public function isAdmin(): bool
    {
        return $this->level >= 80;
    }

    /**
     * 作用域：检查角色是否为超级管理员
     */
    public function isSuperAdmin(): bool
    {
        return $this->level >= 100;
    }
}