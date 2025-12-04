<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Layers;

use App\Domain\Analytics\Abstractions\DataWarehouseLayer;

/**
 * DWS (Data Warehouse Summary) 数据仓库汇总层
 * 基于DWD层进行数据聚合和汇总，形成各种分析指标
 */
class DWSLayer extends DataWarehouseLayer
{
    public function __construct()
    {
        parent::__construct('DWS');
    }

    /**
     * 定义 DWS 表结构
     */
    protected function defineSchema(): array
    {
        return [
            'dws_sales_daily' => [
                'id' => 'BIGINT PRIMARY KEY',
                'date_key' => 'INT',
                'year' => 'INT',
                'month' => 'INT',
                'quarter' => 'INT',
                'week' => 'INT',
                'total_orders' => 'INT',
                'total_customers' => 'INT',
                'new_customers' => 'INT',
                'total_revenue' => 'DECIMAL(15,2)',
                'total_revenue_cny' => 'DECIMAL(15,2)',
                'avg_order_value' => 'DECIMAL(10,2)',
                'total_quantity' => 'INT',
                'unique_products' => 'INT',
                'conversion_rate' => 'DECIMAL(5,4)',
                'customer_retention_rate' => 'DECIMAL(5,4)',
                'region_performance' => 'JSON',
                'category_performance' => 'JSON',
                'etl_time' => 'TIMESTAMP',
                'etl_batch_id' => 'VARCHAR(50)'
            ],
            'dws_customer_rfm' => [
                'id' => 'BIGINT PRIMARY KEY',
                'user_key' => 'BIGINT',
                'user_id' => 'BIGINT',
                'recency_score' => 'INT',
                'frequency_score' => 'INT',
                'monetary_score' => 'INT',
                'rfm_score' => 'VARCHAR(3)',
                'customer_segment' => 'VARCHAR(20)',
                'last_order_date' => 'DATE',
                'first_order_date' => 'DATE',
                'total_orders' => 'INT',
                'total_revenue' => 'DECIMAL(12,2)',
                'avg_order_value' => 'DECIMAL(10,2)',
                'days_since_last_order' => 'INT',
                'order_frequency_days' => 'DECIMAL(8,2)',
                'lifetime_value' => 'DECIMAL(12,2)',
                'churn_probability' => 'DECIMAL(5,4)',
                'prediction_date' => 'DATE',
                'etl_time' => 'TIMESTAMP'
            ],
            'dws_product_performance' => [
                'id' => 'BIGINT PRIMARY KEY',
                'product_key' => 'BIGINT',
                'product_id' => 'BIGINT',
                'sku' => 'VARCHAR(100)',
                'product_name' => 'VARCHAR(255)',
                'category_l1' => 'VARCHAR(100)',
                'category_l2' => 'VARCHAR(100)',
                'brand' => 'VARCHAR(100)',
                'total_sales' => 'INT',
                'total_revenue' => 'DECIMAL(12,2)',
                'total_revenue_cny' => 'DECIMAL(12,2)',
                'avg_price' => 'DECIMAL(10,2)',
                'profit_margin' => 'DECIMAL(5,4)',
                'inventory_turnover' => 'DECIMAL(8,2)',
                'days_of_supply' => 'INT',
                'stockout_count' => 'INT',
                'return_rate' => 'DECIMAL(5,4)',
                'customer_rating' => 'DECIMAL(3,2)',
                'view_count' => 'INT',
                'conversion_rate' => 'DECIMAL(5,4)',
                'trend_indicator' => 'VARCHAR(10)',
                'performance_period' => 'VARCHAR(20)',
                'etl_time' => 'TIMESTAMP'
            ],
            'dws_inventory_analysis' => [
                'id' => 'BIGINT PRIMARY KEY',
                'date_key' => 'INT',
                'warehouse_code' => 'VARCHAR(20)',
                'total_products' => 'INT',
                'total_value' => 'DECIMAL(15,2)',
                'total_value_cny' => 'DECIMAL(15,2)',
                'available_quantity' => 'INT',
                'reserved_quantity' => 'INT',
                'out_of_stock_count' => 'INT',
                'low_stock_count' => 'INT',
                'overstock_count' => 'INT',
                'avg_turnover_days' => 'DECIMAL(8,2)',
                'obsolete_value' => 'DECIMAL(12,2)',
                'carrying_cost' => 'DECIMAL(12,2)',
                'service_level' => 'DECIMAL(5,4)',
                'forecast_accuracy' => 'DECIMAL(5,4)',
                'reorder_suggestions' => 'JSON',
                'etl_time' => 'TIMESTAMP'
            ],
            'dws_financial_summary' => [
                'id' => 'BIGINT PRIMARY KEY',
                'period_type' => 'VARCHAR(20)',
                'period_key' => 'VARCHAR(20)',
                'period_start' => 'DATE',
                'period_end' => 'DATE',
                'total_revenue' => 'DECIMAL(15,2)',
                'total_revenue_cny' => 'DECIMAL(15,2)',
                'cost_of_goods_sold' => 'DECIMAL(15,2)',
                'gross_profit' => 'DECIMAL(15,2)',
                'gross_margin' => 'DECIMAL(5,4)',
                'operating_expenses' => 'DECIMAL(15,2)',
                'operating_profit' => 'DECIMAL(15,2)',
                'operating_margin' => 'DECIMAL(5,4)',
                'net_profit' => 'DECIMAL(15,2)',
                'net_margin' => 'DECIMAL(5,4)',
                'cash_flow' => 'DECIMAL(15,2)',
                'accounts_receivable' => 'DECIMAL(15,2)',
                'accounts_payable' => 'DECIMAL(15,2)',
                'inventory_value' => 'DECIMAL(15,2)',
                'working_capital' => 'DECIMAL(15,2)',
                'etl_time' => 'TIMESTAMP'
            ]
        ];
    }

    /**
     * 定义索引
     */
    protected function defineIndexes(): array
    {
        return [
            'dws_sales_daily' => [
                'idx_date_key' => 'date_key',
                'idx_year_month' => 'year,month',
                'idx_quarter' => 'quarter',
                'idx_week' => 'week',
                'idx_etl_time' => 'etl_time'
            ],
            'dws_customer_rfm' => [
                'idx_user_key' => 'user_key',
                'idx_user_id' => 'user_id',
                'idx_rfm_score' => 'rfm_score',
                'idx_customer_segment' => 'customer_segment',
                'idx_churn_probability' => 'churn_probability'
            ],
            'dws_product_performance' => [
                'idx_product_key' => 'product_key',
                'idx_product_id' => 'product_id',
                'idx_sku' => 'sku',
                'idx_category_l1' => 'category_l1',
                'idx_brand' => 'brand',
                'idx_trend_indicator' => 'trend_indicator'
            ],
            'dws_inventory_analysis' => [
                'idx_date_key' => 'date_key',
                'idx_warehouse_code' => 'warehouse_code',
                'idx_etl_time' => 'etl_time'
            ],
            'dws_financial_summary' => [
                'idx_period_type' => 'period_type',
                'idx_period_key' => 'period_key',
                'idx_period_start' => 'period_start',
                'idx_period_end' => 'period_end'
            ]
        ];
    }

    /**
     * 数据验证规则
     */
    public function getValidationRules(): array
    {
        return [
            'id' => 'required|numeric|positive',
            'date_key' => 'required|numeric',
            'total_orders' => 'required|numeric|positive',
            'total_revenue' => 'required|numeric',
            'user_key' => 'required|numeric|positive',
            'recency_score' => 'required|numeric|min:1|max:5',
            'frequency_score' => 'required|numeric|min:1|max:5',
            'monetary_score' => 'required|numeric|min:1|max:5',
            'product_key' => 'required|numeric|positive',
            'total_sales' => 'required|numeric|positive',
            'period_type' => 'required|string',
            'period_key' => 'required|string'
        ];
    }

    /**
     * 数据转换逻辑
     */
    public function transform(array $rawData): array
    {
        return array_map(function ($record) {
            return [
                ...$record,
                'etl_time' => now(),
                'etl_batch_id' => $this->generateBatchId(),
                'conversion_rate' => $this->calculateConversionRate($record),
                'customer_retention_rate' => $this->calculateRetentionRate($record),
                'avg_order_value' => $this->calculateAvgOrderValue($record),
                'profit_margin' => $this->calculateProfitMargin($record),
                'inventory_turnover' => $this->calculateInventoryTurnover($record),
                'churn_probability' => $this->calculateChurnProbability($record),
                'trend_indicator' => $this->calculateTrendIndicator($record)
            ];
        }, $rawData);
    }

    /**
     * 计算转化率
     */
    private function calculateConversionRate(array $record): float
    {
        $visitors = $record['total_visitors'] ?? 1;
        $orders = $record['total_orders'] ?? 0;
        
        return round($orders / $visitors, 4);
    }

    /**
     * 计算客户留存率
     */
    private function calculateRetentionRate(array $record): float
    {
        $previousCustomers = $record['previous_period_customers'] ?? 1;
        $returningCustomers = $record['returning_customers'] ?? 0;
        
        return round($returningCustomers / $previousCustomers, 4);
    }

    /**
     * 计算平均订单价值
     */
    private function calculateAvgOrderValue(array $record): float
    {
        $orders = $record['total_orders'] ?? 1;
        $revenue = $record['total_revenue'] ?? 0;
        
        return round($revenue / $orders, 2);
    }

    /**
     * 计算利润率
     */
    private function calculateProfitMargin(array $record): float
    {
        $revenue = $record['total_revenue'] ?? 1;
        $cost = $record['total_cost'] ?? 0;
        
        return round(($revenue - $cost) / $revenue, 4);
    }

    /**
     * 计算库存周转率
     */
    private function calculateInventoryTurnover(array $record): float
    {
        $cogs = $record['cost_of_goods_sold'] ?? 1;
        $avgInventory = $record['avg_inventory_value'] ?? 1;
        
        return round($cogs / $avgInventory, 2);
    }

    /**
     * 计算客户流失概率
     */
    private function calculateChurnProbability(array $record): float
    {
        $daysSinceLastOrder = $record['days_since_last_order'] ?? 0;
        $orderFrequency = $record['order_frequency_days'] ?? 30;
        
        // 简化的流失概率计算
        if ($daysSinceLastOrder > $orderFrequency * 3) {
            return 0.8;
        } elseif ($daysSinceLastOrder > $orderFrequency * 2) {
            return 0.6;
        } elseif ($daysSinceLastOrder > $orderFrequency * 1.5) {
            return 0.4;
        } else {
            return 0.1;
        }
    }

    /**
     * 计算趋势指标
     */
    private function calculateTrendIndicator(array $record): string
    {
        $currentPeriod = $record['current_period_value'] ?? 0;
        $previousPeriod = $record['previous_period_value'] ?? 1;
        
        $growthRate = ($currentPeriod - $previousPeriod) / $previousPeriod;
        
        if ($growthRate > 0.2) {
            return 'Strong Up';
        } elseif ($growthRate > 0.05) {
            return 'Up';
        } elseif ($growthRate > -0.05) {
            return 'Stable';
        } elseif ($growthRate > -0.2) {
            return 'Down';
        } else {
            return 'Strong Down';
        }
    }

    /**
     * 生成批次ID
     */
    private function generateBatchId(): string
    {
        return 'BATCH_' . date('Ymd_His') . '_' . uniqid();
    }

    /**
     * RFM分析计算
     */
    public function calculateRFM(array $customerData): array
    {
        $recency = $this->calculateRecencyScore($customerData);
        $frequency = $this->calculateFrequencyScore($customerData);
        $monetary = $this->calculateMonetaryScore($customerData);
        
        return [
            'recency_score' => $recency,
            'frequency_score' => $frequency,
            'monetary_score' => $monetary,
            'rfm_score' => $recency . $frequency . $monetary,
            'customer_segment' => $this->segmentCustomer($recency, $frequency, $monetary)
        ];
    }

    /**
     * 计算近度得分
     */
    private function calculateRecencyScore(array $customerData): int
    {
        $daysSinceLastOrder = $customerData['days_since_last_order'] ?? 365;
        
        if ($daysSinceLastOrder <= 30) return 5;
        if ($daysSinceLastOrder <= 60) return 4;
        if ($daysSinceLastOrder <= 90) return 3;
        if ($daysSinceLastOrder <= 180) return 2;
        return 1;
    }

    /**
     * 计算频度得分
     */
    private function calculateFrequencyScore(array $customerData): int
    {
        $orderCount = $customerData['total_orders'] ?? 0;
        
        if ($orderCount >= 20) return 5;
        if ($orderCount >= 10) return 4;
        if ($orderCount >= 5) return 3;
        if ($orderCount >= 2) return 2;
        return 1;
    }

    /**
     * 计算金额得分
     */
    private function calculateMonetaryScore(array $customerData): int
    {
        $totalRevenue = $customerData['total_revenue'] ?? 0;
        
        if ($totalRevenue >= 100000) return 5;
        if ($totalRevenue >= 50000) return 4;
        if ($totalRevenue >= 20000) return 3;
        if ($totalRevenue >= 5000) return 2;
        return 1;
    }

    /**
     * 客户细分
     */
    private function segmentCustomer(int $recency, int $frequency, int $monetary): string
    {
        $rfm = $recency . $frequency . $monetary;
        
        return match (true) {
            $recency >= 4 && $frequency >= 4 && $monetary >= 4 => 'Champions',
            $recency >= 3 && $frequency >= 3 && $monetary >= 3 => 'Loyal Customers',
            $recency >= 4 && $frequency <= 2 => 'New Customers',
            $recency <= 2 && $frequency >= 3 => 'At Risk',
            $recency <= 2 && $frequency <= 2 => 'Lost',
            default => 'Potential'
        };
    }
}