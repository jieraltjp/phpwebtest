<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * 缓存键前缀
     */
    const PREFIX = 'b2b_portal:';
    
    /**
     * 默认缓存时间（秒）
     */
    const DEFAULT_TTL = 3600; // 1小时
    
    /**
     * 长期缓存时间（秒）
     */
    const LONG_TTL = 86400; // 24小时
    
    /**
     * 短期缓存时间（秒）
     */
    const SHORT_TTL = 300; // 5分钟

    /**
     * 获取缓存数据
     */
    public static function get(string $key, $default = null)
    {
        return Cache::get(self::PREFIX . $key, $default);
    }

    /**
     * 设置缓存数据
     */
    public static function set(string $key, $value, int $ttl = self::DEFAULT_TTL): bool
    {
        return Cache::put(self::PREFIX . $key, $value, $ttl);
    }

    /**
     * 记住缓存数据（如果不存在则设置）
     */
    public static function remember(string $key, $ttl, \Closure $callback)
    {
        return Cache::remember(self::PREFIX . $key, $ttl, $callback);
    }

    /**
     * 永久记住缓存数据
     */
    public static function rememberForever(string $key, \Closure $callback)
    {
        return Cache::rememberForever(self::PREFIX . $key, $callback);
    }

    /**
     * 删除缓存数据
     */
    public static function forget(string $key): bool
    {
        return Cache::forget(self::PREFIX . $key);
    }

    /**
     * 清除所有缓存
     */
    public static function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * 检查缓存是否存在
     */
    public static function has(string $key): bool
    {
        return Cache::has(self::PREFIX . $key);
    }

    /**
     * 获取或设置产品缓存
     */
    public static function getProduct(int $productId)
    {
        return self::remember("product:{$productId}", self::LONG_TTL, function () use ($productId) {
            return \App\Models\Product::find($productId);
        });
    }

    /**
     * 获取或设置产品列表缓存
     */
    public static function getProducts(array $filters = [], int $page = 1, int $perPage = 20)
    {
        $cacheKey = 'products:' . md5(serialize($filters) . ":{$page}:{$perPage}");
        
        return self::remember($cacheKey, self::SHORT_TTL, function () use ($filters, $page, $perPage) {
            $query = \App\Models\Product::query();
            
            // 应用筛选条件
            if (isset($filters['category'])) {
                $query->where('category', $filters['category']);
            }
            
            if (isset($filters['min_price'])) {
                $query->where('price', '>=', $filters['min_price']);
            }
            
            if (isset($filters['max_price'])) {
                $query->where('price', '<=', $filters['max_price']);
            }
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }
            
            return $query->paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * 获取或设置用户订单缓存
     */
    public static function getUserOrders(int $userId, int $page = 1, int $perPage = 20)
    {
        $cacheKey = "user:{$userId}:orders:{$page}:{$perPage}";
        
        return self::remember($cacheKey, self::SHORT_TTL, function () use ($userId, $page, $perPage) {
            return \App\Models\Order::where('user_id', $userId)
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * 获取或设置用户询价缓存
     */
    public static function getUserInquiries(int $userId, int $page = 1, int $perPage = 20)
    {
        $cacheKey = "user:{$userId}:inquiries:{$page}:{$perPage}";
        
        return self::remember($cacheKey, self::SHORT_TTL, function () use ($userId, $page, $perPage) {
            return \App\Models\Inquiry::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * 获取或设置统计数据缓存
     */
    public static function getStats(string $type = 'general')
    {
        $cacheKey = "stats:{$type}";
        
        return self::remember($cacheKey, self::SHORT_TTL, function () use ($type) {
            switch ($type) {
                case 'general':
                    return [
                        'total_users' => \App\Models\User::count(),
                        'total_products' => \App\Models\Product::count(),
                        'total_orders' => \App\Models\Order::count(),
                        'total_inquiries' => \App\Models\Inquiry::count(),
                        'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
                        'pending_inquiries' => \App\Models\Inquiry::where('status', 'pending')->count(),
                    ];
                    
                case 'admin':
                    return [
                        'total_users' => \App\Models\User::count(),
                        'total_orders' => \App\Models\Order::count(),
                        'total_revenue' => \App\Models\Order::sum('total_amount'),
                        'pending_shipments' => \App\Models\Order::where('status', 'shipped')->count(),
                    ];
                    
                default:
                    return [];
            }
        });
    }

    /**
     * 清除产品相关缓存
     */
    public static function clearProductCache(int $productId = null): void
    {
        if ($productId) {
            self::forget("product:{$productId}");
        }
        
        // 清除产品列表缓存（使用模式匹配）
        $pattern = self::PREFIX . 'products:*';
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        } else {
            // 对于文件缓存，只能清除所有
            self::flush();
        }
    }

    /**
     * 清除用户相关缓存
     */
    public static function clearUserCache(int $userId): void
    {
        self::forget("user:{$userId}:orders");
        self::forget("user:{$userId}:inquiries");
        
        // 清除统计数据缓存
        self::forget('stats:general');
        self::forget('stats:admin');
    }

    /**
     * 清除订单相关缓存
     */
    public static function clearOrderCache(): void
    {
        self::forget('stats:general');
        self::forget('stats:admin');
        
        // 清除用户订单缓存（使用模式匹配）
        $pattern = self::PREFIX . 'user:*:orders:*';
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
    }

    /**
     * 清除询价相关缓存
     */
    public static function clearInquiryCache(): void
    {
        self::forget('stats:general');
        
        // 清除用户询价缓存（使用模式匹配）
        $pattern = self::PREFIX . 'user:*:inquiries:*';
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
    }

    /**
     * 预热缓存
     */
    public static function warmup(): void
    {
        // 预热统计数据
        self::getStats('general');
        self::getStats('admin');
        
        // 预热热门产品
        $popularProducts = \App\Models\Product::limit(10)->get();
        foreach ($popularProducts as $product) {
            self::getProduct($product->id);
        }
        
        // 预热产品列表
        self::getProducts([], 1, 20);
    }

    /**
     * 获取缓存系统信息
     */
    public static function getCacheInfo(): array
    {
        $store = app()->cache->getStore();
        
        if ($store instanceof \Illuminate\Cache\RedisStore) {
            $keys = Redis::keys(self::PREFIX . '*');
            return [
                'total_keys' => count($keys),
                'memory_usage' => Redis::info('memory')['used_memory_human'] ?? 'N/A',
                'store_type' => 'Redis'
            ];
        }
        
        return [
            'total_keys' => 'N/A',
            'memory_usage' => 'N/A',
            'store_type' => get_class($store)
        ];
    }
}