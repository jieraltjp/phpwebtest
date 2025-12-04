<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Analytics\Layers\ODSLayer;
use App\Domain\Analytics\Layers\DWDLayer;
use App\Domain\Analytics\Layers\DWSLayer;
use App\Domain\Analytics\Layers\ADSLayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * 分析ETL服务
 * 负责数据仓库各层之间的数据抽取、转换和加载
 */
class AnalyticsETLService
{
    private ODSLayer $odsLayer;
    private DWDLayer $dwdLayer;
    private DWSLayer $dwsLayer;
    private ADSLayer $adsLayer;
    private string $batchId;
    private array $etlStats;

    public function __construct()
    {
        $this->odsLayer = new ODSLayer();
        $this->dwdLayer = new DWDLayer();
        $this->dwsLayer = new DWSLayer();
        $this->adsLayer = new ADSLayer();
        $this->batchId = $this->generateBatchId();
        $this->etlStats = [
            'start_time' => now(),
            'records_processed' => 0,
            'errors' => [],
            'warnings' => []
        ];
    }

    /**
     * 执行完整的ETL流程
     */
    public function runFullETL(array $options = []): array
    {
        try {
            Log::info("Starting ETL process with batch ID: {$this->batchId}");
            
            // 1. ODS层数据抽取
            $this->extractToODS($options['ods_filters'] ?? []);
            
            // 2. DWD层数据转换
            $this->transformToDWD();
            
            // 3. DWS层数据聚合
            $this->aggregateToDWS($options['aggregation_period'] ?? 'daily');
            
            // 4. ADS层数据应用
            $this->applyToADS();
            
            // 5. 数据质量检查
            $this->performDataQualityCheck();
            
            $this->etlStats['end_time'] = now();
            $this->etlStats['duration'] = $this->etlStats['start_time']->diffInSeconds($this->etlStats['end_time']);
            $this->etlStats['status'] = 'completed';
            
            Log::info("ETL process completed successfully", $this->etlStats);
            
            return $this->etlStats;
            
        } catch (Exception $e) {
            $this->etlStats['status'] = 'failed';
            $this->etlStats['error'] = $e->getMessage();
            $this->etlStats['end_time'] = now();
            
            Log::error("ETL process failed", [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * 抽取数据到ODS层
     */
    private function extractToODS(array $filters = []): void
    {
        Log::info("Starting ODS extraction");
        
        $sourceTables = ['orders', 'order_items', 'products', 'users', 'inquiries'];
        
        foreach ($sourceTables as $table) {
            try {
                $rawData = $this->odsLayer->extractFromSource($table, $filters);
                
                if (!empty($rawData)) {
                    $this->loadToODSTable($table, $rawData);
                    $this->etlStats['records_processed'] += count($rawData);
                }
                
            } catch (Exception $e) {
                $this->etlStats['errors'][] = "ODS extraction failed for table {$table}: " . $e->getMessage();
                Log::error("ODS extraction error", ['table' => $table, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * 加载数据到ODS表
     */
    private function loadToODSTable(string $tableName, array $data): void
    {
        $odsTableName = "ods_{$tableName}";
        
        // 清理现有数据（根据配置决定是否全量替换或增量更新）
        if ($this->shouldFullReplace($tableName)) {
            DB::table($odsTableName)->truncate();
        }
        
        // 批量插入数据
        $chunks = array_chunk($data, 1000);
        foreach ($chunks as $chunk) {
            $transformedData = $this->odsLayer->transform($chunk);
            DB::table($odsTableName)->insert($transformedData);
        }
        
        Log::info("Loaded " . count($data) . " records to {$odsTableName}");
    }

    /**
     * 转换数据到DWD层
     */
    private function transformToDWD(): void
    {
        Log::info("Starting DWD transformation");
        
        // 生成日期维度数据
        $this->generateDateDimensions();
        
        // 转换订单事实数据
        $this->transformOrderFacts();
        
        // 转换用户维度数据
        $this->transformUserDimensions();
        
        // 转换产品维度数据
        $this->transformProductDimensions();
        
        // 转换库存事实数据
        $this->transformInventoryFacts();
    }

    /**
     * 生成日期维度
     */
    private function generateDateDimensions(): void
    {
        $startDate = new \DateTime('2020-01-01');
        $endDate = new \DateTime('+2 years');
        
        $dateDimensions = $this->dwdLayer->generateDateDimensions($startDate, $endDate);
        
        DB::table('dwd_dim_date')->insert($dateDimensions);
        
        Log::info("Generated " . count($dateDimensions) . " date dimension records");
    }

    /**
     * 转换订单事实数据
     */
    private function transformOrderFacts(): void
    {
        $odsOrders = DB::table('ods_orders')->get();
        $odsOrderItems = DB::table('ods_order_items')->get();
        
        $factData = [];
        
        foreach ($odsOrders as $order) {
            $orderItems = $odsOrderItems->where('order_id', $order->id);
            
            foreach ($orderItems as $item) {
                $factData[] = [
                    'fact_id' => $this->generateFactId(),
                    'order_id' => $order->id,
                    'order_date_key' => $this->getDateKey($order->order_date),
                    'user_key' => $this->getUserKey($order->user_id),
                    'product_key' => $this->getProductKey($item->product_id),
                    'order_amount' => $item->total_price,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'status_code' => $this->getStatusCode($order->status),
                    'region_code' => $this->getRegionCode($order->user_id),
                    'etl_batch_id' => $this->batchId,
                    'etl_time' => now()
                ];
            }
        }
        
        if (!empty($factData)) {
            $transformedData = $this->dwdLayer->transform($factData);
            DB::table('dwd_fact_orders')->insert($transformedData);
        }
        
        Log::info("Transformed " . count($factData) . " order fact records");
    }

    /**
     * 转换用户维度数据
     */
    private function transformUserDimensions(): void
    {
        $odsUsers = DB::table('ods_users')->get();
        
        $dimensionData = [];
        
        foreach ($odsUsers as $user) {
            $dimensionData[] = [
                'user_key' => $this->generateUserKey(),
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => trim($user->first_name . ' ' . $user->last_name),
                'company_name' => $user->company,
                'country_code' => $this->getCountryCode($user->country),
                'country_name' => $user->country,
                'registration_date_key' => $this->getDateKey($user->registration_date),
                'is_active' => true,
                'effective_from' => now()->format('Y-m-d'),
                'effective_to' => '9999-12-31',
                'is_current' => true
            ];
        }
        
        if (!empty($dimensionData)) {
            $transformedData = $this->dwdLayer->transform($dimensionData);
            DB::table('dwd_dim_user')->insert($transformedData);
        }
        
        Log::info("Transformed " . count($dimensionData) . " user dimension records");
    }

    /**
     * 转换产品维度数据
     */
    private function transformProductDimensions(): void
    {
        $odsProducts = DB::table('ods_products')->get();
        
        $dimensionData = [];
        
        foreach ($odsProducts as $product) {
            $dimensionData[] = [
                'product_key' => $this->generateProductKey(),
                'product_id' => $product->id,
                'sku' => $product->sku,
                'product_name' => $product->name,
                'category_l1' => $this->getCategoryLevel1($product->category),
                'category_l2' => $this->getCategoryLevel2($product->category),
                'brand' => $this->extractBrand($product->name),
                'unit_price' => $product->price,
                'is_active' => true,
                'effective_from' => now()->format('Y-m-d'),
                'effective_to' => '9999-12-31',
                'is_current' => true
            ];
        }
        
        if (!empty($dimensionData)) {
            $transformedData = $this->dwdLayer->transform($dimensionData);
            DB::table('dwd_dim_product')->insert($transformedData);
        }
        
        Log::info("Transformed " . count($dimensionData) . " product dimension records");
    }

    /**
     * 聚合数据到DWS层
     */
    private function aggregateToDWS(string $period = 'daily'): void
    {
        Log::info("Starting DWS aggregation for period: {$period}");
        
        match ($period) {
            'daily' => $this->aggregateDailyData(),
            'weekly' => $this->aggregateWeeklyData(),
            'monthly' => $this->aggregateMonthlyData(),
            default => $this->aggregateDailyData()
        };
    }

    /**
     * 聚合日数据
     */
    private function aggregateDailyData(): void
    {
        // 聚合销售日报
        $salesData = DB::table('dwd_fact_orders')
            ->select(
                'order_date_key',
                DB::raw('COUNT(DISTINCT order_id) as total_orders'),
                DB::raw('COUNT(DISTINCT user_key) as total_customers'),
                DB::raw('SUM(order_amount) as total_revenue'),
                DB::raw('AVG(order_amount) as avg_order_value'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT product_key) as unique_products')
            )
            ->groupBy('order_date_key')
            ->get();
        
        $aggregatedData = [];
        
        foreach ($salesData as $data) {
            $dateInfo = $this->getDateInfo($data->order_date_key);
            
            $aggregatedData[] = [
                'date_key' => $data->order_date_key,
                'year' => $dateInfo['year'],
                'month' => $dateInfo['month'],
                'quarter' => $dateInfo['quarter'],
                'week' => $dateInfo['week'],
                'total_orders' => $data->total_orders,
                'total_customers' => $data->total_customers,
                'total_revenue' => $data->total_revenue,
                'avg_order_value' => $data->avg_order_value,
                'total_quantity' => $data->total_quantity,
                'unique_products' => $data->unique_products,
                'etl_batch_id' => $this->batchId
            ];
        }
        
        if (!empty($aggregatedData)) {
            $transformedData = $this->dwsLayer->transform($aggregatedData);
            DB::table('dws_sales_daily')->insert($transformedData);
        }
        
        // 计算RFM分析
        $this->calculateRFMAnalysis();
        
        Log::info("Aggregated " . count($aggregatedData) . " daily sales records");
    }

    /**
     * 计算RFM分析
     */
    private function calculateRFMAnalysis(): void
    {
        $customers = DB::table('dwd_fact_orders')
            ->select(
                'user_key',
                DB::raw('MAX(order_date_key) as last_order_date'),
                DB::raw('COUNT(DISTINCT order_id) as total_orders'),
                DB::raw('SUM(order_amount) as total_revenue'),
                DB::raw('AVG(order_amount) as avg_order_value')
            )
            ->groupBy('user_key')
            ->get();
        
        $rfmData = [];
        
        foreach ($customers as $customer) {
            $customerData = [
                'days_since_last_order' => $this->calculateDaysSince($customer->last_order_date),
                'total_orders' => $customer->total_orders,
                'total_revenue' => $customer->total_revenue,
                'avg_order_value' => $customer->avg_order_value
            ];
            
            $rfmAnalysis = $this->dwsLayer->calculateRFM($customerData);
            
            $rfmData[] = [
                'user_key' => $customer->user_key,
                'recency_score' => $rfmAnalysis['recency_score'],
                'frequency_score' => $rfmAnalysis['frequency_score'],
                'monetary_score' => $rfmAnalysis['monetary_score'],
                'rfm_score' => $rfmAnalysis['rfm_score'],
                'customer_segment' => $rfmAnalysis['customer_segment'],
                'last_order_date' => $this->convertDateKeyToDate($customer->last_order_date),
                'total_orders' => $customer->total_orders,
                'total_revenue' => $customer->total_revenue,
                'avg_order_value' => $customer->avg_order_value,
                'days_since_last_order' => $customerData['days_since_last_order'],
                'lifetime_value' => $customer->total_revenue * 1.5, // 简化的LTV计算
                'prediction_date' => now()->format('Y-m-d')
            ];
        }
        
        if (!empty($rfmData)) {
            $transformedData = $this->dwsLayer->transform($rfmData);
            DB::table('dws_customer_rfm')->insert($transformedData);
        }
        
        Log::info("Calculated RFM analysis for " . count($rfmData) . " customers");
    }

    /**
     * 应用数据到ADS层
     */
    private function applyToADS(): void
    {
        Log::info("Starting ADS application");
        
        // 生成高管仪表板数据
        $this->generateExecutiveDashboard();
        
        // 生成客户洞察数据
        $this->generateCustomerInsights();
        
        // 生成产品智能数据
        $this->generateProductIntelligence();
        
        // 生成库存优化数据
        $this->generateInventoryOptimization();
    }

    /**
     * 生成高管仪表板数据
     */
    private function generateExecutiveDashboard(): void
    {
        $latestData = DB::table('dws_sales_daily')
            ->orderBy('date_key', 'desc')
            ->first();
        
        $previousData = DB::table('dws_sales_daily')
            ->where('date_key', '<', $latestData->date_key)
            ->orderBy('date_key', 'desc')
            ->first();
        
        $dashboardData = [
            'dashboard_date' => now()->format('Y-m-d'),
            'period_type' => 'daily',
            'kpi_revenue' => $latestData->total_revenue ?? 0,
            'kpi_revenue_previous' => $previousData->total_revenue ?? 0,
            'kpi_orders' => $latestData->total_orders ?? 0,
            'kpi_orders_previous' => $previousData->total_orders ?? 0,
            'kpi_customers' => $latestData->total_customers ?? 0,
            'kpi_customers_previous' => $previousData->total_customers ?? 0,
            'kpi_avg_order_value' => $latestData->avg_order_value ?? 0,
            'top_products' => $this->getTopProducts(),
            'top_customers' => $this->getTopCustomers(),
            'region_performance' => $this->getRegionPerformance()
        ];
        
        $transformedData = $this->adsLayer->transform([$dashboardData]);
        DB::table('ads_executive_dashboard')->insert($transformedData);
        
        Log::info("Generated executive dashboard data");
    }

    /**
     * 执行数据质量检查
     */
    private function performDataQualityCheck(): void
    {
        Log::info("Starting data quality check");
        
        $qualityIssues = [];
        
        // 检查数据完整性
        $qualityIssues = array_merge($qualityIssues, $this->checkDataCompleteness());
        
        // 检查数据一致性
        $qualityIssues = array_merge($qualityIssues, $this->checkDataConsistency());
        
        // 检查数据准确性
        $qualityIssues = array_merge($qualityIssues, $this->checkDataAccuracy());
        
        if (!empty($qualityIssues)) {
            $this->etlStats['warnings'] = $qualityIssues;
            Log::warning("Data quality issues found", $qualityIssues);
        }
        
        Log::info("Data quality check completed");
    }

    /**
     * 检查数据完整性
     */
    private function checkDataCompleteness(): array
    {
        $issues = [];
        
        // 检查关键字段是否为空
        $nullOrders = DB::table('dwd_fact_orders')
            ->whereNull('order_amount')
            ->orWhereNull('user_key')
            ->count();
        
        if ($nullOrders > 0) {
            $issues[] = [
                'type' => 'completeness',
                'table' => 'dwd_fact_orders',
                'issue' => 'Found ' . $nullOrders . ' records with null key fields',
                'severity' => 'high'
            ];
        }
        
        return $issues;
    }

    /**
     * 检查数据一致性
     */
    private function checkDataConsistency(): array
    {
        $issues = [];
        
        // 检查外键一致性
        $orphanedOrders = DB::table('dwd_fact_orders')
            ->leftJoin('dwd_dim_user', 'dwd_fact_orders.user_key', '=', 'dwd_dim_user.user_key')
            ->whereNull('dwd_dim_user.user_key')
            ->count();
        
        if ($orphanedOrders > 0) {
            $issues[] = [
                'type' => 'consistency',
                'issue' => 'Found ' . $orphanedOrders . ' orphaned order records',
                'severity' => 'medium'
            ];
        }
        
        return $issues;
    }

    /**
     * 检查数据准确性
     */
    private function checkDataAccuracy(): array
    {
        $issues = [];
        
        // 检查负数金额
        $negativeAmounts = DB::table('dwd_fact_orders')
            ->where('order_amount', '<', 0)
            ->count();
        
        if ($negativeAmounts > 0) {
            $issues[] = [
                'type' => 'accuracy',
                'issue' => 'Found ' . $negativeAmounts . ' records with negative amounts',
                'severity' => 'high'
            ];
        }
        
        return $issues;
    }

    // 辅助方法
    private function generateBatchId(): string
    {
        return 'ETL_' . date('Ymd_His') . '_' . uniqid();
    }

    private function generateFactId(): string
    {
        return 'FACT_' . uniqid();
    }

    private function generateUserKey(): string
    {
        return 'USER_' . uniqid();
    }

    private function generateProductKey(): string
    {
        return 'PROD_' . uniqid();
    }

    private function getDateKey(string $date): int
    {
        return (int)date('Ymd', strtotime($date));
    }

    private function convertDateKeyToDate(int $dateKey): string
    {
        return date('Y-m-d', strtotime($dateKey));
    }

    private function calculateDaysSince(int $dateKey): int
    {
        $date = new \DateTime(date('Y-m-d', strtotime($dateKey)));
        $today = new \DateTime();
        return $today->diff($date)->days;
    }

    private function shouldFullReplace(string $table): bool
    {
        // 根据表类型和配置决定是否全量替换
        return in_array($table, ['products', 'users']);
    }

    // 其他辅助方法的简化实现
    private function getUserKey(int $userId): string { return 'USER_' . $userId; }
    private function getProductKey(int $productId): string { return 'PROD_' . $productId; }
    private function getStatusCode(string $status): string { return substr($status, 0, 3); }
    private function getRegionCode(int $userId): string { return 'REG001'; }
    private function getCountryCode(string $country): string { return 'CN'; }
    private function getCategoryLevel1(string $category): string { return explode('>', $category)[0] ?? 'Other'; }
    private function getCategoryLevel2(string $category): string { return explode('>', $category)[1] ?? 'Other'; }
    private function extractBrand(string $name): string { return 'Generic'; }
    private function getDateInfo(int $dateKey): array { return ['year' => 2024, 'month' => 1, 'quarter' => 1, 'week' => 1]; }
    private function getTopProducts(): array { return []; }
    private function getTopCustomers(): array { return []; }
    private function getRegionPerformance(): array { return []; }
    private function transformInventoryFacts(): void { /* 简化实现 */ }
    private function generateCustomerInsights(): void { /* 简化实现 */ }
    private function generateProductIntelligence(): void { /* 简化实现 */ }
    private function generateInventoryOptimization(): void { /* 简化实现 */ }
    private function aggregateWeeklyData(): void { /* 简化实现 */ }
    private function aggregateMonthlyData(): void { /* 简化实现 */ }
}