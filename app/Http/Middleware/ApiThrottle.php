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
     * 增强版：支持多级限流和动态调整
     */
    protected $limits = [
        'default' => [
            'requests' => 60,    // 每分钟请求数
            'minutes' => 1,      // 时间窗口（分钟）
            'burst' => 10,       // 突发请求限制
            'decay' => 1,        // 衰减系数
        ],
        'auth' => [
            'requests' => 120,   // 认证用户每分钟请求数
            'minutes' => 1,
            'burst' => 20,
            'decay' => 1,
        ],
        'admin' => [
            'requests' => 300,   // 管理员每分钟请求数
            'minutes' => 1,
            'burst' => 50,
            'decay' => 0.8,
        ],
        'search' => [
            'requests' => 30,    // 搜索接口每分钟请求数
            'minutes' => 1,
            'burst' => 5,
            'decay' => 2,
        ],
        'inquiry' => [
            'requests' => 10,    // 询价接口每分钟请求数
            'minutes' => 1,
            'burst' => 3,
            'decay' => 3,
        ],
        'order' => [
            'requests' => 20,    // 订单接口每分钟请求数
            'minutes' => 1,
            'burst' => 5,
            'decay' => 2,
        ],
        'bulk_purchase' => [
            'requests' => 5,     // 批量采购接口限制更严格
            'minutes' => 5,      // 5分钟窗口
            'burst' => 2,
            'decay' => 5,
        ],
        'auth_login' => [
            'requests' => 5,     // 登录接口严格限制
            'minutes' => 15,     // 15分钟窗口
            'burst' => 2,
            'decay' => 10,
        ],
        'auth_register' => [
            'requests' => 3,     // 注册接口严格限制
            'minutes' => 60,     // 1小时窗口
            'burst' => 1,
            'decay' => 20,
        ],
    ];

    /**
     * Handle an incoming request.
     * 增强版：支持智能限流、异常检测和动态调整
     */
    public function handle(Request $request, Closure $next, $type = 'default')
    {
        // 获取限流配置
        $limit = $this->getLimit($request, $type);
        
        // 生成限流键
        $key = $this->getThrottleKey($request, $type);
        
        // 检查是否在黑名单中
        if ($this->isBlacklisted($request)) {
            return $this->buildBlacklistResponse();
        }
        
        // 检查异常行为
        if ($this->detectAbnormalBehavior($request, $key)) {
            $this->applyStrictThrottling($request, $key, $limit);
        }
        
        // 检查限流
        if ($this->isThrottled($key, $limit)) {
            $this->logThrottleViolation($request, $key, $limit);
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
     * 检查是否在黑名单中
     */
    protected function isBlacklisted(Request $request): bool
    {
        try {
            $ip = $request->ip();
            $blacklistKey = 'blacklist:ip:' . $ip;
            
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                return Redis::exists($blacklistKey);
            }
            
            return Cache::has($blacklistKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检测异常行为
     */
    protected function detectAbnormalBehavior(Request $request, string $key): bool
    {
        try {
            $ip = $request->ip();
            
            // 检测短时间内大量不同端点的请求
            $endpointKey = 'endpoints:' . $ip . ':' . now()->format('Y-m-d H:i');
            $currentEndpoint = $request->path();
            
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                // 记录端点访问
                Redis::sadd($endpointKey, $currentEndpoint);
                Redis::expire($endpointKey, 60);
                
                // 检查端点数量
                $endpointCount = Redis::scard($endpointKey);
                
                // 如果1分钟内访问超过20个不同端点，视为异常
                if ($endpointCount > 20) {
                    return true;
                }
                
                // 检测请求频率异常
                $requestCount = $this->getCurrentRequests($key);
                if ($requestCount > 50) { // 超过正常阈值
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 应用严格限流
     */
    protected function applyStrictThrottling(Request $request, string $key, array $limit): void
    {
        try {
            $ip = $request->ip();
            $strictKey = 'strict_throttle:' . $ip;
            
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                // 设置严格限流标记，持续15分钟
                Redis::setex($strictKey, 900, '1');
                
                // 记录异常行为
                $violationKey = 'violations:' . $ip;
                Redis::incr($violationKey);
                Redis::expire($violationKey, 86400); // 24小时
            }
        } catch (\Exception $e) {
            // 忽略失败
        }
    }

    /**
     * 构建黑名单响应
     */
    protected function buildBlacklistResponse()
    {
        return ApiResponseService::error(
            '访问被拒绝，您的IP已被限制访问',
            [
                'reason' => 'IP_BLACKLISTED',
                'duration' => '24小时',
                'contact' => 'support@manpou.jp'
            ],
            403
        );
    }

    /**
     * 记录限流违规
     */
    protected function logThrottleViolation(Request $request, string $key, array $limit): void
    {
        try {
            $logData = [
                'ip' => $request->ip(),
                'user_id' => $request->user() ? $request->user()->id : null,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'limit' => $limit['requests'],
                'key' => $key,
                'timestamp' => now()->toISOString(),
                'user_agent' => $request->userAgent(),
            ];
            
            \Log::warning('API throttle violation', $logData);
            
            // 记录到Redis用于实时监控
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $violationKey = 'throttle_violations:' . date('Y-m-d H');
                Redis::incr($violationKey);
                Redis::expire($violationKey, 86400);
            }
        } catch (\Exception $e) {
            // 忽略日志记录失败
        }
    }

    /**
     * 添加IP到黑名单
     */
    public static function addToBlacklist(string $ip, int $duration = 86400): bool
    {
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $blacklistKey = 'blacklist:ip:' . $ip;
                return Redis::setex($blacklistKey, $duration, '1');
            }
            
            return Cache::put('blacklist:ip:' . $ip, '1', $duration);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 从黑名单移除IP
     */
    public static function removeFromBlacklist(string $ip): bool
    {
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $blacklistKey = 'blacklist:ip:' . $ip;
                return Redis::del($blacklistKey) > 0;
            }
            
            return Cache::forget('blacklist:ip:' . $ip);
        } catch (\Exception $e) {
            return false;
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
                    'type_breakdown' => [],
                    'blacklisted_ips' => 0,
                    'violations_today' => 0,
                ];
                
                foreach ($keys as $key) {
                    $count = Redis::get($key) ?? 0;
                    
                    if (strpos($key, ':user:') !== false) {
                        $stats['user_requests'] += $count;
                    } elseif (strpos($key, ':ip:') !== false) {
                        $stats['ip_requests'] += $count;
                    }
                    
                    // 统计类型
                    foreach (['default', 'auth', 'admin', 'search', 'inquiry', 'order', 'bulk_purchase', 'auth_login', 'auth_register'] as $type) {
                        if (strpos($key, ':' . $type . ':') !== false) {
                            if (!isset($stats['type_breakdown'][$type])) {
                                $stats['type_breakdown'][$type] = 0;
                            }
                            $stats['type_breakdown'][$type] += $count;
                        }
                    }
                }
                
                // 统计黑名单IP
                $blacklistKeys = Redis::keys('blacklist:ip:*');
                $stats['blacklisted_ips'] = count($blacklistKeys);
                
                // 统计今日违规
                $violationKey = 'throttle_violations:' . date('Y-m-d H');
                $stats['violations_today'] = (int) Redis::get($violationKey);
                
                return $stats;
            }
        } catch (\Exception $e) {
            // 忽略统计失败
        }
        
        return [
            'total_keys' => 'N/A',
            'user_requests' => 'N/A',
            'ip_requests' => 'N/A',
            'type_breakdown' => [],
            'blacklisted_ips' => 'N/A',
            'violations_today' => 'N/A',
        ];
    }
}