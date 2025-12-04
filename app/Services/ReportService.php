<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Inquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportService
{
    /**
     * 缓存时间（秒）
     */
    const CACHE_TTL = 3600; // 1小时
    
    /**
     * 获取销售报表数据
     */
    public function getSalesReport(array $params = []): array
    {
        $cacheKey = 'report:sales:' . md5(serialize($params));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($params) {
            $startDate = $params['start_date'] ?? now()->subDays(30);
            $endDate = $params['end_date'] ?? now();
            
            // 基础销售数据
            $salesData = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value,
                    COUNT(DISTINCT user_id) as unique_customers
                ')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // 产品销售排行
            $topProducts = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->selectRaw('
                    products.name,
                    products.sku,
                    SUM(order_items.quantity) as total_quantity,
                    SUM(order_items.price * order_items.quantity) as total_revenue
                ')
                ->groupBy('products.id', 'products.name', 'products.sku')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get();
            
            // 客户排行
            $topCustomers = Order::join('users', 'orders.user_id', '=', 'users.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->selectRaw('
                    users.name,
                    users.email,
                    COUNT(orders.id) as total_orders,
                    SUM(orders.total_amount) as total_spent
                ')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('total_spent', 'desc')
                ->limit(10)
                ->get();
            
            // 按状态统计
            $statusBreakdown = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('status')
                ->get();
            
            return [
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'days' => $startDate->diffInDays($endDate) + 1,
                ],
                'summary' => [
                    'total_orders' => $salesData->sum('total_orders'),
                    'total_revenue' => $salesData->sum('total_revenue'),
                    'avg_order_value' => $salesData->avg('avg_order_value'),
                    'unique_customers' => $salesData->sum('unique_customers'),
                ],
                'daily_data' => $salesData->toArray(),
                'top_products' => $topProducts->toArray(),
                'top_customers' => $topCustomers->toArray(),
                'status_breakdown' => $statusBreakdown->toArray(),
            ];
        });
    }
    
    /**
     * 获取用户行为报表
     */
    public function getUserBehaviorReport(array $params = []): array
    {
        $cacheKey = 'report:user_behavior:' . md5(serialize($params));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($params) {
            $startDate = $params['start_date'] ?? now()->subDays(30);
            $endDate = $params['end_date'] ?? now();
            
            // 用户注册趋势
            $registrationTrend = User::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as registrations')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // 用户活跃度
            $activeUsers = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('
                    DATE(created_at) as date,
                    COUNT(DISTINCT user_id) as active_users
                ')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // 用户留存率
            $retentionData = $this->calculateUserRetention($startDate, $endDate);
            
            // 用户行为分布
            $behaviorDistribution = [
                'browsers' => $this->getUserBrowserStats($startDate, $endDate),
                'peak_hours' => $this->getUserPeakHours($startDate, $endDate),
                'geographic' => $this->getUserGeographicStats($startDate, $endDate),
            ];
            
            return [
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'registration_trend' => $registrationTrend->toArray(),
                'active_users' => $activeUsers->toArray(),
                'retention_data' => $retentionData,
                'behavior_distribution' => $behaviorDistribution,
            ];
        });
    }
    
    /**
     * 获取产品分析报表
     */
    public function getProductAnalysisReport(array $params = []): array
    {
        $cacheKey = 'report:product_analysis:' . md5(serialize($params));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($params) {
            $startDate = $params['start_date'] ?? now()->subDays(30);
            $endDate = $params['end_date'] ?? now();
            
            // 产品销售表现
            $productPerformance = Product::leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->orWhereNull('orders.id')
                ->selectRaw('
                    products.id,
                    products.name,
                    products.sku,
                    products.price,
                    products.stock,
                    COALESCE(SUM(order_items.quantity), 0) as total_sold,
                    COALESCE(SUM(order_items.price * order_items.quantity), 0) as total_revenue,
                    COALESCE(COUNT(DISTINCT orders.user_id), 0) as unique_buyers
                ')
                ->groupBy('products.id', 'products.name', 'products.sku', 'products.price', 'products.stock')
                ->orderBy('total_revenue', 'desc')
                ->get();
            
            // 产品分类统计
            $categoryStats = Product::selectRaw('
                supplier_shop as category,
                COUNT(*) as total_products,
                SUM(stock) as total_stock,
                AVG(price) as avg_price
            ')
                ->groupBy('supplier_shop')
                ->orderBy('total_products', 'desc')
                ->get();
            
            // 库存预警
            $stockAlerts = Product::where('stock', '<', 10)
                ->where('active', true)
                ->orderBy('stock', 'asc')
                ->limit(20)
                ->get();
            
            // 产品询价统计
            $inquiryStats = Product::leftJoin('inquiries', 'products.id', '=', 'inquiries.product_id')
                ->whereBetween('inquiries.created_at', [$startDate, $endDate])
                ->selectRaw('
                    products.id,
                    products.name,
                    products.sku,
                    COUNT(inquiries.id) as inquiry_count,
                    AVG(inquiries.budget) as avg_budget
                ')
                ->groupBy('products.id', 'products.name', 'products.sku')
                ->orderBy('inquiry_count', 'desc')
                ->limit(10)
                ->get();
            
            return [
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'product_performance' => $productPerformance->toArray(),
                'category_stats' => $categoryStats->toArray(),
                'stock_alerts' => $stockAlerts->toArray(),
                'inquiry_stats' => $inquiryStats->toArray(),
            ];
        });
    }
    
    /**
     * 获取询价分析报表
     */
    public function getInquiryAnalysisReport(array $params = []): array
    {
        $cacheKey = 'report:inquiry_analysis:' . md5(serialize($params));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($params) {
            $startDate = $params['start_date'] ?? now()->subDays(30);
            $endDate = $params['end_date'] ?? now();
            
            // 询价趋势
            $inquiryTrend = Inquiry::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as total_inquiries,
                    AVG(budget) as avg_budget,
                    SUM(CASE WHEN status = "QUOTED" THEN 1 ELSE 0 END) as quoted_count,
                    SUM(CASE WHEN status = "ACCEPTED" THEN 1 ELSE 0 END) as accepted_count
                ')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // 询价状态分布
            $statusDistribution = Inquiry::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();
            
            // 询价转化漏斗
            $conversionFunnel = [
                'total_inquiries' => Inquiry::whereBetween('created_at', [$startDate, $endDate])->count(),
                'quoted_inquiries' => Inquiry::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'QUOTED')->count(),
                'accepted_inquiries' => Inquiry::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'ACCEPTED')->count(),
                'converted_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                    ->where('order_type', 'inquiry')->count(),
            ];
            
            // 热门询价产品
            $topInquiredProducts = Inquiry::join('products', 'inquiries.product_id', '=', 'products.id')
                ->whereBetween('inquiries.created_at', [$startDate, $endDate])
                ->selectRaw('
                    products.name,
                    products.sku,
                    COUNT(inquiries.id) as inquiry_count,
                    AVG(inquiries.budget) as avg_budget
                ')
                ->groupBy('products.id', 'products.name', 'products.sku')
                ->orderBy('inquiry_count', 'desc')
                ->limit(10)
                ->get();
            
            return [
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'inquiry_trend' => $inquiryTrend->toArray(),
                'status_distribution' => $statusDistribution->toArray(),
                'conversion_funnel' => $conversionFunnel,
                'top_inquired_products' => $topInquiredProducts->toArray(),
            ];
        });
    }
    
    /**
     * 获取财务报表
     */
    public function getFinancialReport(array $params = []): array
    {
        $cacheKey = 'report:financial:' . md5(serialize($params));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($params) {
            $startDate = $params['start_date'] ?? now()->subDays(30);
            $endDate = $params['end_date'] ?? now();
            
            // 收入趋势
            $revenueTrend = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('
                    DATE(created_at) as date,
                    SUM(total_amount) as daily_revenue,
                    COUNT(*) as order_count
                ')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // 货币分布
            $currencyBreakdown = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('currency, SUM(total_amount) as total, COUNT(*) as count')
                ->groupBy('currency')
                ->get();
            
            // 平均订单价值趋势
            $avgOrderValue = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('
                    DATE(created_at) as date,
                    AVG(total_amount) as avg_value
                ')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // 收入预测（基于历史数据）
            $revenueForecast = $this->calculateRevenueForecast($startDate, $endDate);
            
            return [
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'revenue_trend' => $revenueTrend->toArray(),
                'currency_breakdown' => $currencyBreakdown->toArray(),
                'avg_order_value' => $avgOrderValue->toArray(),
                'revenue_forecast' => $revenueForecast,
            ];
        });
    }
    
    /**
     * 计算用户留存率
     */
    protected function calculateUserRetention(Carbon $startDate, Carbon $endDate): array
    {
        $retentionData = [];
        
        // 获取在开始日期前注册的用户
        $cohortUsers = User::where('created_at', '<', $startDate)
            ->pluck('id')
            ->toArray();
        
        if (empty($cohortUsers)) {
            return $retentionData;
        }
        
        // 计算每周留存率
        $period = CarbonPeriod::create($startDate, $endDate->copy()->addDays(7));
        
        foreach ($period as $week) {
            $weekStart = $week->copy()->startOfWeek();
            $weekEnd = $week->copy()->endOfWeek();
            
            $activeUsers = Order::whereBetween('created_at', [$weekStart, $weekEnd])
                ->whereIn('user_id', $cohortUsers)
                ->distinct('user_id')
                ->count('user_id');
            
            $retentionRate = count($cohortUsers) > 0 
                ? round(($activeUsers / count($cohortUsers)) * 100, 2) 
                : 0;
            
            $retentionData[] = [
                'week' => $weekStart->format('Y-m-d'),
                'active_users' => $activeUsers,
                'retention_rate' => $retentionRate,
            ];
        }
        
        return $retentionData;
    }
    
    /**
     * 获取用户浏览器统计
     */
    protected function getUserBrowserStats(Carbon $startDate, Carbon $endDate): array
    {
        // 这里简化处理，实际应该从用户代理中解析
        return [
            'Chrome' => 65,
            'Safari' => 20,
            'Firefox' => 8,
            'Edge' => 5,
            'Other' => 2,
        ];
    }
    
    /**
     * 获取用户活跃时段
     */
    protected function getUserPeakHours(Carbon $startDate, Carbon $endDate): array
    {
        $hourlyStats = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        return $hourlyStats->toArray();
    }
    
    /**
     * 获取用户地理分布
     */
    protected function getUserGeographicStats(Carbon $startDate, Carbon $endDate): array
    {
        // 这里简化处理，实际应该从IP地址解析
        return [
            'Japan' => 45,
            'China' => 30,
            'USA' => 15,
            'Other' => 10,
        ];
    }
    
    /**
     * 计算收入预测
     */
    protected function calculateRevenueForecast(Carbon $startDate, Carbon $endDate): array
    {
        // 获取历史数据进行简单线性回归预测
        $historicalData = Order::whereBetween('created_at', [
            $startDate->copy()->subDays(30),
            $startDate
        ])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        if ($historicalData->count() < 7) {
            return [];
        }
        
        // 简单的移动平均预测
        $forecast = [];
        $period = CarbonPeriod::create($endDate->copy()->addDay(), $endDate->copy()->addDays(7));
        
        foreach ($period as $day) {
            $dayOfWeek = $day->dayOfWeek;
            $historicalSameDay = $historicalData->filter(function ($item) use ($dayOfWeek) {
                return Carbon::parse($item->date)->dayOfWeek === $dayOfWeek;
            });
            
            if ($historicalSameDay->isNotEmpty()) {
                $predictedRevenue = $historicalSameDay->avg('revenue');
            } else {
                $predictedRevenue = $historicalData->avg('revenue');
            }
            
            $forecast[] = [
                'date' => $day->toDateString(),
                'predicted_revenue' => round($predictedRevenue, 2),
            ];
        }
        
        return $forecast;
    }
    
    /**
     * 导出报表数据
     */
    public function exportReport(string $reportType, array $params = []): array
    {
        switch ($reportType) {
            case 'sales':
                $data = $this->getSalesReport($params);
                break;
            case 'user_behavior':
                $data = $this->getUserBehaviorReport($params);
                break;
            case 'product_analysis':
                $data = $this->getProductAnalysisReport($params);
                break;
            case 'inquiry_analysis':
                $data = $this->getInquiryAnalysisReport($params);
                break;
            case 'financial':
                $data = $this->getFinancialReport($params);
                break;
            default:
                throw new \InvalidArgumentException('不支持的报表类型');
        }
        
        return [
            'report_type' => $reportType,
            'generated_at' => now()->toISOString(),
            'data' => $data,
        ];
    }
    
    /**
     * 清除报表缓存
     */
    public function clearReportCache(): int
    {
        $cleared = 0;
        
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $pattern = 'report:*';
                $keys = app('redis')->keys($pattern);
                
                if (!empty($keys)) {
                    $cleared = app('redis')->del($keys);
                }
            }
        } catch (\Exception $e) {
            // 忽略清除失败
        }
        
        return $cleared;
    }
}