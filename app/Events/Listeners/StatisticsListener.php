<?php

namespace App\Events\Listeners;

use App\Events\AbstractListener;
use App\Events\Contracts\EventInterface;
use App\Events\User\UserRegisteredEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Inquiry\InquiryCreatedEvent;
use App\Events\Product\ProductViewedEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsListener extends AbstractListener
{
    protected int $priority = 5;
    protected array $supportedEvents = [
        UserRegisteredEvent::class,
        OrderCreatedEvent::class,
        InquiryCreatedEvent::class,
        ProductViewedEvent::class,
    ];

    public function handle(EventInterface $event): void
    {
        $this->safeHandle($event, function ($event) {
            switch (true) {
                case $event instanceof UserRegisteredEvent:
                    $this->handleUserRegistered($event);
                    break;
                case $event instanceof OrderCreatedEvent:
                    $this->handleOrderCreated($event);
                    break;
                case $event instanceof InquiryCreatedEvent:
                    $this->handleInquiryCreated($event);
                    break;
                case $event instanceof ProductViewedEvent:
                    $this->handleProductViewed($event);
                    break;
                default:
                    $this->log($event, 'Statistics: No handler for event type');
            }
        });
    }

    protected function handleUserRegistered(UserRegisteredEvent $event): void
    {
        // 更新用户注册统计
        $this->incrementStat('users:registered:total');
        $this->incrementStat('users:registered:' . now()->format('Y-m-d'));
        $this->incrementStat('users:registered:' . now()->format('Y-m'));
        
        // 更新用户总数缓存
        $this->updateUserCountCache();
        
        // 记录注册来源统计
        $source = $event->getMetadataField('source', 'unknown');
        $this->incrementStat("users:registered:source:{$source}");

        $this->log($event, 'User registration statistics updated', [
            'user_id' => $event->getUserId(),
            'source' => $source
        ]);
    }

    protected function handleOrderCreated(OrderCreatedEvent $event): void
    {
        // 更新订单统计
        $this->incrementStat('orders:created:total');
        $this->incrementStat('orders:created:' . now()->format('Y-m-d'));
        $this->incrementStat('orders:created:' . now()->format('Y-m'));
        
        // 更新收入统计
        $this->addToStat('revenue:total', $event->getTotalAmount());
        $this->addToStat('revenue:' . now()->format('Y-m-d'), $event->getTotalAmount());
        $this->addToStat('revenue:' . now()->format('Y-m'), $event->getTotalAmount());
        
        // 更新订单金额统计
        $this->addToStat('orders:total_amount', $event->getTotalAmount());
        
        // 更新平均订单金额
        $this->updateAverageOrderAmount();
        
        // 更新产品销售统计
        foreach ($event->getItems() as $item) {
            $this->incrementStat("products:{$item['product_id']}:sold");
            $this->addToStat("products:{$item['product_id']}:revenue", $item['total_price']);
            $this->incrementStat("products:{$item['product_id']}:quantity", $item['quantity']);
        }
        
        // 更新分类销售统计
        $this->updateCategorySalesStats($event);
        
        // 更新用户订单统计
        $this->incrementStat("users:{$event->getUserId()}:orders");
        $this->addToStat("users:{$event->getUserId()}:total_spent", $event->getTotalAmount());

        $this->log($event, 'Order statistics updated', [
            'order_id' => $event->getOrderId(),
            'total_amount' => $event->getTotalAmount(),
            'items_count' => $event->getItemsCount()
        ]);
    }

    protected function handleInquiryCreated(InquiryCreatedEvent $event): void
    {
        // 更新询价统计
        $this->incrementStat('inquiries:created:total');
        $this->incrementStat('inquiries:created:' . now()->format('Y-m-d'));
        $this->incrementStat('inquiries:created:' . now()->format('Y-m'));
        
        // 更新优先级统计
        $priority = $event->getPriority();
        $this->incrementStat("inquiries:priority:{$priority}");
        
        // 更新预算统计
        if ($event->hasBudget()) {
            $this->addToStat('inquiries:total_budget', $event->getEstimatedBudget());
            $this->incrementStat('inquiries:with_budget');
        }
        
        // 更新公司询价统计
        $this->incrementStat("companies:{$event->getCompanyName()}:inquiries");
        
        // 更新来源统计
        $source = $event->getMetadataField('source', 'unknown');
        $this->incrementStat("inquiries:source:{$source}");

        $this->log($event, 'Inquiry statistics updated', [
            'inquiry_id' => $event->getInquiryId(),
            'priority' => $priority,
            'has_budget' => $event->hasBudget()
        ]);
    }

    protected function handleProductViewed(ProductViewedEvent $event): void
    {
        // 更新产品浏览统计
        $this->incrementStat("products:{$event->getProductId()}:views");
        $this->incrementStat("products:{$event->getProductId()}:views:" . now()->format('Y-m-d'));
        
        // 更新分类浏览统计
        if ($event->getCategory()) {
            $this->incrementStat("categories:{$event->getCategory()}:views");
        }
        
        // 更新用户浏览统计（如果是认证用户）
        if ($event->isViewedByAuthenticatedUser()) {
            $this->incrementStat("users:{$event->getViewerId()}:product_views");
        }
        
        // 更新总浏览统计
        $this->incrementStat('products:views:total');
        $this->incrementStat('products:views:' . now()->format('Y-m-d'));
        
        // 更新热门产品排名
        $this->updatePopularProductsRanking();

        $this->log($event, 'Product view statistics updated', [
            'product_id' => $event->getProductId(),
            'is_authenticated' => $event->isViewedByAuthenticatedUser()
        ]);
    }

    /**
     * 增加统计计数
     */
    protected function incrementStat(string $key, int $amount = 1): void
    {
        Cache::increment("stats:{$key}", $amount);
    }

    /**
     * 添加到统计数值
     */
    protected function addToStat(string $key, float $amount): void
    {
        $current = Cache::get("stats:{$key}", 0);
        Cache::put("stats:{$key}", $current + $amount, 86400); // 24小时
    }

    /**
     * 更新用户总数缓存
     */
    protected function updateUserCountCache(): void
    {
        $count = DB::table('users')->count();
        Cache::put('stats:users:total', $count, 3600);
    }

    /**
     * 更新平均订单金额
     */
    protected function updateAverageOrderAmount(): void
    {
        $totalOrders = Cache::get('stats:orders:created:total', 0);
        $totalAmount = Cache::get('stats:orders:total_amount', 0);
        
        if ($totalOrders > 0) {
            $average = $totalAmount / $totalOrders;
            Cache::put('stats:orders:average_amount', $average, 3600);
        }
    }

    /**
     * 更新分类销售统计
     */
    protected function updateCategorySalesStats(OrderCreatedEvent $event): void
    {
        foreach ($event->getItems() as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            if ($product && $product->category) {
                $this->incrementStat("categories:{$product->category}:orders");
                $this->addToStat("categories:{$product->category}:revenue", $item['total_price']);
            }
        }
    }

    /**
     * 更新热门产品排名
     */
    protected function updatePopularProductsRanking(): void
    {
        // 异步更新热门产品排名，避免影响性能
        dispatch(function () {
            $popularProducts = DB::table('products')
                ->join('product_view_stats', 'products.id', '=', 'product_view_stats.product_id')
                ->orderBy('product_view_stats.view_count', 'desc')
                ->limit(10)
                ->get(['products.id', 'products.name', 'product_view_stats.view_count']);
            
            Cache::put('stats:products:popular', $popularProducts->toArray(), 3600);
        });
    }

    /**
     * 获取统计报告
     */
    public function getStatisticsReport(): array
    {
        return [
            'users' => [
                'total' => Cache::get('stats:users:total', 0),
                'registered_today' => Cache::get('stats:users:registered:' . now()->format('Y-m-d'), 0),
                'registered_this_month' => Cache::get('stats:users:registered:' . now()->format('Y-m'), 0),
            ],
            'orders' => [
                'total' => Cache::get('stats:orders:created:total', 0),
                'today' => Cache::get('stats:orders:created:' . now()->format('Y-m-d'), 0),
                'this_month' => Cache::get('stats:orders:created:' . now()->format('Y-m'), 0),
                'average_amount' => Cache::get('stats:orders:average_amount', 0),
                'total_revenue' => Cache::get('stats:revenue:total', 0),
            ],
            'inquiries' => [
                'total' => Cache::get('stats:inquiries:created:total', 0),
                'today' => Cache::get('stats:inquiries:created:' . now()->format('Y-m-d'), 0),
                'this_month' => Cache::get('stats:inquiries:created:' . now()->format('Y-m'), 0),
                'high_priority' => Cache::get('stats:inquiries:priority:high', 0),
                'total_budget' => Cache::get('stats:inquiries:total_budget', 0),
            ],
            'products' => [
                'total_views' => Cache::get('stats:products:views:total', 0),
                'views_today' => Cache::get('stats:products:views:' . now()->format('Y-m-d'), 0),
                'popular' => Cache::get('stats:products:popular', []),
            ],
        ];
    }
}