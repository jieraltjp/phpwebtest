<?php

namespace App\Events\Listeners;

use App\Events\AbstractListener;
use App\Events\Contracts\EventInterface;
use App\Events\Product\ProductCreatedEvent;
use App\Events\Product\ProductUpdatedEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\User\UserUpdatedEvent;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class CacheUpdateListener extends AbstractListener
{
    protected int $priority = 8;
    protected array $supportedEvents = [
        ProductCreatedEvent::class,
        ProductUpdatedEvent::class,
        OrderCreatedEvent::class,
        UserUpdatedEvent::class,
    ];

    public function handle(EventInterface $event): void
    {
        $this->safeHandle($event, function ($event) {
            switch (true) {
                case $event instanceof ProductCreatedEvent:
                    $this->handleProductCreated($event);
                    break;
                case $event instanceof ProductUpdatedEvent:
                    $this->handleProductUpdated($event);
                    break;
                case $event instanceof OrderCreatedEvent:
                    $this->handleOrderCreated($event);
                    break;
                case $event instanceof UserUpdatedEvent:
                    $this->handleUserUpdated($event);
                    break;
                default:
                    $this->log($event, 'Cache update: No handler for event type');
            }
        });
    }

    protected function handleProductCreated(ProductCreatedEvent $event): void
    {
        // 清除产品列表缓存
        $this->clearProductListCache();
        
        // 预热新产品缓存
        $this->warmProductCache($event->getProductId());
        
        // 清除分类缓存
        $this->clearCategoryCache($event->getCategory());

        $this->log($event, 'Product cache updated for new product', [
            'product_id' => $event->getProductId(),
            'sku' => $event->getSku()
        ]);
    }

    protected function handleProductUpdated(ProductUpdatedEvent $event): void
    {
        // 清除特定产品缓存
        $this->clearProductCache($event->getProductId());
        
        // 如果库存发生变化，清除相关缓存
        if ($event->hasStockChanged()) {
            $this->clearStockRelatedCache($event->getProductId());
            
            // 如果库存过低或售罄，清除首页推荐缓存
            if ($event->isOutOfStock() || $event->isLowStock()) {
                $this->clearHomepageCache();
            }
        }
        
        // 如果价格发生变化，清除价格相关缓存
        if ($event->hasPriceChanged()) {
            $this->clearPriceRelatedCache($event->getProductId());
        }

        // 预热更新后的产品缓存
        $this->warmProductCache($event->getProductId());

        $this->log($event, 'Product cache updated for product changes', [
            'product_id' => $event->getProductId(),
            'stock_changed' => $event->hasStockChanged(),
            'price_changed' => $event->hasPriceChanged()
        ]);
    }

    protected function handleOrderCreated(OrderCreatedEvent $event): void
    {
        // 清除用户订单相关缓存
        $this->clearUserOrderCache($event->getUserId());
        
        // 清除产品库存缓存（因为订单可能影响库存）
        foreach ($event->getProductIds() as $productId) {
            $this->clearStockRelatedCache($productId);
        }
        
        // 清除统计缓存
        $this->clearStatisticsCache();

        $this->log($event, 'Order-related cache cleared', [
            'order_id' => $event->getOrderId(),
            'user_id' => $event->getUserId(),
            'product_ids' => $event->getProductIds()
        ]);
    }

    protected function handleUserUpdated(UserUpdatedEvent $event): void
    {
        // 清除用户相关缓存
        $this->clearUserCache($event->getUserId());
        
        // 如果用户资料发生变化，清除仪表板缓存
        if ($event->hasFieldChanged('name') || $event->hasFieldChanged('email')) {
            $this->clearUserDashboardCache($event->getUserId());
        }

        $this->log($event, 'User cache updated', [
            'user_id' => $event->getUserId(),
            'changed_fields' => $event->getChangedFields()
        ]);
    }

    /**
     * 清除产品列表缓存
     */
    protected function clearProductListCache(): void
    {
        $patterns = [
            'products:list:*',
            'products:featured:*',
            'products:recent:*',
            'products:category:*'
        ];

        foreach ($patterns as $pattern) {
            CacheService::clearByPattern($pattern);
        }
    }

    /**
     * 清除特定产品缓存
     */
    protected function clearProductCache(int $productId): void
    {
        $keys = [
            "product:{$productId}",
            "product:{$productId}:details",
            "product:{$productId}:related"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * 预热产品缓存
     */
    protected function warmProductCache(int $productId): void
    {
        // 异步预热产品缓存
        dispatch(function () use ($productId) {
            $product = \App\Models\Product::find($productId);
            if ($product) {
                Cache::put("product:{$productId}", $product, 3600);
                Cache::put("product:{$productId}:details", $product->toArray(), 3600);
            }
        });
    }

    /**
     * 清除分类缓存
     */
    protected function clearCategoryCache(?string $category): void
    {
        if ($category) {
            CacheService::clearByPattern("products:category:{$category}:*");
        }
    }

    /**
     * 清除库存相关缓存
     */
    protected function clearStockRelatedCache(int $productId): void
    {
        $keys = [
            "product:{$productId}:stock",
            "products:in_stock:*",
            "products:low_stock:*",
            "homepage:featured_products"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * 清除价格相关缓存
     */
    protected function clearPriceRelatedCache(int $productId): void
    {
        $keys = [
            "product:{$productId}:price",
            "products:price_range:*"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * 清除首页缓存
     */
    protected function clearHomepageCache(): void
    {
        $keys = [
            'homepage:featured_products',
            'homepage:recent_products',
            'homepage:stats'
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * 清除用户订单缓存
     */
    protected function clearUserOrderCache(int $userId): void
    {
        $patterns = [
            "user:{$userId}:orders:*",
            "user:{$userId}:order_stats",
            "user:{$userId}:dashboard"
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                CacheService::clearByPattern($pattern);
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * 清除用户缓存
     */
    protected function clearUserCache(int $userId): void
    {
        $keys = [
            "user:{$userId}",
            "user:{$userId}:profile",
            "user:{$userId}:preferences"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * 清除用户仪表板缓存
     */
    protected function clearUserDashboardCache(int $userId): void
    {
        Cache::forget("user:{$userId}:dashboard");
        Cache::forget("user:{$userId}:stats");
    }

    /**
     * 清除统计缓存
     */
    protected function clearStatisticsCache(): void
    {
        $keys = [
            'stats:orders:daily',
            'stats:orders:weekly',
            'stats:orders:monthly',
            'stats:products:popular',
            'stats:revenue:daily'
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}