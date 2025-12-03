<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'group',
        'is_active',
    ];

    /**
     * 应该被转换的属性
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 获取权限的角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * 获取权限的用户
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withTimestamps();
    }

    /**
     * 获取可用的权限组
     */
    public static function getAvailableGroups(): array
    {
        return [
            'users' => '用户管理',
            'products' => '产品管理',
            'orders' => '订单管理',
            'inquiries' => '询价管理',
            'reports' => '报表管理',
            'analytics' => '数据分析',
            'settings' => '系统设置',
            'profile' => '个人资料',
        ];
    }

    /**
     * 获取每个组的权限列表
     */
    public static function getPermissionsByGroup(): array
    {
        return [
            'users' => [
                'users.view' => '查看用户',
                'users.create' => '创建用户',
                'users.update' => '更新用户',
                'users.delete' => '删除用户',
                'users.approve' => '审核用户',
                'users.manage_roles' => '管理用户角色',
            ],
            'products' => [
                'products.view' => '查看产品',
                'products.create' => '创建产品',
                'products.update' => '更新产品',
                'products.delete' => '删除产品',
                'products.manage_stock' => '管理库存',
                'products.manage_categories' => '管理分类',
            ],
            'orders' => [
                'orders.view' => '查看订单',
                'orders.create' => '创建订单',
                'orders.update' => '更新订单',
                'orders.delete' => '删除订单',
                'orders.approve' => '审核订单',
                'orders.manage_status' => '管理订单状态',
                'orders.export' => '导出订单',
            ],
            'inquiries' => [
                'inquiries.view' => '查看询价',
                'inquiries.create' => '创建询价',
                'inquiries.update' => '更新询价',
                'inquiries.delete' => '删除询价',
                'inquiries.respond' => '回复询价',
                'inquiries.export' => '导出询价',
            ],
            'reports' => [
                'reports.view' => '查看报表',
                'reports.create' => '创建报表',
                'reports.update' => '更新报表',
                'reports.delete' => '删除报表',
                'reports.export' => '导出报表',
            ],
            'analytics' => [
                'analytics.view' => '查看分析',
                'analytics.create' => '创建分析',
                'analytics.update' => '更新分析',
                'analytics.delete' => '删除分析',
                'analytics.export' => '导出分析',
            ],
            'settings' => [
                'settings.view' => '查看设置',
                'settings.update' => '更新设置',
                'settings.manage_system' => '管理系统设置',
                'settings.manage_permissions' => '管理权限设置',
            ],
            'profile' => [
                'profile.view' => '查看个人资料',
                'profile.update' => '更新个人资料',
                'profile.change_password' => '修改密码',
                'profile.manage_api_keys' => '管理API密钥',
            ],
        ];
    }

    /**
     * 创建默认权限
     */
    public static function createDefaultPermissions(): void
    {
        $permissionsByGroup = self::getPermissionsByGroup();
        
        foreach ($permissionsByGroup as $group => $permissions) {
            foreach ($permissions as $slug => $description) {
                self::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $description,
                        'slug' => $slug,
                        'description' => $description,
                        'group' => $group,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * 按组获取权限
     */
    public static function getByGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('group', $group)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * 获取所有激活的权限
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get();
    }

    /**
     * 检查权限是否属于指定组
     */
    public function belongsToGroup(string $group): bool
    {
        return $this->group === $group;
    }

    /**
     * 获取权限的完整描述
     */
    public function getFullDescription(): string
    {
        $groups = self::getAvailableGroups();
        $groupName = $groups[$this->group] ?? $this->group;
        
        return "[{$groupName}] {$this->name}";
    }

    /**
     * 搜索权限
     */
    public static function search(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%")
                  ->orWhere('group', 'like', "%{$query}%");
            })
            ->orderBy('group')
            ->orderBy('name')
            ->get();
    }

    /**
     * 批量激活权限
     */
    public static function batchActivate(array $permissionIds): int
    {
        return self::whereIn('id', $permissionIds)
            ->update(['is_active' => true]);
    }

    /**
     * 批量停用权限
     */
    public static function batchDeactivate(array $permissionIds): int
    {
        return self::whereIn('id', $permissionIds)
            ->update(['is_active' => false]);
    }

    /**
     * 获取权限使用统计
     */
    public function getUsageStats(): array
    {
        return [
            'roles_count' => $this->roles()->count(),
            'users_count' => $this->users()->count(),
            'total_users' => $this->roles()->withCount('users')->get()->sum('users_count'),
        ];
    }
}