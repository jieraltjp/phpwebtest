<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApiThrottleService
{
    /**
     * 缓存键前缀
     */
    const CACHE_PREFIX = 'api_throttle:';
    
    /**
     * 黑名单前缀
     */
    const BLACKLIST_PREFIX = 'blacklist:';
    
    /**
     * 统计前缀
     */
    const STATS_PREFIX = 'throttle_stats:';
    
    /**
     * 违规记录前缀
     */
    const VIOLATION_PREFIX = 'throttle_violations:';
    
    /**
     * 获取实时限流统计
     */
    public function getRealTimeStats(): array
    {
        try {
            $stats = [
                'timestamp' => now()->toISOString(),
                'current_minute' => $this->getCurrentMinuteStats(),
                'last_hour' => $this->getLastHourStats(),
                'last_24_hours' => $this->getLast24HoursStats(),
                'top_endpoints' => $this->getTopEndpoints(10),
                'top_ips' => $this->getTopIPs(10),
                'blacklisted_ips' => $this->getBlacklistedIPs(),
                'anomalies' => $this->detectAnomalies(),
            ];
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('获取限流统计失败: ' . $e->getMessage());
            return $this->getEmptyStats();
        }
    }
    
    /**
     * 获取当前分钟统计
     */
    protected function getCurrentMinuteStats(): array
    {
        $currentMinute = now()->format('Y-m-d H:i');
        $pattern = self::CACHE_PREFIX . '*:' . $currentMinute . '*';
        
        $stats = [
            'total_requests' => 0,
            'user_requests' => 0,
            'ip_requests' => 0,
            'type_breakdown' => [],
            'unique_users' => 0,
            'unique_ips' => 0,
        ];
        
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            $keys = Redis::keys($pattern);
            $uniqueUsers = [];
            $uniqueIPs = [];
            
            foreach ($keys as $key) {
                $count = (int) Redis::get($key);
                $stats['total_requests'] += $count;
                
                // 解析键名获取类型
                if (strpos($key, ':user:') !== false) {
                    $stats['user_requests'] += $count;
                    $userId = $this->extractUserIdFromKey($key);
                    if ($userId) $uniqueUsers[] = $userId;
                } elseif (strpos($key, ':ip:') !== false) {
                    $stats['ip_requests'] += $count;
                    $ip = $this->extractIPFromKey($key);
                    if ($ip) $uniqueIPs[] = $ip;
                }
                
                // 统计类型
                $type = $this->extractTypeFromKey($key);
                if ($type) {
                    if (!isset($stats['type_breakdown'][$type])) {
                        $stats['type_breakdown'][$type] = 0;
                    }
                    $stats['type_breakdown'][$type] += $count;
                }
            }
            
            $stats['unique_users'] = count(array_unique($uniqueUsers));
            $stats['unique_ips'] = count(array_unique($uniqueIPs));
        }
        
        return $stats;
    }
    
    /**
     * 获取最近1小时统计
     */
    protected function getLastHourStats(): array
    {
        $stats = [
            'total_requests' => 0,
            'violations' => 0,
            'blacklist_additions' => 0,
            'hourly_trend' => [],
        ];
        
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            // 获取过去60分钟的请求数据
            for ($i = 0; $i < 60; $i++) {
                $minute = now()->subMinutes($i)->format('Y-m-d H:i');
                $pattern = self::CACHE_PREFIX . '*:' . $minute . '*';
                $keys = Redis::keys($pattern);
                
                $minuteRequests = 0;
                foreach ($keys as $key) {
                    $minuteRequests += (int) Redis::get($key);
                }
                
                $stats['hourly_trend'][] = [
                    'minute' => $minute,
                    'requests' => $minuteRequests,
                ];
                
                $stats['total_requests'] += $minuteRequests;
            }
            
            // 获取违规记录
            $violationPattern = self::VIOLATION_PREFIX . '*';
            $violationKeys = Redis::keys($violationPattern);
            
            foreach ($violationKeys as $key) {
                $stats['violations'] += (int) Redis::get($key);
            }
            
            // 获取黑名单记录
            $blacklistPattern = self::BLACKLIST_PREFIX . '*';
            $blacklistKeys = Redis::keys($blacklistPattern);
            $stats['blacklist_additions'] = count($blacklistKeys);
        }
        
        // 反转趋势数组，使其从最早到最新
        $stats['hourly_trend'] = array_reverse($stats['hourly_trend']);
        
        return $stats;
    }
    
    /**
     * 获取最近24小时统计
     */
    protected function getLast24HoursStats(): array
    {
        $stats = [
            'total_requests' => 0,
            'daily_breakdown' => [],
            'peak_hour' => null,
            'peak_requests' => 0,
        ];
        
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            // 按小时统计
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d H');
                $pattern = self::CACHE_PREFIX . '*:' . $hour . '*';
                $keys = Redis::keys($pattern);
                
                $hourRequests = 0;
                foreach ($keys as $key) {
                    $hourRequests += (int) Redis::get($key);
                }
                
                $stats['daily_breakdown'][] = [
                    'hour' => $hour,
                    'requests' => $hourRequests,
                ];
                
                $stats['total_requests'] += $hourRequests;
                
                if ($hourRequests > $stats['peak_requests']) {
                    $stats['peak_requests'] = $hourRequests;
                    $stats['peak_hour'] = $hour;
                }
            }
            
            // 反转数组
            $stats['daily_breakdown'] = array_reverse($stats['daily_breakdown']);
        }
        
        return $stats;
    }
    
    /**
     * 获取热门端点
     */
    protected function getTopEndpoints(int $limit = 10): array
    {
        $endpoints = [];
        
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            $pattern = 'endpoints:*';
            $keys = Redis::keys($pattern);
            
            foreach ($keys as $key) {
                $ip = $this->extractIPFromKey($key);
                $endpointsList = Redis::smembers($key);
                
                foreach ($endpointsList as $endpoint) {
                    if (!isset($endpoints[$endpoint])) {
                        $endpoints[$endpoint] = 0;
                    }
                    $endpoints[$endpoint]++;
                }
            }
            
            // 排序并取前N个
            arsort($endpoints);
            $endpoints = array_slice($endpoints, 0, $limit, true);
        }
        
        return $endpoints;
    }
    
    /**
     * 获取热门IP
     */
    protected function getTopIPs(int $limit = 10): array
    {
        $ips = [];
        
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            $pattern = self::CACHE_PREFIX . 'ip:*';
            $keys = Redis::keys($pattern);
            
            foreach ($keys as $key) {
                $ip = $this->extractIPFromKey($key);
                $count = (int) Redis::get($key);
                
                if ($ip) {
                    $ips[$ip] = $count;
                }
            }
            
            // 排序并取前N个
            arsort($ips);
            $ips = array_slice($ips, 0, $limit, true);
        }
        
        return $ips;
    }
    
    /**
     * 获取黑名单IP
     */
    protected function getBlacklistedIPs(): array
    {
        $blacklistedIPs = [];
        
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            $pattern = self::BLACKLIST_PREFIX . 'ip:*';
            $keys = Redis::keys($pattern);
            
            foreach ($keys as $key) {
                $ip = $this->extractIPFromKey($key);
                $ttl = Redis::ttl($key);
                
                if ($ip) {
                    $blacklistedIPs[] = [
                        'ip' => $ip,
                        'remaining_seconds' => $ttl,
                        'blacklisted_at' => now()->subSeconds($ttl)->toISOString(),
                    ];
                }
            }
        }
        
        return $blacklistedIPs;
    }
    
    /**
     * 检测异常
     */
    protected function detectAnomalies(): array
    {
        $anomalies = [];
        
        if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
            // 检测异常高频IP
            $currentMinute = now()->format('Y-m-d H:i');
            $pattern = self::CACHE_PREFIX . 'ip:*:' . $currentMinute . '*';
            $keys = Redis::keys($pattern);
            
            foreach ($keys as $key) {
                $count = (int) Redis::get($key);
                if ($count > 100) { // 超过100次/分钟视为异常
                    $ip = $this->extractIPFromKey($key);
                    if ($ip) {
                        $anomalies[] = [
                            'type' => 'high_frequency_ip',
                            'ip' => $ip,
                            'requests_per_minute' => $count,
                            'severity' => $count > 200 ? 'critical' : 'warning',
                        ];
                    }
                }
            }
            
            // 检测异常端点访问
            $endpointPattern = 'endpoints:*';
            $endpointKeys = Redis::keys($endpointPattern);
            
            foreach ($endpointKeys as $key) {
                $endpointCount = Redis::scard($key);
                if ($endpointCount > 30) { // 访问超过30个不同端点
                    $ip = $this->extractIPFromKey($key);
                    if ($ip) {
                        $anomalies[] = [
                            'type' => 'endpoint_scanner',
                            'ip' => $ip,
                            'unique_endpoints' => $endpointCount,
                            'severity' => $endpointCount > 50 ? 'critical' : 'warning',
                        ];
                    }
                }
            }
        }
        
        return $anomalies;
    }
    
    /**
     * 添加IP到黑名单
     */
    public function addToBlacklist(string $ip, int $duration = 86400, string $reason = 'Manual'): bool
    {
        try {
            $blacklistKey = self::BLACKLIST_PREFIX . 'ip:' . $ip;
            
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                Redis::setex($blacklistKey, $duration, json_encode([
                    'reason' => $reason,
                    'blacklisted_at' => now()->toISOString(),
                    'duration' => $duration,
                ]));
                
                // 记录日志
                Log::info("IP {$ip} 已添加到黑名单", [
                    'reason' => $reason,
                    'duration' => $duration,
                ]);
                
                return true;
            }
            
            return Cache::put($blacklistKey, json_encode([
                'reason' => $reason,
                'blacklisted_at' => now()->toISOString(),
                'duration' => $duration,
            ]), $duration);
        } catch (\Exception $e) {
            Log::error('添加IP到黑名单失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 从黑名单移除IP
     */
    public function removeFromBlacklist(string $ip): bool
    {
        try {
            $blacklistKey = self::BLACKLIST_PREFIX . 'ip:' . $ip;
            
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $result = Redis::del($blacklistKey) > 0;
                
                if ($result) {
                    Log::info("IP {$ip} 已从黑名单移除");
                }
                
                return $result;
            }
            
            return Cache::forget($blacklistKey);
        } catch (\Exception $e) {
            Log::error('从黑名单移除IP失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 清理过期数据
     */
    public function cleanupExpiredData(): int
    {
        $cleaned = 0;
        
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                // 清理超过24小时的限流记录
                $pattern = self::CACHE_PREFIX . '*';
                $keys = Redis::keys($pattern);
                
                foreach ($keys as $key) {
                    $ttl = Redis::ttl($key);
                    if ($ttl === -1) { // 没有过期时间的键
                        // 检查键的时间戳，如果超过24小时则删除
                        $timestamp = $this->extractTimestampFromKey($key);
                        if ($timestamp && $timestamp < now()->subHours(24)->timestamp) {
                            Redis::del($key);
                            $cleaned++;
                        }
                    }
                }
                
                // 清理超过7天的违规记录
                $violationPattern = self::VIOLATION_PREFIX . '*';
                $violationKeys = Redis::keys($violationPattern);
                
                foreach ($violationKeys as $key) {
                    $date = $this->extractDateFromKey($key);
                    if ($date && $date < now()->subDays(7)) {
                        Redis::del($key);
                        $cleaned++;
                    }
                }
                
                Log::info("清理过期数据完成", ['cleaned_keys' => $cleaned]);
            }
        } catch (\Exception $e) {
            Log::error('清理过期数据失败: ' . $e->getMessage());
        }
        
        return $cleaned;
    }
    
    /**
     * 获取空统计数据
     */
    protected function getEmptyStats(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'current_minute' => [
                'total_requests' => 'N/A',
                'user_requests' => 'N/A',
                'ip_requests' => 'N/A',
                'type_breakdown' => [],
                'unique_users' => 'N/A',
                'unique_ips' => 'N/A',
            ],
            'last_hour' => [
                'total_requests' => 'N/A',
                'violations' => 'N/A',
                'blacklist_additions' => 'N/A',
                'hourly_trend' => [],
            ],
            'last_24_hours' => [
                'total_requests' => 'N/A',
                'daily_breakdown' => [],
                'peak_hour' => 'N/A',
                'peak_requests' => 'N/A',
            ],
            'top_endpoints' => [],
            'top_ips' => [],
            'blacklisted_ips' => [],
            'anomalies' => [],
        ];
    }
    
    /**
     * 从键中提取用户ID
     */
    protected function extractUserIdFromKey(string $key): ?int
    {
        if (preg_match('/user:(\d+):/', $key, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
    
    /**
     * 从键中提取IP
     */
    protected function extractIPFromKey(string $key): ?string
    {
        if (preg_match('/ip:([0-9.]+):/', $key, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * 从键中提取类型
     */
    protected function extractTypeFromKey(string $key): ?string
    {
        if (preg_match('/:([a-z_]+):\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $key, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * 从键中提取时间戳
     */
    protected function extractTimestampFromKey(string $key): ?int
    {
        if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2})$/', $key, $matches)) {
            return strtotime($matches[1]);
        }
        return null;
    }
    
    /**
     * 从键中提取日期
     */
    protected function extractDateFromKey(string $key): ?Carbon
    {
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $key, $matches)) {
            return Carbon::createFromFormat('Y-m-d', $matches[1]);
        }
        return null;
    }
}