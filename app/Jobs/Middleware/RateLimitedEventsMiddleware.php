<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Redis;

class RateLimitedEventsMiddleware
{
    /**
     * 处理任务
     */
    public function handle(object $job, callable $next): void
    {
        $eventKey = $this->getEventKey($job);
        $maxAttempts = $this->getMaxAttempts($job);
        $decaySeconds = $this->getDecaySeconds($job);

        // 检查速率限制
        if ($this->isRateLimited($eventKey, $maxAttempts, $decaySeconds)) {
            $this->releaseJob($job, $decaySeconds);
            return;
        }

        // 增加计数器
        $this->incrementCounter($eventKey, $decaySeconds);

        // 继续处理任务
        $next($job);
    }

    /**
     * 获取事件键
     */
    protected function getEventKey(object $job): string
    {
        if (method_exists($job, 'event')) {
            $eventName = class_basename($job->event);
            return "events:rate_limit:{$eventName}";
        }

        return 'events:rate_limit:default';
    }

    /**
     * 获取最大尝试次数
     */
    protected function getMaxAttempts(object $job): int
    {
        if (method_exists($job, 'event') && method_exists($job->event, 'getPriority')) {
            $priority = $job->event->getPriority();
            
            // 根据优先级设置不同的速率限制
            return match (true) {
                $priority >= 10 => 1000,  // 高优先级：每分钟1000次
                $priority >= 5 => 500,    // 中优先级：每分钟500次
                default => 100,           // 低优先级：每分钟100次
            };
        }

        return 100;
    }

    /**
     * 获取衰减时间（秒）
     */
    protected function getDecaySeconds(object $job): int
    {
        return 60; // 1分钟
    }

    /**
     * 检查是否超过速率限制
     */
    protected function isRateLimited(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        try {
            $current = Redis::get($key);
            return $current && (int)$current >= $maxAttempts;
        } catch (\Exception $e) {
            // Redis 连接失败时不限制
            return false;
        }
    }

    /**
     * 增加计数器
     */
    protected function incrementCounter(string $key, int $decaySeconds): void
    {
        try {
            Redis::incr($key);
            Redis::expire($key, $decaySeconds);
        } catch (\Exception $e) {
            // Redis 连接失败时忽略
        }
    }

    /**
     * 释放任务
     */
    protected function releaseJob(object $job, int $delay): void
    {
        $job->release($delay);
    }
}