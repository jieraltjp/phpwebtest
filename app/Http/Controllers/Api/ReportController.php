<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    protected $reportService;
    
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    
    /**
     * 获取销售报表
     */
    public function getSalesReport(Request $request): JsonResponse
    {
        try {
            $params = $this->validateDateParams($request);
            $data = $this->reportService->getSalesReport($params);
            
            return ApiResponseService::success($data, '获取销售报表成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取销售报表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取用户行为报表
     */
    public function getUserBehaviorReport(Request $request): JsonResponse
    {
        try {
            $params = $this->validateDateParams($request);
            $data = $this->reportService->getUserBehaviorReport($params);
            
            return ApiResponseService::success($data, '获取用户行为报表成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取用户行为报表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取产品分析报表
     */
    public function getProductAnalysisReport(Request $request): JsonResponse
    {
        try {
            $params = $this->validateDateParams($request);
            $data = $this->reportService->getProductAnalysisReport($params);
            
            return ApiResponseService::success($data, '获取产品分析报表成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取产品分析报表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取询价分析报表
     */
    public function getInquiryAnalysisReport(Request $request): JsonResponse
    {
        try {
            $params = $this->validateDateParams($request);
            $data = $this->reportService->getInquiryAnalysisReport($params);
            
            return ApiResponseService::success($data, '获取询价分析报表成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取询价分析报表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取财务报表
     */
    public function getFinancialReport(Request $request): JsonResponse
    {
        try {
            $params = $this->validateDateParams($request);
            $data = $this->reportService->getFinancialReport($params);
            
            return ApiResponseService::success($data, '获取财务报表成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取财务报表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取仪表板概览数据
     */
    public function getDashboardOverview(Request $request): JsonResponse
    {
        try {
            $params = $this->validateDateParams($request);
            
            // 并行获取多个报表数据
            $salesReport = $this->reportService->getSalesReport($params);
            $userBehaviorReport = $this->reportService->getUserBehaviorReport($params);
            $productAnalysisReport = $this->reportService->getProductAnalysisReport($params);
            $inquiryAnalysisReport = $this->reportService->getInquiryAnalysisReport($params);
            $financialReport = $this->reportService->getFinancialReport($params);
            
            $overview = [
                'summary' => [
                    'total_revenue' => $salesReport['summary']['total_revenue'],
                    'total_orders' => $salesReport['summary']['total_orders'],
                    'total_users' => count($userBehaviorReport['active_users']) > 0 
                        ? max(array_column($userBehaviorReport['active_users'], 'active_users'))
                        : 0,
                    'total_products' => count($productAnalysisReport['product_performance']),
                    'total_inquiries' => array_sum(array_column($inquiryAnalysisReport['inquiry_trend'], 'total_inquiries')),
                ],
                'key_metrics' => [
                    'avg_order_value' => $salesReport['summary']['avg_order_value'],
                    'conversion_rate' => $this->calculateConversionRate($inquiryAnalysisReport),
                    'customer_retention' => $this->calculateAverageRetention($userBehaviorReport),
                    'revenue_growth' => $this->calculateRevenueGrowth($financialReport),
                ],
                'trends' => [
                    'sales_trend' => array_slice($salesReport['daily_data'], -7), // 最近7天
                    'user_activity' => array_slice($userBehaviorReport['active_users'], -7),
                    'inquiry_trend' => array_slice($inquiryAnalysisReport['inquiry_trend'], -7),
                ],
                'top_performers' => [
                    'top_products' => array_slice($salesReport['top_products'], 0, 5),
                    'top_customers' => array_slice($salesReport['top_customers'], 0, 5),
                    'stock_alerts' => array_slice($productAnalysisReport['stock_alerts'], 0, 5),
                ],
                'alerts' => [
                    'low_stock' => count($productAnalysisReport['stock_alerts']),
                    'pending_inquiries' => $inquiryAnalysisReport['conversion_funnel']['total_inquiries'] 
                        - $inquiryAnalysisReport['conversion_funnel']['quoted_inquiries'],
                    'revenue_forecast' => end($financialReport['revenue_forecast'])['predicted_revenue'] ?? 0,
                ],
            ];
            
            return ApiResponseService::success($overview, '获取仪表板概览成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取仪表板概览失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 导出报表
     */
    public function exportReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'report_type' => 'required|string|in:sales,user_behavior,product_analysis,inquiry_analysis,financial',
                'format' => 'string|in:json,csv,xlsx',
                'start_date' => 'date',
                'end_date' => 'date',
            ]);
            
            $params = $this->validateDateParams($request);
            $reportType = $request->input('report_type');
            $format = $request->input('format', 'json');
            
            $data = $this->reportService->exportReport($reportType, $params);
            
            // 根据格式处理数据
            switch ($format) {
                case 'csv':
                    $exportData = $this->convertToCsv($data);
                    $filename = $reportType . '_report_' . now()->format('Y-m-d') . '.csv';
                    break;
                case 'xlsx':
                    // 这里可以集成 PhpSpreadsheet 来生成 Excel 文件
                    $exportData = $this->convertToCsv($data); // 简化处理
                    $filename = $reportType . '_report_' . now()->format('Y-m-d') . '.csv';
                    break;
                default:
                    $exportData = $data;
                    $filename = null;
            }
            
            $response = ApiResponseService::success($exportData, '报表导出成功');
            
            if ($filename) {
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
                $response->headers->set('Content-Type', 'text/csv');
            }
            
            return $response;
        } catch (\Exception $e) {
            return ApiResponseService::serverError('导出报表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 清除报表缓存
     */
    public function clearCache(): JsonResponse
    {
        try {
            $cleared = $this->reportService->clearReportCache();
            
            return ApiResponseService::success([
                'cleared_keys' => $cleared,
            ], '报表缓存清除成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('清除报表缓存失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取报表配置
     */
    public function getConfig(): JsonResponse
    {
        try {
            $config = [
                'report_types' => [
                    'sales' => [
                        'name' => '销售报表',
                        'description' => '包含销售数据、产品排行、客户排行等信息',
                        'metrics' => ['total_orders', 'total_revenue', 'avg_order_value', 'unique_customers'],
                    ],
                    'user_behavior' => [
                        'name' => '用户行为报表',
                        'description' => '包含用户注册趋势、活跃度、留存率等数据',
                        'metrics' => ['registrations', 'active_users', 'retention_rate'],
                    ],
                    'product_analysis' => [
                        'name' => '产品分析报表',
                        'description' => '包含产品销售表现、库存状态、询价统计等',
                        'metrics' => ['total_sold', 'total_revenue', 'stock_level', 'inquiry_count'],
                    ],
                    'inquiry_analysis' => [
                        'name' => '询价分析报表',
                        'description' => '包含询价趋势、转化率、热门产品等',
                        'metrics' => ['total_inquiries', 'conversion_rate', 'quoted_rate'],
                    ],
                    'financial' => [
                        'name' => '财务报表',
                        'description' => '包含收入趋势、货币分布、收入预测等',
                        'metrics' => ['revenue', 'profit', 'growth_rate'],
                    ],
                ],
                'export_formats' => ['json', 'csv', 'xlsx'],
                'date_ranges' => [
                    'today' => '今天',
                    'yesterday' => '昨天',
                    'last_7_days' => '最近7天',
                    'last_30_days' => '最近30天',
                    'last_90_days' => '最近90天',
                    'this_month' => '本月',
                    'last_month' => '上月',
                    'this_year' => '今年',
                    'custom' => '自定义',
                ],
                'cache_settings' => [
                    'ttl' => 3600, // 1小时
                    'auto_cleanup' => true,
                ],
            ];
            
            return ApiResponseService::success($config, '获取报表配置成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取报表配置失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 验证日期参数
     */
    protected function validateDateParams(Request $request): array
    {
        $params = [];
        
        if ($request->has('start_date')) {
            $params['start_date'] = Carbon::parse($request->input('start_date'));
        }
        
        if ($request->has('end_date')) {
            $params['end_date'] = Carbon::parse($request->input('end_date'));
        }
        
        if ($request->has('date_range')) {
            $range = $request->input('date_range');
            $now = now();
            
            switch ($range) {
                case 'today':
                    $params['start_date'] = $now->copy()->startOfDay();
                    $params['end_date'] = $now->copy()->endOfDay();
                    break;
                case 'yesterday':
                    $params['start_date'] = $now->copy()->subDay()->startOfDay();
                    $params['end_date'] = $now->copy()->subDay()->endOfDay();
                    break;
                case 'last_7_days':
                    $params['start_date'] = $now->copy()->subDays(6)->startOfDay();
                    $params['end_date'] = $now->copy()->endOfDay();
                    break;
                case 'last_30_days':
                    $params['start_date'] = $now->copy()->subDays(29)->startOfDay();
                    $params['end_date'] = $now->copy()->endOfDay();
                    break;
                case 'this_month':
                    $params['start_date'] = $now->copy()->startOfMonth();
                    $params['end_date'] = $now->copy()->endOfMonth();
                    break;
                case 'last_month':
                    $params['start_date'] = $now->copy()->subMonth()->startOfMonth();
                    $params['end_date'] = $now->copy()->subMonth()->endOfMonth();
                    break;
            }
        }
        
        return $params;
    }
    
    /**
     * 计算转化率
     */
    protected function calculateConversionRate(array $inquiryReport): float
    {
        $funnel = $inquiryReport['conversion_funnel'];
        
        if ($funnel['total_inquiries'] > 0) {
            return round(($funnel['accepted_inquiries'] / $funnel['total_inquiries']) * 100, 2);
        }
        
        return 0;
    }
    
    /**
     * 计算平均留存率
     */
    protected function calculateAverageRetention(array $userReport): float
    {
        if (empty($userReport['retention_data'])) {
            return 0;
        }
        
        $totalRetention = array_sum(array_column($userReport['retention_data'], 'retention_rate'));
        $count = count($userReport['retention_data']);
        
        return $count > 0 ? round($totalRetention / $count, 2) : 0;
    }
    
    /**
     * 计算收入增长率
     */
    protected function calculateRevenueGrowth(array $financialReport): float
    {
        if (empty($financialReport['revenue_trend']) || count($financialReport['revenue_trend']) < 2) {
            return 0;
        }
        
        $trend = $financialReport['revenue_trend'];
        $firstPeriod = array_slice($trend, 0, ceil(count($trend) / 2));
        $secondPeriod = array_slice($trend, ceil(count($trend) / 2));
        
        $firstRevenue = array_sum(array_column($firstPeriod, 'daily_revenue'));
        $secondRevenue = array_sum(array_column($secondPeriod, 'daily_revenue'));
        
        if ($firstRevenue > 0) {
            return round((($secondRevenue - $firstRevenue) / $firstRevenue) * 100, 2);
        }
        
        return 0;
    }
    
    /**
     * 转换为CSV格式
     */
    protected function convertToCsv(array $data): string
    {
        $csv = '';
        $headers = [];
        
        // 简化处理，实际应该根据不同报表类型生成不同的CSV结构
        if (isset($data['data']['daily_data'])) {
            $headers = ['date', 'total_orders', 'total_revenue', 'avg_order_value', 'unique_customers'];
            $csv .= implode(',', $headers) . "\n";
            
            foreach ($data['data']['daily_data'] as $row) {
                $csv .= implode(',', [
                    $row['date'],
                    $row['total_orders'],
                    $row['total_revenue'],
                    $row['avg_order_value'],
                    $row['unique_customers']
                ]) . "\n";
            }
        }
        
        return $csv;
    }
}
