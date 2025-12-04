<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\EncryptionService;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        'active',
        'last_login_at',
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
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    /**
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
    }

    /**
     * 更新最后登录时间
     */
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
    }
}
