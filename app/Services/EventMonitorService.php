<?php

namespace App\Services;

use App\Events\Contracts\EventInterface;
use App\Events\EventDispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventMonitorService
{
    protected EventDispatcher $dispatcher;
    protected array $metrics = [];
    protected bool $monitoring = false;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * 开始监控
     */
    public function startMonitoring(): void
    {
        $this->monitoring = true;
        $this->metrics = [];
        
        Log::info('Event monitoring started');
    }

    /**
     * 停止监控
     */
    public function stopMonitoring(): void
    {
        $this->monitoring = false;
        
        Log::info('Event monitoring stopped', [
            'total_events' => count($this->metrics)
        ]);
    }

    /**
     * 记录事件指标
     */
    public function recordEvent(EventInterface $event, float $processingTime, array $listenerMetrics = []): void
    {
        if (!$this->monitoring) {
            return;
        }

        $this->metrics[] = [
            'event_id' => $event->getId(),
            'event_name' => $event->getName(),
            'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
            'processing_time_ms' => round($processingTime * 1000, 2),
            'async' => $event->shouldProcessAsync(),
            'priority' => $event->getPriority(),
            'listener_count' => count($listenerMetrics),
            'listener_metrics' => $listenerMetrics,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];

        // 实时更新缓存中的指标
        $this->updateRealTimeMetrics($event, $processingTime);
    }

    /**
     * 更新实时指标
     */
    protected function updateRealTimeMetrics(EventInterface $event, float $processingTime): void
    {
        $eventName = $event->getName();
        $now = now()->format('Y-m-d H:i');

        // 更新事件计数
        Cache::increment("metrics:events:{$eventName}:count");
        Cache::increment("metrics:events:{$eventName}:count:{$now}");

        // 更新处理时间
        $this->updateAverageProcessingTime($eventName, $processingTime);

        // 更新内存使用
        $this->updateMemoryUsage($eventName);

        // 更新错误率（如果有错误）
        // $this->updateErrorRate($eventName);
    }

    /**
     * 更新平均处理时间
     */
    protected function updateAverageProcessingTime(string $eventName, float $processingTime): void
    {
        $key = "metrics:events:{$eventName}:avg_time";
        $countKey = "metrics:events:{$eventName}:count";
        
        $currentAvg = Cache::get($key, 0);
        $count = Cache::get($countKey, 1);
        
        $newAvg = (($currentAvg * ($count - 1)) + $processingTime) / $count;
        Cache::put($key, $newAvg, 3600);
    }

    /**
     * 更新内存使用
     */
    protected function updateMemoryUsage(string $eventName): void
    {
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);

        Cache::put("metrics:events:{$eventName}:memory", $memoryUsage, 3600);
        Cache::put("metrics:events:{$eventName}:peak_memory", $peakMemory, 3600);
    }

    /**
     * 获取监控报告
     */
    public function getMonitoringReport(): array
    {
        if (empty($this->metrics)) {
            return [
                'status' => 'no_data',
                'message' => 'No monitoring data available'
            ];
        }

        $totalEvents = count($this->metrics);
        $totalProcessingTime = array_sum(array_column($this->metrics, 'processing_time_ms'));
        $avgProcessingTime = $totalProcessingTime / $totalEvents;
        
        $eventTypes = array_count_values(array_column($this->metrics, 'event_name'));
        $asyncEvents = array_filter($this->metrics, fn($m) => $m['async']);
        $syncEvents = array_filter($this->metrics, fn($m) => !$m['async']);

        return [
            'status' => 'active',
            'summary' => [
                'total_events' => $totalEvents,
                'total_processing_time_ms' => round($totalProcessingTime, 2),
                'average_processing_time_ms' => round($avgProcessingTime, 2),
                'async_events' => count($asyncEvents),
                'sync_events' => count($syncEvents),
                'max_memory_usage_mb' => max(array_column($this->metrics, 'peak_memory_mb')),
                'avg_memory_usage_mb' => round(array_sum(array_column($this->metrics, 'memory_usage_mb')) / $totalEvents, 2),
            ],
            'event_types' => $eventTypes,
            'slow_events' => $this->getSlowEvents(),
            'high_memory_events' => $this->getHighMemoryEvents(),
            'recent_events' => array_slice($this->metrics, -10),
        ];
    }

    /**
     * 获取慢事件
     */
    protected function getSlowEvents(): array
    {
        $threshold = 1000; // 1秒
        
        return array_filter($this->metrics, function ($metric) use ($threshold) {
            return $metric['processing_time_ms'] > $threshold;
        });
    }

    /**
     * 获取高内存使用事件
     */
    protected function getHighMemoryEvents(): array
    {
        $threshold = 50; // 50MB
        
        return array_filter($this->metrics, function ($metric) use ($threshold) {
            return $metric['peak_memory_mb'] > $threshold;
        });
    }

    /**
     * 获取实时指标
     */
    public function getRealTimeMetrics(): array
    {
        $keys = Cache::getRedis()->keys('metrics:events:*');
        $metrics = [];

        foreach ($keys as $key) {
            $value = Cache::get($key);
            $metrics[$key] = $value;
        }

        return $metrics;
    }

    /**
     * 获取性能分析
     */
    public function getPerformanceAnalysis(): array
    {
        return [
            'event_performance' => $this->getEventPerformance(),
            'listener_performance' => $this->getListenerPerformance(),
            'memory_analysis' => $this->getMemoryAnalysis(),
            'bottlenecks' => $this->identifyBottlenecks(),
        ];
    }

    /**
     * 获取事件性能
     */
    protected function getEventPerformance(): array
    {
        $eventStats = [];
        
        foreach ($this->metrics as $metric) {
            $eventName = $metric['event_name'];
            
            if (!isset($eventStats[$eventName])) {
                $eventStats[$eventName] = [
                    'count' => 0,
                    'total_time' => 0,
                    'min_time' => PHP_FLOAT_MAX,
                    'max_time' => 0,
                    'total_memory' => 0,
                    'max_memory' => 0,
                ];
            }
            
            $stats = &$eventStats[$eventName];
            $stats['count']++;
            $stats['total_time'] += $metric['processing_time_ms'];
            $stats['min_time'] = min($stats['min_time'], $metric['processing_time_ms']);
            $stats['max_time'] = max($stats['max_time'], $metric['processing_time_ms']);
            $stats['total_memory'] += $metric['memory_usage_mb'];
            $stats['max_memory'] = max($stats['max_memory'], $metric['peak_memory_mb']);
        }

        // 计算平均值
        foreach ($eventStats as &$stats) {
            $stats['avg_time'] = $stats['total_time'] / $stats['count'];
            $stats['avg_memory'] = $stats['total_memory'] / $stats['count'];
        }

        return $eventStats;
    }

    /**
     * 获取监听器性能
     */
    protected function getListenerPerformance(): array
    {
        $listenerStats = [];
        
        foreach ($this->metrics as $metric) {
            foreach ($metric['listener_metrics'] as $listenerMetric) {
                $listenerName = $listenerMetric['name'];
                
                if (!isset($listenerStats[$listenerName])) {
                    $listenerStats[$listenerName] = [
                        'count' => 0,
                        'total_time' => 0,
                        'min_time' => PHP_FLOAT_MAX,
                        'max_time' => 0,
                        'failures' => 0,
                    ];
                }
                
                $stats = &$listenerStats[$listenerName];
                $stats['count']++;
                $stats['total_time'] += $listenerMetric['processing_time_ms'];
                $stats['min_time'] = min($stats['min_time'], $listenerMetric['processing_time_ms']);
                $stats['max_time'] = max($stats['max_time'], $listenerMetric['processing_time_ms']);
                
                if ($listenerMetric['failed']) {
                    $stats['failures']++;
                }
            }
        }

        // 计算平均值和成功率
        foreach ($listenerStats as &$stats) {
            $stats['avg_time'] = $stats['total_time'] / $stats['count'];
            $stats['success_rate'] = (($stats['count'] - $stats['failures']) / $stats['count']) * 100;
        }

        return $listenerStats;
    }

    /**
     * 获取内存分析
     */
    protected function getMemoryAnalysis(): array
    {
        $memoryData = array_column($this->metrics, 'memory_usage_mb');
        $peakMemoryData = array_column($this->metrics, 'peak_memory_mb');

        return [
            'avg_memory_usage_mb' => round(array_sum($memoryData) / count($memoryData), 2),
            'max_memory_usage_mb' => max($peakMemoryData),
            'min_memory_usage_mb' => min($memoryData),
            'memory_growth_events' => $this->getMemoryGrowthEvents(),
        ];
    }

    /**
     * 获取内存增长事件
     */
    protected function getMemoryGrowthEvents(): array
    {
        $growthEvents = [];
        $previousMemory = null;

        foreach ($this->metrics as $metric) {
            if ($previousMemory !== null) {
                $growth = $metric['memory_usage_mb'] - $previousMemory;
                if ($growth > 10) { // 增长超过10MB
                    $growthEvents[] = [
                        'event_id' => $metric['event_id'],
                        'event_name' => $metric['event_name'],
                        'growth_mb' => round($growth, 2),
                        'memory_mb' => $metric['memory_usage_mb'],
                    ];
                }
            }
            $previousMemory = $metric['memory_usage_mb'];
        }

        return $growthEvents;
    }

    /**
     * 识别瓶颈
     */
    protected function identifyBottlenecks(): array
    {
        $bottlenecks = [];

        // 慢事件瓶颈
        $slowEvents = $this->getSlowEvents();
        if (!empty($slowEvents)) {
            $bottlenecks[] = [
                'type' => 'slow_events',
                'description' => 'Events with processing time > 1 second',
                'count' => count($slowEvents),
                'details' => array_map(fn($e) => [
                    'event_name' => $e['event_name'],
                    'processing_time_ms' => $e['processing_time_ms']
                ], $slowEvents)
            ];
        }

        // 高内存使用瓶颈
        $highMemoryEvents = $this->getHighMemoryEvents();
        if (!empty($highMemoryEvents)) {
            $bottlenecks[] = [
                'type' => 'high_memory',
                'description' => 'Events with memory usage > 50MB',
                'count' => count($highMemoryEvents),
                'details' => array_map(fn($e) => [
                    'event_name' => $e['event_name'],
                    'peak_memory_mb' => $e['peak_memory_mb']
                ], $highMemoryEvents)
            ];
        }

        return $bottlenecks;
    }

    /**
     * 清除监控数据
     */
    public function clearMonitoringData(): void
    {
        $this->metrics = [];
        
        // 清除缓存中的指标
        $keys = Cache::getRedis()->keys('metrics:events:*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Log::info('Event monitoring data cleared');
    }

    /**
     * 导出监控数据
     */
    public function exportMonitoringData(): array
    {
        return [
            'export_time' => now()->toISOString(),
            'monitoring_active' => $this->monitoring,
            'metrics' => $this->metrics,
            'report' => $this->getMonitoringReport(),
            'performance_analysis' => $this->getPerformanceAnalysis(),
        ];
    }
}