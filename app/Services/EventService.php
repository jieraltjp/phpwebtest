<?php

namespace App\Services;

use App\Events\EventDispatcher;
use App\Events\Contracts\EventInterface;
use App\Events\Contracts\ListenerInterface;
use Illuminate\Support\Facades\Log;

class EventService
{
    protected static ?EventDispatcher $dispatcher = null;
    protected static bool $enabled = true;

    /**
     * 获取事件调度器实例
     */
    public static function dispatcher(): EventDispatcher
    {
        if (self::$dispatcher === null) {
            self::$dispatcher = new EventDispatcher();
            self::registerDefaultListeners();
        }

        return self::$dispatcher;
    }

    /**
     * 注册默认监听器
     */
    protected static function registerDefaultListeners(): void
    {
        $dispatcher = self::$dispatcher;

        // 注册邮件通知监听器
        $emailListener = new \App\Events\Listeners\EmailNotificationListener();
        $dispatcher->listenTo([
            \App\Events\User\UserRegisteredEvent::class,
            \App\Events\Order\OrderCreatedEvent::class,
            \App\Events\Inquiry\InquiryCreatedEvent::class,
            \App\Events\Order\OrderStatusChangedEvent::class,
        ], $emailListener);

        // 注册缓存更新监听器
        $cacheListener = new \App\Events\Listeners\CacheUpdateListener();
        $dispatcher->listenTo([
            \App\Events\Product\ProductCreatedEvent::class,
            \App\Events\Product\ProductUpdatedEvent::class,
            \App\Events\Order\OrderCreatedEvent::class,
            \App\Events\User\UserUpdatedEvent::class,
        ], $cacheListener);

        // 注册日志记录监听器
        $logListener = new \App\Events\Listeners\LoggingListener();
        $dispatcher->listenTo([], $logListener); // 全局监听器

        // 注册统计数据监听器
        $statsListener = new \App\Events\Listeners\StatisticsListener();
        $dispatcher->listenTo([
            \App\Events\User\UserRegisteredEvent::class,
            \App\Events\Order\OrderCreatedEvent::class,
            \App\Events\Inquiry\InquiryCreatedEvent::class,
            \App\Events\Product\ProductViewedEvent::class,
        ], $statsListener);

        // 注册库存管理监听器
        $inventoryListener = new \App\Events\Listeners\InventoryListener();
        $dispatcher->listenTo([
            \App\Events\Order\OrderCreatedEvent::class,
            \App\Events\Order\OrderCancelledEvent::class,
            \App\Events\Product\ProductUpdatedEvent::class,
        ], $inventoryListener);

        Log::info('Default event listeners registered');
    }

    /**
     * 分发事件
     */
    public static function dispatch(EventInterface $event): void
    {
        if (!self::$enabled) {
            return;
        }

        try {
            self::dispatcher()->dispatch($event);
        } catch (\Throwable $exception) {
            Log::error('Event dispatch failed', [
                'event_id' => $event->getId(),
                'event_name' => $event->getName(),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }
    }

    /**
     * 注册监听器
     */
    public static function listen(string $eventName, ListenerInterface $listener): void
    {
        self::dispatcher()->listen($eventName, $listener);
    }

    /**
     * 注册监听器到多个事件
     */
    public static function listenTo(array $eventNames, ListenerInterface $listener): void
    {
        self::dispatcher()->listenTo($eventNames, $listener);
    }

    /**
     * 启用事件系统
     */
    public static function enable(): void
    {
        self::$enabled = true;
        Log::info('Event system enabled');
    }

    /**
     * 禁用事件系统
     */
    public static function disable(): void
    {
        self::$enabled = false;
        Log::info('Event system disabled');
    }

    /**
     * 检查事件系统是否启用
     */
    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    /**
     * 重置事件调度器
     */
    public static function reset(): void
    {
        self::$dispatcher = null;
        Log::info('Event dispatcher reset');
    }

    /**
     * 获取事件历史
     */
    public static function getEventHistory(): \Illuminate\Support\Collection
    {
        return self::dispatcher()->getEventHistory();
    }

    /**
     * 清除事件历史
     */
    public static function clearEventHistory(): void
    {
        self::dispatcher()->clearEventHistory();
    }

    /**
     * 获取事件统计信息
     */
    public static function getStatistics(): array
    {
        $history = self::getEventHistory();
        $listeners = self::dispatcher()->getListeners();

        return [
            'total_events' => $history->count(),
            'enabled' => self::$enabled,
            'registered_listeners' => $listeners->count(),
            'event_types' => $history->pluck('name')->countBy()->toArray(),
            'async_events' => $history->where('async', true)->count(),
            'sync_events' => $history->where('async', false)->count(),
            'recent_events' => $history->take(10)->values()->toArray()
        ];
    }

    /**
     * 调试事件系统
     */
    public static function debug(): array
    {
        return [
            'enabled' => self::$enabled,
            'dispatcher' => self::$dispatcher !== null,
            'statistics' => self::getStatistics(),
            'listeners' => self::dispatcher()->getListeners()->map(function ($listeners, $event) {
                return [
                    'event' => $event,
                    'listeners' => $listeners->map(fn($l) => [
                        'name' => $l->getName(),
                        'priority' => $l->getPriority(),
                        'supported_events' => $l->getSupportedEvents()
                    ])->toArray()
                ];
            })->values()->toArray()
        ];
    }
}