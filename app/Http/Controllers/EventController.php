<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use App\Services\EventMonitorService;
use App\Events\EventDispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    protected EventMonitorService $monitor;
    protected EventDispatcher $dispatcher;

    public function __construct(EventMonitorService $monitor, EventDispatcher $dispatcher)
    {
        $this->monitor = $monitor;
        $this->dispatcher = $dispatcher;
    }

    /**
     * 获取事件统计信息
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = EventService::getStatistics();
            
            return response()->json([
                'status' => 'success',
                'data' => $stats,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get event statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve event statistics'
            ], 500);
        }
    }

    /**
     * 获取事件历史
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 50), 100);
            $offset = $request->get('offset', 0);
            
            $history = EventService::getEventHistory();
            $total = $history->count();
            $events = $history->slice($offset, $limit)->values();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'events' => $events->toArray(),
                    'pagination' => [
                        'total' => $total,
                        'limit' => $limit,
                        'offset' => $offset,
                        'has_more' => ($offset + $limit) < $total
                    ]
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get event history', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve event history'
            ], 500);
        }
    }

    /**
     * 获取监控报告
     */
    public function monitoring(): JsonResponse
    {
        try {
            $report = $this->monitor->getMonitoringReport();
            
            return response()->json([
                'status' => 'success',
                'data' => $report,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get monitoring report', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve monitoring report'
            ], 500);
        }
    }

    /**
     * 获取性能分析
     */
    public function performance(): JsonResponse
    {
        try {
            $analysis = $this->monitor->getPerformanceAnalysis();
            
            return response()->json([
                'status' => 'success',
                'data' => $analysis,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get performance analysis', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve performance analysis'
            ], 500);
        }
    }

    /**
     * 获取实时指标
     */
    public function realtime(): JsonResponse
    {
        try {
            $metrics = $this->monitor->getRealTimeMetrics();
            
            return response()->json([
                'status' => 'success',
                'data' => $metrics,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get realtime metrics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve realtime metrics'
            ], 500);
        }
    }

    /**
     * 获取调试信息
     */
    public function debug(): JsonResponse
    {
        try {
            $debug = EventService::debug();
            
            return response()->json([
                'status' => 'success',
                'data' => $debug,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get debug info', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve debug information'
            ], 500);
        }
    }

    /**
     * 启用/禁用事件系统
     */
    public function toggle(Request $request): JsonResponse
    {
        try {
            $enabled = $request->get('enabled', true);
            
            if ($enabled) {
                EventService::enable();
            } else {
                EventService::disable();
            }

            return response()->json([
                'status' => 'success',
                'message' => $enabled ? 'Event system enabled' : 'Event system disabled',
                'data' => [
                    'enabled' => EventService::isEnabled()
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle event system', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle event system'
            ], 500);
        }
    }

    /**
     * 启动/停止监控
     */
    public function monitoringToggle(Request $request): JsonResponse
    {
        try {
            $action = $request->get('action');
            
            if ($action === 'start') {
                $this->monitor->startMonitoring();
                $message = 'Event monitoring started';
            } elseif ($action === 'stop') {
                $this->monitor->stopMonitoring();
                $message = 'Event monitoring stopped';
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid action. Use "start" or "stop"'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle monitoring', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle monitoring'
            ], 500);
        }
    }

    /**
     * 清除事件历史
     */
    public function clearHistory(): JsonResponse
    {
        try {
            EventService::clearEventHistory();

            return response()->json([
                'status' => 'success',
                'message' => 'Event history cleared',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear event history', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to clear event history'
            ], 500);
        }
    }

    /**
     * 清除监控数据
     */
    public function clearMonitoring(): JsonResponse
    {
        try {
            $this->monitor->clearMonitoringData();

            return response()->json([
                'status' => 'success',
                'message' => 'Monitoring data cleared',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear monitoring data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to clear monitoring data'
            ], 500);
        }
    }

    /**
     * 导出监控数据
     */
    public function export(): JsonResponse
    {
        try {
            $data = $this->monitor->exportMonitoringData();

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export monitoring data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export monitoring data'
            ], 500);
        }
    }

    /**
     * 重置事件系统
     */
    public function reset(): JsonResponse
    {
        try {
            EventService::reset();
            $this->monitor->clearMonitoringData();

            return response()->json([
                'status' => 'success',
                'message' => 'Event system reset successfully',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reset event system', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reset event system'
            ], 500);
        }
    }
}