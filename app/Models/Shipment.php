<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'logistics_company',
        'tracking_url',
        'domestic_tracking_number',
        'international_tracking_number',
        'status',
    ];

    /**
     * 物流状态常量
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_RETURNED = 'returned';

    /**
     * 获取所属订单
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 生成物流追踪链接
     */
    public static function generateTrackingUrl($logisticsCompany, $trackingNumber)
    {
        $trackingUrls = [
            'Fedex' => "https://www.fedex.com/fedextrack/?trknbr={$trackingNumber}",
            'UPS' => "https://www.ups.com/track?tracknum={$trackingNumber}",
            'DHL' => "https://www.dhl.com/en/express/tracking.html?AWB={$trackingNumber}",
            'SF Express' => "https://www.sf-express.com/ow/zh-cn/dynamic_function/waybill/#search/bill-number/{$trackingNumber}",
            'China Post' => "https://www.17track.net/en/track?nums={$trackingNumber}",
        ];

        return $trackingUrls[$logisticsCompany] ?? "https://www.17track.net/en/track?nums={$trackingNumber}";
    }

    /**
     * 更新物流状态
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();

        // 同步更新订单状态
        if ($this->order) {
            $orderStatusMap = [
                self::STATUS_PENDING => Order::STATUS_PROCESSING,
                self::STATUS_SHIPPED => Order::STATUS_SHIPPED,
                self::STATUS_IN_TRANSIT => Order::STATUS_SHIPPED,
                self::STATUS_DELIVERED => Order::STATUS_DELIVERED,
                self::STATUS_RETURNED => Order::STATUS_RETURNED,
            ];

            if (isset($orderStatusMap[$status])) {
                $this->order->updateStatus($orderStatusMap[$status]);
            }
        }
    }
}