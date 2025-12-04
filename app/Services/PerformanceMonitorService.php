<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PerformanceMonitorService
{
    /**
     * 记录查询性能
     */
    public static function logQueryPerformance(string $operation, float $executionTime, array $context = []): void
    {
        try {
            $logData = [
                'operation' => $operation,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'timestamp' => now()->toISOString(),
                'context' => $context,
            ];
            
            // 记录到日志
            Log::info('Query Performance', $logData);
            
            // 记录到Redis用于实时监控
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $key = 'perf:queries:' . date('Y-m-d H');
                Redis::incr($key);
                Redis::expire($key, 86400);
                
                // 记录平均执行时间
                $avgKey = 'perf:avg_time:' . $operation;
                $current = Redis::get($avgKey) ?: 0;
                $count = Redis::get($avgKey . ':count') ?: 0;
                
                $newAvg = (($current * $count) + $executionTime) / ($count + 1);
                Redis::set($avgKey, $newAvg);
                Redis::set($avgKey . ':count', $count + 1);
                Redis::expire($avgKey, 86400);
                Redis::expire($avgKey . ':count', 86400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log query performance: ' . $e->getMessage());
        }
    }

    /**
     * 记录缓存命中率
     */
    public static function logCacheHit(string $cacheType, bool $hit, array $context = []): void
    {
        try {
            $logData = [
                'cache_type' => $cacheType,
                'hit' => $hit,
                'timestamp' => now()->toISOString(),
                'context' => $context,
            ];
            
            Log::debug('Cache Performance', $logData);
            
            // 记录到Redis
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $hitKey = 'perf:cache_hits:' . $cacheType;
                $missKey = 'perf:cache_misses:' . $cacheType;
                
                if ($hit) {
                    Redis::incr($hitKey);
                } else {
                    Redis::incr($missKey);
                }
                
                Redis::expire($hitKey, 86400);
                Redis::expire($missKey, 86400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log cache performance: ' . $e->getMessage());
        }
    }

    /**
     * 记录API限流事件
     */
    public static function logThrottleEvent(string $ip, ?int $userId, string $endpoint, array $limit): void
    {
        try {
            $logData = [
                'ip' => $ip,
                'user_id' => $userId,
                'endpoint' => $endpoint,
                'limit' => $limit['requests'],
                'window_minutes' => $limit['minutes'],
                'timestamp' => now()->toISOString(),
            ];
            
            Log::warning('API Throttle Event', $logData);
            
            // 记录到Redis
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $key = 'perf:throttle_events:' . date('Y-m-d H');
                Redis::incr($key);
                Redis::expire($key, 86400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log throttle event: ' . $e->getMessage());
        }
    }

    /**
     * 记录加密操作性能
     */
    public static function logEncryptionPerformance(string $operation, float $executionTime, int $dataSize): void
    {
        try {
            $logData = [
                'operation' => $operation,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'data_size_bytes' => $dataSize,
                'timestamp' => now()->toISOString(),
            ];
            
            Log::info('Encryption Performance', $logData);
            
            // 记录到Redis
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $key = 'perf:encryption:' . $operation;
                Redis::incr($key);
                Redis::expire($key, 86400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log encryption performance: ' . $e->getMessage());
        }
    }

    /**
     * 获取性能统计报告
     */
    public static function getPerformanceReport(): array
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'period' => 'last_24_hours',
            'metrics' => []
        ];
        
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                // 查询性能统计
                $report['metrics']['queries'] = [
                    'total_count' => 0,
                    'average_time_ms' => 0,
                    'slow_queries' => 0,
                ];
                
                for ($i = 0; $i < 24; $i++) {
                    $hourKey = 'perf:queries:' . date('Y-m-d H', strtotime("-{$i} hours"));
                    $count = (int) Redis::get($hourKey);
                    $report['metrics']['queries']['total_count'] += $count;
                }
                
                // 缓存命中率
                $report['metrics']['cache'] = [
                    'hits' => 0,
                    'misses' => 0,
                    'hit_rate' => 0,
                ];
                
                $cacheTypes = ['products', 'orders', 'users', 'stats'];
                foreach ($cacheTypes as $type) {
                    $hits = (int) Redis::get('perf:cache_hits:' . $type);
                    $misses = (int) Redis::get('perf:cache_misses:' . $type);
                    
                    $report['metrics']['cache']['hits'] += $hits;
                    $report['metrics']['cache']['misses'] += $misses;
                }
                
                $totalCacheRequests = $report['metrics']['cache']['hits'] + $report['metrics']['cache']['misses'];
                if ($totalCacheRequests > 0) {
                    $report['metrics']['cache']['hit_rate'] = round(
                        ($report['metrics']['cache']['hits'] / $totalCacheRequests) * 100, 2
                    );
                }
                
                // 限流事件统计
                $report['metrics']['throttle'] = [
                    'events' => 0,
                    'blacklisted_ips' => 0,
                ];
                
                for ($i = 0; $i < 24; $i++) {
                    $hourKey = 'perf:throttle_events:' . date('Y-m-d H', strtotime("-{$i} hours"));
                    $events = (int) Redis::get($hourKey);
                    $report['metrics']['throttle']['events'] += $events;
                }
                
                $blacklistKeys = Redis::keys('blacklist:ip:*');
                $report['metrics']['throttle']['blacklisted_ips'] = count($blacklistKeys);
                
                // 加密操作统计
                $report['metrics']['encryption'] = [
                    'encrypt_operations' => (int) Redis::get('perf:encryption:encrypt') ?: 0,
                    'decrypt_operations' => (int) Redis::get('perf:encryption:decrypt') ?: 0,
                    'hash_operations' => (int) Redis::get('perf:encryption:hash') ?: 0,
                ];
            }
        } catch (\Exception $e) {
            $report['error'] = $e->getMessage();
        }
        
        return $report;
    }

    /**
     * 记录内存使用情况
     */
    public static function logMemoryUsage(string $operation): void
    {
        try {
            $memoryUsage = memory_get_usage(true);
            $peakMemory = memory_get_peak_usage(true);
            
            $logData = [
                'operation' => $operation,
                'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
                'timestamp' => now()->toISOString(),
            ];
            
            Log::debug('Memory Usage', $logData);
            
            // 记录到Redis
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $key = 'perf:memory:' . $operation;
                Redis::lpush($key, json_encode($logData));
                Redis::ltrim($key, 0, 99); // 保留最近100条记录
                Redis::expire($key, 86400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log memory usage: ' . $e->getMessage());
        }
    }

    /**
     * 检查系统健康状态
     */
    public static function getSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'checks' => []
        ];
        
        try {
            // 检查Redis连接
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                try {
                    Redis::ping();
                    $health['checks']['redis'] = [
                        'status' => 'healthy',
                        'response_time_ms' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2)
                    ];
                } catch (\Exception $e) {
                    $health['checks']['redis'] = [
                        'status' => 'unhealthy',
                        'error' => $e->getMessage()
                    ];
                    $health['status'] = 'degraded';
                }
            }
            
            // 检查数据库连接
            try {
                \DB::select('SELECT 1');
                $health['checks']['database'] = [
                    'status' => 'healthy'
                ];
            } catch (\Exception $e) {
                $health['checks']['database'] = [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage()
                ];
                $health['status'] = 'unhealthy';
            }
            
            // 检查缓存系统
            try {
                $testKey = 'health_check_' . time();
                CacheService::set($testKey, 'test', 60);
                $retrieved = CacheService::get($testKey);
                CacheService::forget($testKey);
                
                if ($retrieved === 'test') {
                    $health['checks']['cache'] = [
                        'status' => 'healthy'
                    ];
                } else {
                    throw new \Exception('Cache read/write test failed');
                }
            } catch (\Exception $e) {
                $health['checks']['cache'] = [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage()
                ];
                $health['status'] = 'degraded';
            }
            
            // 检查加密系统
            try {
                $validation = EncryptionService::validateEncryptionSystem();
                $health['checks']['encryption'] = [
                    'status' => $validation['status'] === 'healthy' ? 'healthy' : 'degraded',
                    'details' => $validation
                ];
                
                if ($validation['status'] !== 'healthy') {
                    $health['status'] = 'degraded';
                }
            } catch (\Exception $e) {
                $health['checks']['encryption'] = [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage()
                ];
                $health['status'] = 'degraded';
            }
            
        } catch (\Exception $e) {
            $health['status'] = 'error';
            $health['error'] = $e->getMessage();
        }
        
        return $health;
    }
}