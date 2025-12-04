<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiThrottleService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiThrottleController extends Controller
{
    protected $throttleService;
    
    public function __construct(ApiThrottleService $throttleService)
    {
        $this->throttleService = $throttleService;
    }
    
    /**
     * 获取实时限流统计
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->throttleService->getRealTimeStats();
            
            return ApiResponseService::success($stats, '获取限流统计成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取限流统计失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取限流配置
     */
    public function getConfig(): JsonResponse
    {
        try {
            $config = [
                'limits' => [
                    'default' => [
                        'requests' => 60,
                        'minutes' => 1,
                        'description' => '默认用户每分钟60次请求',
                    ],
                    'auth' => [
                        'requests' => 120,
                        'minutes' => 1,
                        'description' => '认证用户每分钟120次请求',
                    ],
                    'admin' => [
                        'requests' => 300,
                        'minutes' => 1,
                        'description' => '管理员每分钟300次请求',
                    ],
                    'search' => [
                        'requests' => 30,
                        'minutes' => 1,
                        'description' => '搜索接口每分钟30次请求',
                    ],
                    'inquiry' => [
                        'requests' => 10,
                        'minutes' => 1,
                        'description' => '询价接口每分钟10次请求',
                    ],
                    'order' => [
                        'requests' => 20,
                        'minutes' => 1,
                        'description' => '订单接口每分钟20次请求',
                    ],
                    'bulk_purchase' => [
                        'requests' => 5,
                        'minutes' => 5,
                        'description' => '批量采购接口每5分钟5次请求',
                    ],
                    'auth_login' => [
                        'requests' => 5,
                        'minutes' => 15,
                        'description' => '登录接口每15分钟5次请求',
                    ],
                    'auth_register' => [
                        'requests' => 3,
                        'minutes' => 60,
                        'description' => '注册接口每小时3次请求',
                    ],
                ],
                'features' => [
                    'blacklist_support' => true,
                    'anomaly_detection' => true,
                    'dynamic_adjustment' => true,
                    'real_time_monitoring' => true,
                    'violation_logging' => true,
                ],
                'security' => [
                    'max_requests_per_minute' => 500,
                    'max_unique_endpoints_per_minute' => 30,
                    'blacklist_duration' => '24小时',
                    'strict_throttle_duration' => '15分钟',
                ],
            ];
            
            return ApiResponseService::success($config, '获取限流配置成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取限流配置失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 添加IP到黑名单
     */
    public function addToBlacklist(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ip' => 'required|ip',
                'duration' => 'integer|min:60|max:86400',
                'reason' => 'string|max:255',
            ]);
            
            $ip = $request->input('ip');
            $duration = $request->input('duration', 86400);
            $reason = $request->input('reason', 'Manual');
            
            $success = $this->throttleService->addToBlacklist($ip, $duration, $reason);
            
            if ($success) {
                return ApiResponseService::success([
                    'ip' => $ip,
                    'duration' => $duration,
                    'reason' => $reason,
                ], 'IP已添加到黑名单');
            } else {
                return ApiResponseService::error('添加IP到黑名单失败');
            }
        } catch (\Exception $e) {
            return ApiResponseService::serverError('添加IP到黑名单失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 从黑名单移除IP
     */
    public function removeFromBlacklist(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ip' => 'required|ip',
            ]);
            
            $ip = $request->input('ip');
            $success = $this->throttleService->removeFromBlacklist($ip);
            
            if ($success) {
                return ApiResponseService::success([
                    'ip' => $ip,
                ], 'IP已从黑名单移除');
            } else {
                return ApiResponseService::error('从黑名单移除IP失败');
            }
        } catch (\Exception $e) {
            return ApiResponseService::serverError('从黑名单移除IP失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 清理过期数据
     */
    public function cleanup(): JsonResponse
    {
        try {
            $cleaned = $this->throttleService->cleanupExpiredData();
            
            return ApiResponseService::success([
                'cleaned_keys' => $cleaned,
            ], '清理过期数据完成');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('清理过期数据失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取黑名单列表
     */
    public function getBlacklist(): JsonResponse
    {
        try {
            $stats = $this->throttleService->getRealTimeStats();
            $blacklistedIPs = $stats['blacklisted_ips'];
            
            return ApiResponseService::success($blacklistedIPs, '获取黑名单列表成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取黑名单列表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取异常检测报告
     */
    public function getAnomalies(): JsonResponse
    {
        try {
            $stats = $this->throttleService->getRealTimeStats();
            $anomalies = $stats['anomalies'];
            
            // 添加异常处理建议
            $enhancedAnomalies = array_map(function ($anomaly) {
                $anomaly['recommendation'] = $this->getAnomalyRecommendation($anomaly);
                return $anomaly;
            }, $anomalies);
            
            return ApiResponseService::success($enhancedAnomalies, '获取异常检测报告成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取异常检测报告失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取异常处理建议
     */
    protected function getAnomalyRecommendation(array $anomaly): string
    {
        switch ($anomaly['type']) {
            case 'high_frequency_ip':
                if ($anomaly['severity'] === 'critical') {
                    return '建议立即添加到黑名单，可能为恶意攻击';
                } else {
                    return '建议监控该IP，考虑临时限制';
                }
                
            case 'endpoint_scanner':
                if ($anomaly['severity'] === 'critical') {
                    return '建议立即添加到黑名单，可能为漏洞扫描';
                } else {
                    return '建议监控该IP，可能为爬虫行为';
                }
                
            default:
                return '建议进一步调查该异常行为';
        }
    }
}