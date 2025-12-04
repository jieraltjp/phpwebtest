<?php

namespace App\Providers;

use App\Services\WebSocketService;
use App\Services\RealtimeEventService;
use App\Services\MessagePersistenceService;
use Illuminate\Support\ServiceProvider;

/**
 * WebSocket服务提供者
 * 
 * 注册WebSocket相关服务和配置
 */
class WebSocketServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册WebSocket服务
        $this->app->singleton(WebSocketService::class, function ($app) {
            return new WebSocketService(
                $app->make(CacheService::class),
                $app->make(EventService::class)
            );
        });

        // 注册实时事件服务
        $this->app->singleton(RealtimeEventService::class, function ($app) {
            return new RealtimeEventService(
                $app->make(WebSocketService::class),
                $app->make(CacheService::class)
            );
        });

        // 注册消息持久化服务
        $this->app->singleton(MessagePersistenceService::class, function ($app) {
            return new MessagePersistenceService(
                $app->make(CacheService::class)
            );
        });

        // 合并配置
        $this->mergeConfigFrom(
            __DIR__.'/../../config/websocket.php', 'websocket'
        );
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 发布配置文件
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/websocket.php' => config_path('websocket.php'),
            ], 'websocket-config');
        }

        // 注册命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\WebSocketServerCommand::class,
            ]);
        }

        // 注册事件监听器
        $this->registerEventListeners();
    }

    /**
     * 注册事件监听器
     */
    private function registerEventListeners(): void
    {
        // 订单事件监听
        \App\Models\Order::created(function ($order) {
            app(RealtimeEventService::class)->notifyNewOrder($order);
        });

        \App\Models\Order::updated(function ($order) {
            if ($order->wasChanged('status')) {
                $oldStatus = $order->getOriginal('status');
                app(RealtimeEventService::class)->notifyOrderStatusChange(
                    $order, 
                    $oldStatus, 
                    $order->status
                );
            }
        });

        // 产品库存变化监听
        \App\Models\Product::updated(function ($product) {
            if ($product->wasChanged('stock')) {
                $oldStock = $product->getOriginal('stock');
                app(RealtimeEventService::class)->notifyInventoryChange(
                    $product,
                    $oldStock,
                    $product->stock
                );
            }
        });

        // 询价状态变化监听
        \App\Models\Inquiry::updated(function ($inquiry) {
            if ($inquiry->wasChanged('status')) {
                $oldStatus = $inquiry->getOriginal('status');
                app(RealtimeEventService::class)->notifyInquiryStatusChange(
                    $inquiry,
                    $oldStatus,
                    $inquiry->status
                );
            }
        });
    }
}