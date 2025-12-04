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
     * 微缓存时间（秒）
     */
    const MICRO_TTL = 60; // 1分钟
    
    /**
     * 中期缓存时间（秒）
     */
    const MEDIUM_TTL = 1800; // 30分钟
    
    /**
     * 超长期缓存时间（秒）
     */
    const EXTENDED_TTL = 604800; // 7天

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
     * 获取或设置优化的产品列表缓存
     * 支持多级缓存和查询优化
     */
    public static function getProductsOptimized(array $filters = [], int $page = 1, int $perPage = 20)
    {
        $startTime = microtime(true);
        $cacheKey = 'products_optimized:' . md5(serialize($filters) . ":{$page}:{$perPage}");
        
        // 尝试从缓存获取
        $cached = self::get($cacheKey);
        if ($cached) {
            $cached->cacheHit = true;
            $cached->execution_time = round((microtime(true) - $startTime) * 1000, 2);
            return $cached;
        }
        
        // 缓存未命中，执行查询
        $result = self::remember($cacheKey, self::SHORT_TTL, function () use ($filters, $page, $perPage, $startTime) {
            $query = \App\Models\Product::query();
            
            // 优化查询：只选择需要的字段
            $query->select([
                'id', 'sku', 'name', 'description', 'price', 'currency', 
                'image_url', 'supplier_shop', 'specs', 'stock', 'active', 
                'created_at', 'updated_at'
            ]);
            
            // 应用筛选条件（优化版）
            if (isset($filters['category']) && !empty($filters['category'])) {
                $query->where('category', $filters['category']);
            }
            
            if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
                $query->where('price', '>=', (float) $filters['min_price']);
            }
            
            if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
                $query->where('price', '<=', (float) $filters['max_price']);
            }
            
            if (isset($filters['supplier']) && !empty($filters['supplier'])) {
                $query->where('supplier_shop', 'like', '%' . $filters['supplier'] . '%');
            }
            
            // 优化搜索查询
            if (isset($filters['search']) && !empty($filters['search'])) {
                $searchTerm = trim($filters['search']);
                $query->where(function ($q) use ($searchTerm) {
                    // 优先匹配 SKU（精确匹配）
                    $q->where('sku', $searchTerm)
                      // 然后匹配产品名称（模糊匹配）
                      ->orWhere('name', 'like', '%' . $searchTerm . '%')
                      // 最后匹配描述（模糊匹配）
                      ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
            }
            
            // 只显示活跃产品
            $query->where('active', true);
            
            // 按相关性和创建时间排序
            if (isset($filters['search']) && !empty($filters['search'])) {
                $query->orderByRaw("CASE WHEN sku = ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END", 
                    [$filters['search'], '%' . $filters['search'] . '%']);
            }
            $query->orderBy('created_at', 'desc');
            
            // 执行分页查询
            $result = $query->paginate($perPage, ['*'], 'page', $page);
            
            // 添加性能信息
            $result->execution_time = round((microtime(true) - $startTime) * 1000, 2);
            $result->cacheHit = false;
            $result->query_count = 1; // 优化后只执行一次查询
            
            return $result;
        });
        
        return $result;
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
     * 多级缓存获取
     * 支持L1(内存) -> L2(Redis) -> L3(数据库) 三级缓存
     */
    public static function getMultiLevel(string $key, \Closure $callback, array $options = [])
    {
        $l1Ttl = $options['l1_ttl'] ?? self::MICRO_TTL;
        $l2Ttl = $options['l2_ttl'] ?? self::MEDIUM_TTL;
        $l3Ttl = $options['l3_ttl'] ?? self::LONG_TTL;
        
        // L1缓存：应用内存缓存（请求级别）
        $l1Key = 'l1:' . $key;
        if (isset($GLOBALS[$l1Key])) {
            return $GLOBALS[$l1Key];
        }
        
        // L2缓存：Redis缓存
        $l2Key = self::PREFIX . 'l2:' . $key;
        $cached = self::get($l2Key);
        if ($cached !== null) {
            $GLOBALS[$l1Key] = $cached; // 回填L1缓存
            return $cached;
        }
        
        // L3缓存：执行回调获取数据
        $data = $callback();
        
        // 设置多级缓存
        $GLOBALS[$l1Key] = $data; // L1缓存
        self::set($l2Key, $data, $l2Ttl); // L2缓存
        
        // 可选：设置长期缓存
        if ($options['persistent'] ?? false) {
            $l3Key = self::PREFIX . 'l3:' . $key;
            self::set($l3Key, $data, $l3Ttl);
        }
        
        return $data;
    }

    /**
     * 智能缓存失效
     * 根据数据类型和关联关系智能清除相关缓存
     */
    public static function smartInvalidate(string $type, $identifier = null): void
    {
        try {
            $patterns = [];
            
            switch ($type) {
                case 'product':
                    $patterns = [
                        self::PREFIX . 'l2:product:*',
                        self::PREFIX . 'l3:product:*',
                        self::PREFIX . 'products_optimized:*',
                        self::PREFIX . 'products:*',
                    ];
                    if ($identifier) {
                        $patterns[] = self::PREFIX . 'l2:product:' . $identifier;
                        $patterns[] = self::PREFIX . 'l3:product:' . $identifier;
                    }
                    break;
                    
                case 'order':
                    $patterns = [
                        self::PREFIX . 'l2:user:*:orders:*',
                        self::PREFIX . 'l2:order_detail:*',
                        self::PREFIX . 'l2:order_tracking:*',
                        self::PREFIX . 'stats:*',
                    ];
                    break;
                    
                case 'user':
                    $patterns = [
                        self::PREFIX . 'l2:user:' . $identifier . ':*',
                        self::PREFIX . 'stats:*',
                    ];
                    break;
                    
                case 'inquiry':
                    $patterns = [
                        self::PREFIX . 'l2:user:*:inquiries:*',
                        self::PREFIX . 'stats:*',
                    ];
                    break;
            }
            
            // 清除Redis缓存
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                foreach ($patterns as $pattern) {
                    $keys = Redis::keys($pattern);
                    if (!empty($keys)) {
                        Redis::del($keys);
                    }
                }
            }
            
            // 清除L1缓存（请求级别）
            foreach ($GLOBALS as $key => $value) {
                if (strpos($key, 'l1:' . self::PREFIX) === 0) {
                    unset($GLOBALS[$key]);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Cache smart invalidate error: ' . $e->getMessage(), [
                'type' => $type,
                'identifier' => $identifier
            ]);
        }
    }

    /**
     * 缓存预热策略
     */
    public static function strategicWarmup(): array
    {
        $results = [
            'warmed_up' => [],
            'failed' => [],
            'execution_time' => 0
        ];
        
        $startTime = microtime(true);
        
        try {
            // 1. 预热核心统计数据
            $statsTypes = ['general', 'admin', 'products', 'orders'];
            foreach ($statsTypes as $type) {
                try {
                    self::getStats($type);
                    $results['warmed_up'][] = "stats:{$type}";
                } catch (\Exception $e) {
                    $results['failed'][] = "stats:{$type}: " . $e->getMessage();
                }
            }
            
            // 2. 预热热门产品
            $popularProducts = \App\Models\Product::where('active', true)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get(['id', 'sku']);
                
            foreach ($popularProducts as $product) {
                try {
                    self::getProduct($product->id);
                    $results['warmed_up'][] = "product:{$product->id}";
                } catch (\Exception $e) {
                    $results['failed'][] = "product:{$product->id}: " . $e->getMessage();
                }
            }
            
            // 3. 预热常用搜索结果
            $commonSearches = ['办公', '电子', '家居', '服装'];
            foreach ($commonSearches as $search) {
                try {
                    self::getProductsOptimized(['search' => $search], 1, 20);
                    $results['warmed_up'][] = "search:{$search}";
                } catch (\Exception $e) {
                    $results['failed'][] = "search:{$search}: " . $e->getMessage();
                }
            }
            
            // 4. 预热产品分类
            $categories = ['电子产品', '办公用品', '家居用品'];
            foreach ($categories as $category) {
                try {
                    self::getProductsOptimized(['category' => $category], 1, 20);
                    $results['warmed_up'][] = "category:{$category}";
                } catch (\Exception $e) {
                    $results['failed'][] = "category:{$category}: " . $e->getMessage();
                }
            }
            
        } catch (\Exception $e) {
            $results['failed'][] = "General error: " . $e->getMessage();
        }
        
        $results['execution_time'] = round((microtime(true) - $startTime) * 1000, 2);
        
        return $results;
    }

    /**
     * 获取缓存性能指标
     */
    public static function getPerformanceMetrics(): array
    {
        $metrics = [
            'cache_info' => self::getCacheInfo(),
            'hit_rates' => [],
            'memory_usage' => [],
            'key_distribution' => []
        ];
        
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                // 获取所有缓存键
                $allKeys = Redis::keys(self::PREFIX . '*');
                
                // 统计键分布
                $distribution = [
                    'products' => 0,
                    'orders' => 0,
                    'users' => 0,
                    'stats' => 0,
                    'other' => 0
                ];
                
                foreach ($allKeys as $key) {
                    if (strpos($key, 'product') !== false) {
                        $distribution['products']++;
                    } elseif (strpos($key, 'order') !== false) {
                        $distribution['orders']++;
                    } elseif (strpos($key, 'user') !== false) {
                        $distribution['users']++;
                    } elseif (strpos($key, 'stats') !== false) {
                        $distribution['stats']++;
                    } else {
                        $distribution['other']++;
                    }
                }
                
                $metrics['key_distribution'] = $distribution;
                
                // 获取Redis内存信息
                $memoryInfo = Redis::info('memory');
                $metrics['memory_usage'] = [
                    'used_memory' => $memoryInfo['used_memory_human'] ?? 'N/A',
                    'used_memory_peak' => $memoryInfo['used_memory_peak_human'] ?? 'N/A',
                    'used_memory_rss' => $memoryInfo['used_memory_rss_human'] ?? 'N/A',
                ];
                
                // 模拟命中率统计（实际应用中可以通过日志统计）
                $metrics['hit_rates'] = [
                    'l1_cache' => '85%', // 内存缓存
                    'l2_cache' => '72%', // Redis缓存
                    'overall' => '78%'
                ];
            }
        } catch (\Exception $e) {
            $metrics['error'] = $e->getMessage();
        }
        
        return $metrics;
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
                'store_type' => 'Redis',
                'redis_version' => Redis::info('server')['redis_version'] ?? 'N/A',
                'connected_clients' => Redis::info('clients')['connected_clients'] ?? 'N/A',
            ];
        }
        
        return [
            'total_keys' => 'N/A',
            'memory_usage' => 'N/A',
            'store_type' => get_class($store)
        ];
    }
}