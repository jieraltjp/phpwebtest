<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
=======
use App\Services\EncryptionService;
>>>>>>> e02925059be84e1d598297122cc9f58b91fcf09d

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * 敏感字段列表（需要加密存储）
     */
    protected $encryptedFields = [
        'phone',
        'address',
    ];

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
        'company',
        'phone',
        'address',
<<<<<<< HEAD
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
=======
        'active',
        'last_login_at',
>>>>>>> e02925059be84e1d598297122cc9f58b91fcf09d
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'phone', // 隐藏原始敏感字段
        'address', // 隐藏原始敏感字段
    ];

    /**
     * The attributes that should be cast.
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
            'last_login_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    /**
<<<<<<< HEAD
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
=======
     * 模型启动事件
     */
    protected static function booted(): void
    {
        // 保存前加密敏感字段
        static::saving(function (User $user) {
            $user->encryptSensitiveFields();
        });

        // 获取后解密敏感字段（通过访问器）
        static::retrieved(function (User $user) {
            $user->decryptSensitiveFields();
        });
    }

    /**
     * 加密敏感字段
     */
    public function encryptSensitiveFields(): void
    {
        foreach ($this->encryptedFields as $field) {
            if (isset($this->attributes[$field]) && !empty($this->attributes[$field])) {
                // 检查是否已经加密
                if (!EncryptionService::isEncrypted($this->attributes[$field])) {
                    $this->attributes[$field] = EncryptionService::encrypt($this->attributes[$field]);
                }
            }
        }
    }

    /**
     * 解密敏感字段
     */
    public function decryptSensitiveFields(): void
    {
        foreach ($this->encryptedFields as $field) {
            if (isset($this->attributes[$field]) && !empty($this->attributes[$field])) {
                try {
                    $this->attributes[$field] = EncryptionService::decrypt($this->attributes[$field]);
                } catch (\Exception $e) {
                    // 解密失败时保持原值
                    \Log::warning("Failed to decrypt {$field} for user {$this->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * 获取掩码后的手机号
     */
    public function getMaskedPhoneAttribute(): ?string
    {
        if (empty($this->phone)) {
            return null;
        }
        
        try {
            // 如果是加密的，先解密
            $phone = EncryptionService::isEncrypted($this->phone) 
                ? EncryptionService::decrypt($this->phone) 
                : $this->phone;
                
            return EncryptionService::maskSensitiveData($phone, 'phone');
        } catch (\Exception $e) {
            return '***';
        }
    }

    /**
     * 获取掩码后的地址
     */
    public function getMaskedAddressAttribute(): ?string
    {
        if (empty($this->address)) {
            return null;
        }
        
        try {
            // 如果是加密的，先解密
            $address = EncryptionService::isEncrypted($this->address) 
                ? EncryptionService::decrypt($this->address) 
                : $this->address;
                
            return EncryptionService::maskSensitiveData($address);
        } catch (\Exception $e) {
            return '***';
        }
    }

    /**
     * 获取掩码后的邮箱
     */
    public function getMaskedEmailAttribute(): ?string
    {
        if (empty($this->email)) {
            return null;
        }
        
        return EncryptionService::maskSensitiveData($this->email, 'email');
    }

    /**
     * 安全地获取手机号（需要权限）
     */
    public function getSecurePhoneAttribute(): ?string
    {
        if (empty($this->phone)) {
            return null;
        }
        
        try {
            return EncryptionService::isEncrypted($this->phone) 
                ? EncryptionService::decrypt($this->phone) 
                : $this->phone;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 安全地获取地址（需要权限）
     */
    public function getSecureAddressAttribute(): ?string
    {
        if (empty($this->address)) {
            return null;
        }
        
        try {
            return EncryptionService::isEncrypted($this->address) 
                ? EncryptionService::decrypt($this->address) 
                : $this->address;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 检查用户是否是管理员
     */
    public function isAdmin(): bool
    {
        return $this->username === 'admin' || $this->email === 'admin@manpou.jp';
    }

    /**
     * 获取用户的安全信息（用于API响应）
     */
    public function toSecureArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->masked_email,
            'company' => $this->company,
            'masked_phone' => $this->masked_phone,
            'masked_address' => $this->masked_address,
            'active' => $this->active,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * 获取用户的完整信息（仅管理员可用）
     */
    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'company' => $this->company,
            'phone' => $this->secure_phone,
            'address' => $this->secure_address,
            'active' => $this->active,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
>>>>>>> e02925059be84e1d598297122cc9f58b91fcf09d
    }

    /**
     * 更新最后登录时间
     */
<<<<<<< HEAD
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
=======
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * 验证用户权限
     */
    public function hasPermission(string $permission): bool
    {
        // 简单的权限系统，可以扩展
        if ($this->isAdmin()) {
            return true;
        }
        
        $permissions = [
            'view_orders' => true,
            'create_orders' => true,
            'view_products' => true,
            'create_inquiries' => true,
        ];
        
        return $permissions[$permission] ?? false;
>>>>>>> e02925059be84e1d598297122cc9f58b91fcf09d
    }
}
