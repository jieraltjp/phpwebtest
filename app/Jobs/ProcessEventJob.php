<?php

namespace App\Jobs;

use App\Events\Contracts\EventInterface;
use App\Events\EventDispatcher;
use App\Services\EventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     */
    public int $tries = 3;

    /**
     * 任务超时时间（秒）
     */
    public int $timeout = 120;

    /**
     * 重试延迟（秒）
     */
    public int $retryAfter = 60;

    /**
     * 事件实例
     */
    protected EventInterface $event;

    /**
     * 队列名称
     */
    public string $queue = 'events';

    /**
     * 创建新的任务实例
     */
    public function __construct(EventInterface $event)
    {
        $this->event = $event;
        
        // 根据事件优先级设置队列
        $this->queue = $this->determineQueue($event);
    }

    /**
     * 执行任务
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        
        try {
            Log::info('Processing async event', [
                'event_id' => $this->event->getId(),
                'event_name' => $this->event->getName(),
                'queue' => $this->queue,
                'attempt' => $this->attempts()
            ]);

            // 重新分发事件进行同步处理
            EventService::dispatcher()->dispatchSync($this->event);

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Async event processed successfully', [
                'event_id' => $this->event->getId(),
                'event_name' => $this->event->getName(),
                'processing_time_ms' => $processingTime,
                'attempt' => $this->attempts()
            ]);

        } catch (\Throwable $exception) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Async event processing failed', [
                'event_id' => $this->event->getId(),
                'event_name' => $this->event->getName(),
                'error' => $exception->getMessage(),
                'processing_time_ms' => $processingTime,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries
            ]);

            throw $exception;
        }
    }

    /**
     * 任务失败时的处理
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Async event job failed permanently', [
            'event_id' => $this->event->getId(),
            'event_name' => $this->event->getName(),
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'exception' => $exception->getTraceAsString()
        ]);

        // 记录失败事件到失败事件表
        $this->recordFailedEvent($exception);
    }

    /**
     * 根据事件确定队列
     */
    protected function determineQueue(EventInterface $event): string
    {
        $priority = $event->getPriority();
        
        if ($priority >= 10) {
            return 'events-high';
        } elseif ($priority >= 5) {
            return 'events-medium';
        } else {
            return 'events-low';
        }
    }

    /**
     * 记录失败事件
     */
    protected function recordFailedEvent(\Throwable $exception): void
    {
        try {
            \DB::table('failed_events')->insert([
                'event_id' => $this->event->getId(),
                'event_name' => $this->event->getName(),
                'event_data' => json_encode($this->event->getData()),
                'event_metadata' => json_encode($this->event->getMetadata()),
                'error_message' => $exception->getMessage(),
                'error_trace' => $exception->getTraceAsString(),
                'attempts' => $this->attempts(),
                'failed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $dbException) {
            Log::error('Failed to record failed event', [
                'event_id' => $this->event->getId(),
                'error' => $dbException->getMessage()
            ]);
        }
    }

    /**
     * 计算重试延迟
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(24); // 24小时后停止重试
    }

    /**
     * 获取任务标识符
     */
    public function uniqueId(): string
    {
        return 'event-' . $this->event->getId();
    }

    /**
     * 获取任务中间件
     */
    public function middleware(): array
    {
        return [
            new \App\Jobs\Middleware\RateLimitedEventsMiddleware(),
            new \App\Jobs\Middleware\EventThrottlingMiddleware(),
        ];
    }
}