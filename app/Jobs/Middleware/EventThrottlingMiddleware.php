<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Cache;

class EventThrottlingMiddleware
{
    /**
     * 处理任务
     */
    public function handle(object $job, callable $next): void
    {
        $throttleKey = $this->getThrottleKey($job);
        $throttleLimit = $this->getThrottleLimit($job);
        $throttleWindow = $this->getThrottleWindow($job);

        // 检查是否需要节流
        if ($this->shouldThrottle($throttleKey, $throttleLimit, $throttleWindow)) {
            $this->handleThrottling($job, $throttleKey, $throttleWindow);
            return;
        }

        // 记录处理
        $this->recordProcessing($throttleKey, $throttleWindow);

        // 继续处理任务
        $next($job);
    }

    /**
     * 获取节流键
     */
    protected function getThrottleKey(object $job): string
    {
        if (method_exists($job, 'event')) {
            $eventName = class_basename($job->event);
            
            // 根据事件类型获取更具体的节流键
            if (method_exists($job->event, 'getUserId')) {
                $userId = $job->event->getUserId();
                return "events:throttle:{$eventName}:user:{$userId}";
            }
            
            if (method_exists($job->event, 'getProductId')) {
                $productId = $job->event->getProductId();
                return "events:throttle:{$eventName}:product:{$productId}";
            }
            
            return "events:throttle:{$eventName}";
        }

        return 'events:throttle:default';
    }

    /**
     * 获取节流限制
     */
    protected function getThrottleLimit(object $job): int
    {
        if (method_exists($job, 'event') && method_exists($job->event, 'getPriority')) {
            $priority = $job->event->getPriority();
            
            return match (true) {
                $priority >= 10 => 10,   // 高优先级：每秒10次
                $priority >= 5 => 5,     // 中优先级：每秒5次
                default => 2,            // 低优先级：每秒2次
            };
        }

        return 2;
    }

    /**
     * 获取节流窗口（秒）
     */
    protected function getThrottleWindow(object $job): int
    {
        return 1; // 1秒窗口
    }

    /**
     * 检查是否需要节流
     */
    protected function shouldThrottle(string $key, int $limit, int $window): bool
    {
        $current = Cache::get($key, 0);
        return $current >= $limit;
    }

    /**
     * 记录处理
     */
    protected function recordProcessing(string $key, int $window): void
    {
        Cache::increment($key);
        Cache::add($key . ':expires', now()->addSeconds($window), $window);
    }

    /**
     * 处理节流
     */
    protected function handleThrottling(object $job, string $key, int $window): void
    {
        // 获取过期时间
        $expiresAt = Cache::get($key . ':expires');
        $delay = $expiresAt ? $expiresAt->diffInSeconds(now()) : $window;

        // 延迟释放任务
        $job->release($delay);

        // 记录节流日志
        \Log::warning('Event job throttled', [
            'job_class' => get_class($job),
            'throttle_key' => $key,
            'delay_seconds' => $delay,
            'released_at' => now()->toISOString()
        ]);
    }
}