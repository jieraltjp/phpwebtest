<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'currency',
        'image_url',
        'supplier_shop',
        'specs',
        'stock',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'specs' => 'array',
        'active' => 'boolean',
    ];

    /**
     * 获取包含此产品的订单项
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'sku', 'sku');
    }

    /**
     * 检查产品是否有足够库存
     */
    public function hasStock($quantity)
    {
        return $this->stock >= $quantity;
    }

    /**
     * 减少库存
     */
    public function decreaseStock($quantity)
    {
        if ($this->hasStock($quantity)) {
            $this->stock -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * 增加库存
     */
    public function increaseStock($quantity)
    {
        $this->stock += $quantity;
        $this->save();
    }
}