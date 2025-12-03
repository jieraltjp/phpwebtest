<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'company_name',
        'phone',
        'address',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'locale',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * 获取用户的所有角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps();
    }

    /**
     * 获取用户的所有权限
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withTimestamps();
    }

    /**
     * 获取用户的订单
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 获取用户的询价
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * 检查用户是否有指定权限
     */
    public function hasPermission(string $permission): bool
    {
        // 检查用户直接权限
        if ($this->permissions()->where('slug', $permission)->exists()) {
            return true;
        }

        // 检查通过角色获得的权限
        return $this->roles()
            ->where('is_active', true)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    /**
     * 检查用户是否有任一指定权限
     */
    public function hasAnyPermission(array $permissions): bool
    {
        // 检查用户直接权限
        $userPermissions = $this->permissions()->whereIn('slug', $permissions)->pluck('slug')->toArray();
        if (!empty($userPermissions)) {
            return true;
        }

        // 检查通过角色获得的权限
        $rolePermissions = $this->roles()
            ->where('is_active', true)
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions->pluck('slug')->toArray();
            })
            ->unique()
            ->toArray();

        return !empty(array_intersect($permissions, $rolePermissions));
    }

    /**
     * 检查用户是否有所有指定权限
     */
    public function hasAllPermissions(array $permissions): bool
    {
        // 检查用户直接权限
        $userPermissions = $this->permissions()->whereIn('slug', $permissions)->pluck('slug')->toArray();
        
        // 检查通过角色获得的权限
        $rolePermissions = $this->roles()
            ->where('is_active', true)
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions->pluck('slug')->toArray();
            })
            ->unique()
            ->toArray();

        $allPermissions = array_unique(array_merge($userPermissions, $rolePermissions));

        return empty(array_diff($permissions, $allPermissions));
    }

    /**
     * 检查用户是否有指定角色
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * 检查用户是否有任一指定角色
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    /**
     * 检查用户是否为管理员
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * 检查用户是否为超级管理员
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * 检查用户是否为活跃状态
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * 分配角色给用户
     */
    public function assignRole(Role $role): bool
    {
        if (!$this->roles()->where('role_id', $role->id)->exists()) {
            return $this->roles()->attach($role->id);
        }
        
        return true;
    }

    /**
     * 移除用户的角色
     */
    public function removeRole(Role $role): bool
    {
        return $this->roles()->detach($role->id);
    }

    /**
     * 同步用户角色
     */
    public function syncRoles(array $roleIds): array
    {
        return $this->roles()->sync($roleIds);
    }

    /**
     * 分配权限给用户
     */
    public function assignPermission(Permission $permission): bool
    {
        if (!$this->permissions()->where('permission_id', $permission->id)->exists()) {
            return $this->permissions()->attach($permission->id);
        }
        
        return true;
    }

    /**
     * 移除用户的权限
     */
    public function removePermission(Permission $permission): bool
    {
        return $this->permissions()->detach($permission->id);
    }

    /**
     * 同步用户权限
     */
    public function syncPermissions(array $permissionIds): array
    {
        return $this->permissions()->sync($permissionIds);
    }

    /**
     * 获取用户的所有有效权限
     */
    public function getAllPermissions(): array
    {
        // 获取用户直接权限
        $userPermissions = $this->permissions()
            ->where('is_active', true)
            ->pluck('slug')
            ->toArray();

        // 获取通过角色获得的权限
        $rolePermissions = $this->roles()
            ->where('is_active', true)
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions
                    ->where('is_active', true)
                    ->pluck('slug')
                    ->toArray();
            })
            ->toArray();

        // 合并并去重
        return array_unique(array_merge($userPermissions, $rolePermissions));
    }

    /**
     * 检查用户是否可以管理指定用户
     */
    public function canManageUser(User $targetUser): bool
    {
        // 不能管理自己
        if ($this->id === $targetUser->id) {
            return false;
        }

        // 超级管理员可以管理所有用户
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 管理员只能管理非管理员用户
        if ($this->isAdmin() && !$targetUser->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * 更新最后登录时间
     */
    public function updateLastLogin(): bool
    {
        return $this->update(['last_login_at' => now()]);
    }

    /**
     * 获取用户权限统计
     */
    public function getPermissionStats(): array
    {
        $directPermissions = $this->permissions()->count();
        $roleCount = $this->roles()->count();
        $rolePermissions = $this->roles()
            ->withCount('permissions')
            ->get()
            ->sum('permissions_count');

        return [
            'direct_permissions' => $directPermissions,
            'roles_count' => $roleCount,
            'role_permissions' => $rolePermissions,
            'total_permissions' => $directPermissions + $rolePermissions,
        ];
    }
}
