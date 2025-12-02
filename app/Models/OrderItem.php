<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'sku',
        'name',
        'quantity',
        'unit_price',
        'total_price',
        'currency',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * 获取所属订单
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 获取产品信息
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }

    /**
     * 创建订单项时自动计算总价
     */
    public static function create(array $attributes = [])
    {
        if (isset($attributes['quantity']) && isset($attributes['unit_price'])) {
            $attributes['total_price'] = $attributes['quantity'] * $attributes['unit_price'];
        }
        
        return static::query()->create($attributes);
    }
}