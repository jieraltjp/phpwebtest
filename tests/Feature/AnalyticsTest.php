<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\BusinessAnalyticsService;
use App\Services\ReportGenerationService;
use App\Services\AnalyticsVisualizationService;
use App\Services\PredictiveAnalyticsService;
use App\Services\AnalyticsETLService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private BusinessAnalyticsService $analyticsService;
    private ReportGenerationService $reportService;
    private AnalyticsVisualizationService $visualizationService;
    private PredictiveAnalyticsService $predictiveService;
    private AnalyticsETLService $etlService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->analyticsService = app(BusinessAnalyticsService::class);
        $this->reportService = app(ReportGenerationService::class);
        $this->visualizationService = app(AnalyticsVisualizationService::class);
        $this->predictiveService = app(PredictiveAnalyticsService::class);
        $this->etlService = app(AnalyticsETLService::class);
    }

    /**
     * 测试销售趋势分析API
     */
    public function test_sales_trends_api(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->getJson('/api/analytics/sales-trends?period=30d');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'summary',
                        'trends',
                        'seasonality',
                        'growth_rates',
                        'forecasts',
                        'insights'
                    ]
                ]);
    }

    /**
     * 测试客户价值分析API
     */
    public function test_customer_value_api(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->getJson('/api/analytics/customer-value?segment=all');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'segments',
                        'top_customers',
                        'churn_risk',
                        'lifetime_value',
                        'retention_analysis',
                        'recommendations'
                    ]
                ]);
    }

    /**
     * 测试库存优化分析API
     */
    public function test_inventory_optimization_api(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->getJson('/api/analytics/inventory-optimization');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'overview',
                        'turnover_analysis',
                        'stockout_analysis',
                        'excess_inventory',
                        'reorder_recommendations',
                        'optimization_opportunities'
                    ]
                ]);
    }

    /**
     * 测试报表生成API
     */
    public function test_generate_report_api(): void
    {
        $reportData = [
            'type' => 'sales',
            'time_range' => '30d',
            'dimensions' => ['date', 'region'],
            'metrics' => ['revenue', 'orders']
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->postJson('/api/analytics/reports/generate', $reportData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'report_id',
                        'type',
                        'generated_at',
                        'data',
                        'summary',
                        'charts',
                        'metadata'
                    ]
                ]);
    }

    /**
     * 测试高管仪表板API
     */
    public function test_executive_dashboard_api(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->getJson('/api/analytics/dashboards/executive');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'dashboard_id',
                        'title',
                        'layout',
                        'widgets',
                        'kpi_cards',
                        'charts',
                        'real_time_data',
                        'alerts',
                        'last_updated'
                    ]
                ]);
    }

    /**
     * 测试时间序列预测API
     */
    public function test_time_series_forecast_api(): void
    {
        $forecastData = [
            'data' => [100, 120, 110, 130, 125, 140, 135, 150],
            'periods' => 7,
            'method' => 'linear',
            'confidence' => 0.95
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->postJson('/api/analytics/forecast/time-series', $forecastData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'forecast_id',
                        'method',
                        'periods',
                        'confidence_interval',
                        'predictions',
                        'confidence_intervals',
                        'accuracy_metrics',
                        'model_parameters',
                        'generated_at'
                    ]
                ]);
    }

    /**
     * 测试客户流失预测API
     */
    public function test_churn_prediction_api(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->postJson('/api/analytics/predict/churn', [
            'segment' => 'all',
            'timeframe' => '90d',
            'threshold' => 0.5
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'prediction_id',
                        'model_info',
                        'predictions',
                        'high_risk_customers',
                        'retention_recommendations',
                        'segment_analysis',
                        'generated_at'
                    ]
                ]);
    }

    /**
     * 测试ETL流程API
     */
    public function test_etl_run_api(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->postJson('/api/analytics/etl/run', [
            'aggregation_period' => 'daily'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'start_time',
                        'end_time',
                        'duration',
                        'records_processed',
                        'errors',
                        'warnings',
                        'status'
                    ]
                ]);
    }

    /**
     * 测试系统健康状态API
     */
    public function test_system_health_api(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->getJson('/api/analytics/system-health');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'database',
                        'cache',
                        'etl_service',
                        'prediction_models',
                        'overall'
                    ]
                ]);
    }

    /**
     * 测试业务分析服务
     */
    public function test_business_analytics_service(): void
    {
        // 测试销售趋势分析
        $salesTrends = $this->analyticsService->analyzeSalesTrends(['period' => '30d']);
        
        $this->assertIsArray($salesTrends);
        $this->assertArrayHasKey('summary', $salesTrends);
        $this->assertArrayHasKey('trends', $salesTrends);
        $this->assertArrayHasKey('insights', $salesTrends);

        // 测试客户价值分析
        $customerValue = $this->analyticsService->analyzeCustomerValue(['segment' => 'all']);
        
        $this->assertIsArray($customerValue);
        $this->assertArrayHasKey('segments', $customerValue);
        $this->assertArrayHasKey('top_customers', $customerValue);
        $this->assertArrayHasKey('recommendations', $customerValue);
    }

    /**
     * 测试报表生成服务
     */
    public function test_report_generation_service(): void
    {
        $config = [
            'type' => 'sales',
            'time_range' => '30d',
            'dimensions' => ['date'],
            'metrics' => ['revenue', 'orders']
        ];

        $report = $this->reportService->generateDynamicReport($config);
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('report_id', $report);
        $this->assertArrayHasKey('data', $report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('charts', $report);

        // 测试报表模板获取
        $templates = $this->reportService->getReportTemplates();
        
        $this->assertIsArray($templates);
        $this->assertArrayHasKey('sales_performance', $templates);
        $this->assertArrayHasKey('customer_analysis', $templates);
    }

    /**
     * 测试可视化服务
     */
    public function test_visualization_service(): void
    {
        // 测试高管仪表板生成
        $dashboard = $this->visualizationService->generateExecutiveDashboard(['period' => '30d']);
        
        $this->assertIsArray($dashboard);
        $this->assertArrayHasKey('dashboard_id', $dashboard);
        $this->assertArrayHasKey('layout', $dashboard);
        $this->assertArrayHasKey('widgets', $dashboard);
        $this->assertArrayHasKey('charts', $dashboard);

        // 测试交互式图表生成
        $chart = $this->visualizationService->generateInteractiveChart([
            'type' => 'line',
            'data_source' => 'sales',
            'title' => '测试图表'
        ]);
        
        $this->assertIsArray($chart);
        $this->assertArrayHasKey('chart_id', $chart);
        $this->assertArrayHasKey('type', $chart);
        $this->assertArrayHasKey('data', $chart);
    }

    /**
     * 测试预测分析服务
     */
    public function test_predictive_analytics_service(): void
    {
        // 测试时间序列预测
        $forecast = $this->predictiveService->timeSeriesForecast([
            'data' => [100, 120, 110, 130, 125, 140, 135, 150],
            'periods' => 7,
            'method' => 'linear'
        ]);
        
        $this->assertIsArray($forecast);
        $this->assertArrayHasKey('forecast_id', $forecast);
        $this->assertArrayHasKey('method', $forecast);
        $this->assertArrayHasKey('predictions', $forecast);
        $this->assertArrayHasKey('confidence_intervals', $forecast);

        // 测试销售预测
        $salesForecast = $this->predictiveService->salesForecast(['period' => '30d']);
        
        $this->assertIsArray($salesForecast);
        $this->assertArrayHasKey('forecast_id', $salesForecast);
        $this->assertArrayHasKey('forecast', $salesForecast);
        $this->assertArrayHasKey('insights', $salesForecast);
    }

    /**
     * 测试ETL服务
     */
    public function test_etl_service(): void
    {
        // 创建测试数据
        $this->createTestData();

        // 运行ETL流程
        $etlResult = $this->etlService->runFullETL([
            'aggregation_period' => 'daily'
        ]);
        
        $this->assertIsArray($etlResult);
        $this->assertArrayHasKey('start_time', $etlResult);
        $this->assertArrayHasKey('end_time', $etlResult);
        $this->assertArrayHasKey('records_processed', $etlResult);
        $this->assertArrayHasKey('status', $etlResult);
        $this->assertEquals('completed', $etlResult['status']);
    }

    /**
     * 测试缓存性能
     */
    public function test_cache_performance(): void
    {
        // 清除缓存
        Cache::flush();

        // 第一次调用（无缓存）
        $start = microtime(true);
        $result1 = $this->analyticsService->analyzeSalesTrends(['period' => '30d']);
        $time1 = microtime(true) - $start;

        // 第二次调用（有缓存）
        $start = microtime(true);
        $result2 = $this->analyticsService->analyzeSalesTrends(['period' => '30d']);
        $time2 = microtime(true) - $start;

        // 验证结果一致性
        $this->assertEquals($result1, $result2);

        // 验证缓存提升了性能
        $this->assertLessThan($time1, $time2);
    }

    /**
     * 测试大数据量处理性能
     */
    public function test_large_data_performance(): void
    {
        // 创建大量测试数据
        $this->createLargeTestData(10000);

        $start = microtime(true);
        
        // 测试大数据量分析
        $result = $this->analyticsService->analyzeSalesTrends(['period' => '90d']);
        
        $executionTime = microtime(true) - $start;

        // 验证执行时间在合理范围内（小于5秒）
        $this->assertLessThan(5.0, $executionTime);
        
        // 验证结果结构正确
        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
    }

    /**
     * 测试并发访问
     */
    public function test_concurrent_access(): void
    {
        // 模拟并发请求
        $promises = [];
        
        for ($i = 0; $i < 10; $i++) {
            $promises[] = $this->async(function() {
                return $this->analyticsService->analyzeSalesTrends(['period' => '30d']);
            });
        }

        // 等待所有请求完成
        $results = $this->awaitAll($promises);

        // 验证所有请求都成功返回
        foreach ($results as $result) {
            $this->assertIsArray($result);
            $this->assertArrayHasKey('summary', $result);
        }
    }

    /**
     * 测试数据质量
     */
    public function test_data_quality(): void
    {
        // 创建包含异常数据的测试数据
        $this->createTestDataWithAnomalies();

        $result = $this->analyticsService->analyzeSalesTrends(['period' => '30d']);
        
        // 验证数据质量检查
        $this->assertArrayHasKey('insights', $result);
        
        // 检查是否检测到异常
        $insights = $result['insights'];
        $hasAnomalyDetection = collect($insights)->contains('type', 'anomaly');
        
        $this->assertTrue($hasAnomalyDetection, '应该检测到数据异常');
    }

    /**
     * 测试错误处理
     */
    public function test_error_handling(): void
    {
        // 测试无效参数
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getValidToken()
        ])->getJson('/api/analytics/sales-trends?period=invalid');

        $response->assertStatus(422);

        // 测试无认证访问
        $response = $this->getJson('/api/analytics/sales-trends');

        $response->assertStatus(401);

        // 测试无效的时间序列数据
        $this->expectException(\Exception::class);
        $this->predictiveService->timeSeriesForecast([
            'data' => [],
            'periods' => 7
        ]);
    }

    /**
     * 测试内存使用
     */
    public function test_memory_usage(): void
    {
        $initialMemory = memory_get_usage();

        // 执行内存密集型操作
        $this->createLargeTestData(50000);
        $result = $this->analyticsService->analyzeSalesTrends(['period' => '90d']);

        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;

        // 验证内存使用在合理范围内（小于100MB）
        $this->assertLessThan(100 * 1024 * 1024, $memoryUsed);
    }

    // 辅助方法

    /**
     * 获取有效的认证令牌
     */
    private function getValidToken(): string
    {
        // 登录获取令牌
        $response = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        return $response->json('data.access_token');
    }

    /**
     * 创建测试数据
     */
    private function createTestData(): void
    {
        // 创建测试用户
        DB::table('users')->insert([
            'id' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 创建测试产品
        DB::table('products')->insert([
            'id' => 1,
            'sku' => 'TEST-001',
            'name' => '测试产品',
            'price' => 100.00,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 创建测试订单
        for ($i = 0; $i < 100; $i++) {
            DB::table('orders')->insert([
                'id' => $i + 1,
                'user_id' => 1,
                'total_amount' => rand(50, 500),
                'status' => 'completed',
                'order_date' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * 创建大量测试数据
     */
    private function createLargeTestData(int $count): void
    {
        $orders = [];
        $now = now();

        for ($i = 0; $i < $count; $i++) {
            $orders[] = [
                'user_id' => 1,
                'total_amount' => rand(50, 500),
                'status' => 'completed',
                'order_date' => $now->copy()->subDays(rand(1, 90)),
                'created_at' => $now->copy()->subDays(rand(1, 90)),
                'updated_at' => $now
            ];

            // 批量插入以提高性能
            if (count($orders) >= 1000) {
                DB::table('orders')->insert($orders);
                $orders = [];
            }
        }

        // 插入剩余数据
        if (!empty($orders)) {
            DB::table('orders')->insert($orders);
        }
    }

    /**
     * 创建包含异常的测试数据
     */
    private function createTestDataWithAnomalies(): void
    {
        // 创建正常数据
        for ($i = 0; $i < 50; $i++) {
            DB::table('orders')->insert([
                'user_id' => 1,
                'total_amount' => rand(100, 200), // 正常范围
                'status' => 'completed',
                'order_date' => now()->subDays($i),
                'created_at' => now()->subDays($i),
                'updated_at' => now()
            ]);
        }

        // 插入异常数据（极高值）
        DB::table('orders')->insert([
            'user_id' => 1,
            'total_amount' => 50000, // 异常高值
            'status' => 'completed',
            'order_date' => now()->subDays(25),
            'created_at' => now()->subDays(25),
            'updated_at' => now()
        ]);

        // 插入异常数据（极低值）
        DB::table('orders')->insert([
            'user_id' => 1,
            'total_amount' => 1, // 异常低值
            'status' => 'completed',
            'order_date' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'updated_at' => now()
        ]);
    }

    /**
     * 异步执行函数（简化实现）
     */
    private function async(callable $callback): callable
    {
        return function() use ($callback) {
            return $callback();
        };
    }

    /**
     * 等待所有异步操作完成（简化实现）
     */
    private function awaitAll(array $promises): array
    {
        $results = [];
        foreach ($promises as $promise) {
            $results[] = $promise();
        }
        return $results;
    }
}