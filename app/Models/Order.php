<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\EncryptionService;

class Order extends Model
{
    /**
     * 敏感字段列表（需要加密存储）
     */
    protected $encryptedFields = [
        'shipping_address',
        'notes',
    ];

    protected $fillable = [
        'order_id', // 修正字段名
        'user_id',
        'total_amount',
        'currency',
        'status',
        'status_message',
        'shipping_address',
        'notes',
        'contact_info',
        'order_type', // 'single' or 'bulk_purchase'
        'domestic_tracking_number',
        'international_tracking_number',
        'total_fee_cny',
        'total_fee_jpy',
    ];

    protected $hidden = [
        'shipping_address', // 隐藏原始敏感字段
        'notes', // 隐藏原始敏感字段
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_fee_cny' => 'decimal:2',
        'total_fee_jpy' => 'decimal:2',
        'contact_info' => 'array',
    ];

    /**
     * 订单状态常量
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * 模型启动事件
     */
    protected static function booted(): void
    {
        // 保存前加密敏感字段
        static::saving(function (Order $order) {
            $order->encryptSensitiveFields();
        });

        // 获取后解密敏感字段
        static::retrieved(function (Order $order) {
            $order->decryptSensitiveFields();
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
                    \Log::warning("Failed to decrypt {$field} for order {$this->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * 获取掩码后的收货地址
     */
    public function getMaskedShippingAddressAttribute(): ?string
    {
        if (empty($this->shipping_address)) {
            return null;
        }
        
        try {
            // 如果是加密的，先解密
            $address = EncryptionService::isEncrypted($this->shipping_address) 
                ? EncryptionService::decrypt($this->shipping_address) 
                : $this->shipping_address;
                
            return EncryptionService::maskSensitiveData($address);
        } catch (\Exception $e) {
            return '***';
        }
    }

    /**
     * 安全地获取收货地址（需要权限）
     */
    public function getSecureShippingAddressAttribute(): ?string
    {
        if (empty($this->shipping_address)) {
            return null;
        }
        
        try {
            return EncryptionService::isEncrypted($this->shipping_address) 
                ? EncryptionService::decrypt($this->shipping_address) 
                : $this->shipping_address;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 安全地获取备注（需要权限）
     */
    public function getSecureNotesAttribute(): ?string
    {
        if (empty($this->notes)) {
            return null;
        }
        
        try {
            return EncryptionService::isEncrypted($this->notes) 
                ? EncryptionService::decrypt($this->notes) 
                : $this->notes;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取订单的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取订单的所有订单项
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * 获取订单的物流信息
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    /**
     * 检查订单是否可以取消
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * 检查订单是否可以发货
     */
    public function canBeShipped(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * 更新订单状态
     */
    public function updateStatus(string $status, ?string $message = null): void
    {
        $this->status = $status;
        if ($message) {
            $this->status_message = $message;
        }
        $this->save();
        
        // 清除相关缓存
        \App\Services\CacheService::smartInvalidate('order', $this->id);
    }

    /**
     * 生成唯一的订单ID
     */
    public static function generateOrderId(): string
    {
        do {
            $orderId = 'YO-' . date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('order_id', $orderId)->exists());
        
        return $orderId;
    }

    /**
     * 获取订单的安全信息（用于API响应）
     */
    public function toSecureArray(): array
    {
        return [
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'status_message' => $this->status_message,
            'masked_shipping_address' => $this->masked_shipping_address,
            'order_type' => $this->order_type,
            'domestic_tracking_number' => $this->domestic_tracking_number,
            'international_tracking_number' => $this->international_tracking_number,
            'total_fee_cny' => $this->total_fee_cny,
            'total_fee_jpy' => $this->total_fee_jpy,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * 获取订单的完整信息（仅管理员可用）
     */
    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'status_message' => $this->status_message,
            'shipping_address' => $this->secure_shipping_address,
            'notes' => $this->secure_notes,
            'contact_info' => $this->contact_info,
            'order_type' => $this->order_type,
            'domestic_tracking_number' => $this->domestic_tracking_number,
            'international_tracking_number' => $this->international_tracking_number,
            'total_fee_cny' => $this->total_fee_cny,
            'total_fee_jpy' => $this->total_fee_jpy,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * 获取订单状态的可读描述
     */
    public function getStatusDescription(): string
    {
        $descriptions = [
            self::STATUS_PENDING => '待处理',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_SHIPPED => '已发货',
            self::STATUS_DELIVERED => '已送达',
            self::STATUS_RETURNED => '已退货',
            self::STATUS_CANCELLED => '已取消',
        ];
        
        return $descriptions[$this->status] ?? '未知状态';
    }
}