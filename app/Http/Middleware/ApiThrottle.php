<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Services\ApiResponseService;

class ApiThrottle
{
    /**
     * 限流配置
     */
    protected $limits = [
        'default' => [
            'requests' => 60,    // 每分钟请求数
            'minutes' => 1,      // 时间窗口（分钟）
        ],
        'auth' => [
            'requests' => 120,   // 认证用户每分钟请求数
            'minutes' => 1,
        ],
        'admin' => [
            'requests' => 300,   // 管理员每分钟请求数
            'minutes' => 1,
        ],
        'search' => [
            'requests' => 30,    // 搜索接口每分钟请求数
            'minutes' => 1,
        ],
        'inquiry' => [
            'requests' => 10,    // 询价接口每分钟请求数
            'minutes' => 1,
        ],
        'order' => [
            'requests' => 20,    // 订单接口每分钟请求数
            'minutes' => 1,
        ],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $type = 'default')
    {
        // 获取限流配置
        $limit = $this->getLimit($request, $type);
        
        // 生成限流键
        $key = $this->getThrottleKey($request, $type);
        
        // 检查限流
        if ($this->isThrottled($key, $limit)) {
            return $this->buildThrottleResponse($limit);
        }
        
        // 记录请求
        $this->incrementRequests($key, $limit);
        
        // 添加响应头
        $response = $next($request);
        $this->addThrottleHeaders($response, $key, $limit);
        
        return $response;
    }

    /**
     * 获取限流配置
     */
    protected function getLimit(Request $request, string $type): array
    {
        // 根据用户类型调整限流
        if ($request->user()) {
            if ($request->user()->isAdmin()) {
                $type = 'admin';
            } else {
                $type = 'auth';
            }
        }
        
        return $this->limits[$type] ?? $this->limits['default'];
    }

    /**
     * 生成限流键
     */
    protected function getThrottleKey(Request $request, string $type): string
    {
        $identifier = 'api_throttle:';
        
        // 使用用户ID或IP地址作为标识符
        if ($request->user()) {
            $identifier .= 'user:' . $request->user()->id;
        } else {
            $identifier .= 'ip:' . $request->ip();
        }
        
        // 添加接口类型
        $identifier .= ':' . $type;
        
        // 添加时间窗口
        $identifier .= ':' . now()->format('Y-m-d H:i');
        
        return $identifier;
    }

    /**
     * 检查是否被限流
     */
    protected function isThrottled(string $key, array $limit): bool
    {
        $current = $this->getCurrentRequests($key);
        
        return $current >= $limit['requests'];
    }

    /**
     * 获取当前请求数
     */
    protected function getCurrentRequests(string $key): int
    {
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                return Redis::get($key) ?? 0;
            }
            
            return Cache::get($key, 0);
        } catch (\Exception $e) {
            // 如果Redis不可用，返回0允许请求
            return 0;
        }
    }

    /**
     * 增加请求计数
     */
    protected function incrementRequests(string $key, array $limit): void
    {
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                // 使用Redis的原子操作
                $ttl = $limit['minutes'] * 60;
                Redis::incr($key);
                Redis::expire($key, $ttl);
            } else {
                // 使用缓存
                $current = Cache::get($key, 0);
                Cache::put($key, $current + 1, $limit['minutes'] * 60);
            }
        } catch (\Exception $e) {
            // 忽略计数失败，允许请求继续
        }
    }

    /**
     * 构建限流响应
     */
    protected function buildThrottleResponse(array $limit)
    {
        return ApiResponseService::error(
            '请求过于频繁，请稍后重试',
            [
                'limit' => $limit['requests'],
                'window' => $limit['minutes'] . ' 分钟',
                'retry_after' => $limit['minutes'] * 60
            ],
            429
        );
    }

    /**
     * 添加限流响应头
     */
    protected function addThrottleHeaders($response, string $key, array $limit): void
    {
        $current = $this->getCurrentRequests($key);
        $remaining = max(0, $limit['requests'] - $current);
        
        $response->headers->set('X-RateLimit-Limit', $limit['requests']);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($limit['minutes'])->timestamp);
    }

    /**
     * 清除用户限流记录
     */
    public static function clearUserThrottle(int $userId): void
    {
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $pattern = 'api_throttle:user:' . $userId . ':*';
                $keys = Redis::keys($pattern);
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            }
        } catch (\Exception $e) {
            // 忽略清除失败
        }
    }

    /**
     * 清除IP限流记录
     */
    public static function clearIpThrottle(string $ip): void
    {
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $pattern = 'api_throttle:ip:' . $ip . ':*';
                $keys = Redis::keys($pattern);
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            }
        } catch (\Exception $e) {
            // 忽略清除失败
        }
    }

    /**
     * 获取限流统计
     */
    public static function getThrottleStats(): array
    {
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $pattern = 'api_throttle:*';
                $keys = Redis::keys($pattern);
                
                $stats = [
                    'total_keys' => count($keys),
                    'user_requests' => 0,
                    'ip_requests' => 0,
                    'type_breakdown' => []
                ];
                
                foreach ($keys as $key) {
                    $count = Redis::get($key) ?? 0;
                    
                    if (strpos($key, ':user:') !== false) {
                        $stats['user_requests'] += $count;
                    } elseif (strpos($key, ':ip:') !== false) {
                        $stats['ip_requests'] += $count;
                    }
                    
                    // 统计类型
                    foreach (['default', 'auth', 'admin', 'search', 'inquiry', 'order'] as $type) {
                        if (strpos($key, ':' . $type . ':') !== false) {
                            if (!isset($stats['type_breakdown'][$type])) {
                                $stats['type_breakdown'][$type] = 0;
                            }
                            $stats['type_breakdown'][$type] += $count;
                        }
                    }
                }
                
                return $stats;
            }
        } catch (\Exception $e) {
            // 忽略统计失败
        }
        
        return [
            'total_keys' => 'N/A',
            'user_requests' => 'N/A',
            'ip_requests' => 'N/A',
            'type_breakdown' => []
        ];
    }
}