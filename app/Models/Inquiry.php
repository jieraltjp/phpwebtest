<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_sku',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
        'currency',
        'message',
        'contact_info',
        'status',
        'quoted_price',
        'quoted_at',
        'expires_at',
        'notes',
    ];

    /**
     * 应该被转换的属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'contact_info' => 'array',
        'quoted_price' => 'decimal:2',
        'quoted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * 获取询价所属的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取询价状态的可选值
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => '待处理',
            'quoted' => '已报价',
            'accepted' => '已接受',
            'rejected' => '已拒绝',
            'expired' => '已过期',
        ];
    }

    /**
     * 检查询价是否已过期
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * 检查询价是否可以被接受
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'quoted' && !$this->isExpired();
    }

    /**
     * 生成询价编号
     */
    public static function generateInquiryNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        
        return "INQ{$date}" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 获取格式化的总价
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return number_format($this->total_price, 2) . ' ' . $this->currency;
    }

    /**
     * 获取格式化的报价
     */
    public function getFormattedQuotedPriceAttribute(): ?string
    {
        if (!$this->quoted_price) {
            return null;
        }
        
        return number_format($this->quoted_price, 2) . ' ' . $this->currency;
    }
}