<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
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

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_fee_cny' => 'decimal:2',
        'total_fee_jpy' => 'decimal:2',
        'contact_info' => 'array',
    ];

    /**
     * 订单状态常量
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_SHIPPED = 'SHIPPED';
    const STATUS_DELIVERED = 'DELIVERED';
    const STATUS_RETURNED = 'RETURNED';
    const STATUS_CANCELLED = 'CANCELLED';

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
    public function orderItems()
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
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * 更新订单状态
     */
    public function updateStatus($status, $message = null)
    {
        $this->status = $status;
        if ($message) {
            $this->status_message = $message;
        }
        $this->save();
    }

    /**
     * 生成唯一的订单ID
     */
    public static function generateOrderId()
    {
        do {
            $orderId = 'YO-' . date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('order_id', $orderId)->exists());
        
        return $orderId;
    }
}