<?php

namespace App\Providers;

use App\Services\EventService;
use App\Services\EventMonitorService;
use App\Events\EventDispatcher;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 注册事件调度器
        $this->app->singleton(EventDispatcher::class, function ($app) {
            return new EventDispatcher();
        });

        // 注册事件监控服务
        $this->app->singleton(EventMonitorService::class, function ($app) {
            return new EventMonitorService($app->make(EventDispatcher::class));
        });

        // 确保事件服务初始化
        $this->app->singleton('events.service', function ($app) {
            return EventService::class;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 启动事件监控（在开发环境）
        if (app()->environment('local', 'testing')) {
            $monitor = app(EventMonitorService::class);
            $monitor->startMonitoring();
        }
    }
}
