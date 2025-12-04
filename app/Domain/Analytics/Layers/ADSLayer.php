<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Layers;

use App\Domain\Analytics\Abstractions\DataWarehouseLayer;

/**
 * ADS (Application Data Service) 应用数据服务层
 * 面向应用的数据集市，提供具体的业务分析结果
 */
class ADSLayer extends DataWarehouseLayer
{
    public function __construct()
    {
        parent::__construct('ADS');
    }

    /**
     * 定义 ADS 表结构
     */
    protected function defineSchema(): array
    {
        return [
            'ads_executive_dashboard' => [
                'id' => 'BIGINT PRIMARY KEY',
                'dashboard_date' => 'DATE',
                'period_type' => 'VARCHAR(20)',
                'kpi_revenue' => 'DECIMAL(15,2)',
                'kpi_revenue_growth' => 'DECIMAL(5,4)',
                'kpi_orders' => 'INT',
                'kpi_orders_growth' => 'DECIMAL(5,4)',
                'kpi_customers' => 'INT',
                'kpi_customers_growth' => 'DECIMAL(5,4)',
                'kpi_avg_order_value' => 'DECIMAL(10,2)',
                'kpi_conversion_rate' => 'DECIMAL(5,4)',
                'kpi_customer_retention' => 'DECIMAL(5,4)',
                'kpi_inventory_turnover' => 'DECIMAL(8,2)',
                'kpi_gross_margin' => 'DECIMAL(5,4)',
                'kpi_net_margin' => 'DECIMAL(5,4)',
                'top_products' => 'JSON',
                'top_customers' => 'JSON',
                'region_performance' => 'JSON',
                'alerts' => 'JSON',
                'recommendations' => 'JSON',
                'etl_time' => 'TIMESTAMP'
            ],
            'ads_sales_forecast' => [
                'id' => 'BIGINT PRIMARY KEY',
                'forecast_date' => 'DATE',
                'forecast_period' => 'VARCHAR(20)',
                'forecast_type' => 'VARCHAR(20)',
                'predicted_revenue' => 'DECIMAL(15,2)',
                'predicted_orders' => 'INT',
                'predicted_customers' => 'INT',
                'confidence_level' => 'DECIMAL(3,2)',
                'accuracy_score' => 'DECIMAL(5,4)',
                'model_version' => 'VARCHAR(20)',
                'factors_considered' => 'JSON',
                'trend_analysis' => 'JSON',
                'seasonal_adjustments' => 'JSON',
                'external_factors' => 'JSON',
                'created_at' => 'TIMESTAMP',
                'updated_at' => 'TIMESTAMP'
            ],
            'ads_customer_insights' => [
                'id' => 'BIGINT PRIMARY KEY',
                'customer_id' => 'BIGINT',
                'analysis_date' => 'DATE',
                'customer_segment' => 'VARCHAR(20)',
                'lifetime_value' => 'DECIMAL(12,2)',
                'churn_risk' => 'VARCHAR(20)',
                'churn_probability' => 'DECIMAL(5,4)',
                'next_purchase_prediction' => 'DATE',
                'preferred_categories' => 'JSON',
                'purchase_patterns' => 'JSON',
                'price_sensitivity' => 'VARCHAR(20)',
                'communication_preference' => 'VARCHAR(20)',
                'loyalty_score' => 'INT',
                'satisfaction_score' => 'INT',
                'recommendation_score' => 'DECIMAL(3,2)',
                'upsell_opportunities' => 'JSON',
                'cross_sell_opportunities' => 'JSON',
                'risk_factors' => 'JSON',
                'action_recommendations' => 'JSON',
                'etl_time' => 'TIMESTAMP'
            ],
            'ads_product_intelligence' => [
                'id' => 'BIGINT PRIMARY KEY',
                'product_id' => 'BIGINT',
                'sku' => 'VARCHAR(100)',
                'analysis_date' => 'DATE',
                'performance_grade' => 'VARCHAR(5)',
                'sales_velocity' => 'VARCHAR(20)',
                'profitability_rank' => 'INT',
                'market_position' => 'VARCHAR(20)',
                'competitor_analysis' => 'JSON',
                'price_elasticity' => 'DECIMAL(8,4)',
                'demand_forecast' => 'JSON',
                'inventory_health' => 'VARCHAR(20)',
                'seasonal_pattern' => 'JSON',
                'customer_satisfaction' => 'DECIMAL(3,2)',
                'return_analysis' => 'JSON',
                'optimization_suggestions' => 'JSON',
                'pricing_recommendations' => 'JSON',
                'marketing_insights' => 'JSON',
                'etl_time' => 'TIMESTAMP'
            ],
            'ads_inventory_optimization' => [
                'id' => 'BIGINT PRIMARY KEY',
                'product_id' => 'BIGINT',
                'warehouse_code' => 'VARCHAR(20)',
                'optimization_date' => 'DATE',
                'current_stock' => 'INT',
                'optimal_stock' => 'INT',
                'reorder_point' => 'INT',
                'reorder_quantity' => 'INT',
                'safety_stock' => 'INT',
                'stockout_risk' => 'VARCHAR(20)',
                'excess_stock_risk' => 'VARCHAR(20)',
                'carrying_cost' => 'DECIMAL(10,2)',
                'opportunity_cost' => 'DECIMAL(10,2)',
                'service_level_target' => 'DECIMAL(3,2)',
                'current_service_level' => 'DECIMAL(3,2)',
                'demand variability' => 'DECIMAL(5,4)',
                'lead_time' => 'INT',
                'replenishment_strategy' => 'VARCHAR(50)',
                'optimization_actions' => 'JSON',
                'cost_savings_potential' => 'DECIMAL(10,2)',
                'etl_time' => 'TIMESTAMP'
            ],
            'ads_market_analysis' => [
                'id' => 'BIGINT PRIMARY KEY',
                'analysis_date' => 'DATE',
                'market_segment' => 'VARCHAR(100)',
                'total_addressable_market' => 'DECIMAL(15,2)',
                'market_share' => 'DECIMAL(5,4)',
                'growth_rate' => 'DECIMAL(5,4)',
                'competitor_count' => 'INT',
                'price_competitiveness' => 'DECIMAL(5,4)',
                'product_differentiation' => 'VARCHAR(20)',
                'customer_preferences' => 'JSON',
                'market_trends' => 'JSON',
                'opportunity_areas' => 'JSON',
                'threat_indicators' => 'JSON',
                'swot_analysis' => 'JSON',
                'market_penetration_rate' => 'DECIMAL(5,4)',
                'brand_strength' => 'DECIMAL(3,2)',
                'strategic_recommendations' => 'JSON',
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
            'ads_executive_dashboard' => [
                'idx_dashboard_date' => 'dashboard_date',
                'idx_period_type' => 'period_type',
                'idx_etl_time' => 'etl_time'
            ],
            'ads_sales_forecast' => [
                'idx_forecast_date' => 'forecast_date',
                'idx_forecast_period' => 'forecast_period',
                'idx_forecast_type' => 'forecast_type',
                'idx_confidence_level' => 'confidence_level'
            ],
            'ads_customer_insights' => [
                'idx_customer_id' => 'customer_id',
                'idx_analysis_date' => 'analysis_date',
                'idx_customer_segment' => 'customer_segment',
                'idx_churn_risk' => 'churn_risk',
                'idx_churn_probability' => 'churn_probability'
            ],
            'ads_product_intelligence' => [
                'idx_product_id' => 'product_id',
                'idx_sku' => 'sku',
                'idx_analysis_date' => 'analysis_date',
                'idx_performance_grade' => 'performance_grade',
                'idx_sales_velocity' => 'sales_velocity'
            ],
            'ads_inventory_optimization' => [
                'idx_product_id' => 'product_id',
                'idx_warehouse_code' => 'warehouse_code',
                'idx_optimization_date' => 'optimization_date',
                'idx_stockout_risk' => 'stockout_risk'
            ],
            'ads_market_analysis' => [
                'idx_analysis_date' => 'analysis_date',
                'idx_market_segment' => 'market_segment',
                'idx_etl_time' => 'etl_time'
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
            'dashboard_date' => 'required|date',
            'kpi_revenue' => 'required|numeric',
            'kpi_orders' => 'required|numeric|positive',
            'kpi_customers' => 'required|numeric|positive',
            'customer_id' => 'required|numeric|positive',
            'product_id' => 'required|numeric|positive',
            'forecast_date' => 'required|date',
            'confidence_level' => 'required|numeric|min:0|max:1',
            'churn_probability' => 'required|numeric|min:0|max:1',
            'performance_grade' => 'required|string',
            'market_segment' => 'required|string'
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
                'kpi_revenue_growth' => $this->calculateGrowthRate($record, 'kpi_revenue'),
                'kpi_orders_growth' => $this->calculateGrowthRate($record, 'kpi_orders'),
                'kpi_customers_growth' => $this->calculateGrowthRate($record, 'kpi_customers'),
                'performance_grade' => $this->assignPerformanceGrade($record),
                'churn_risk' => $this->assessChurnRisk($record),
                'stockout_risk' => $this->assessStockoutRisk($record),
                'excess_stock_risk' => $this->assessExcessStockRisk($record),
                'sales_velocity' => $this->calculateSalesVelocity($record),
                'alerts' => $this->generateAlerts($record),
                'recommendations' => $this->generateRecommendations($record),
                'action_recommendations' => $this->generateActionRecommendations($record)
            ];
        }, $rawData);
    }

    /**
     * 计算增长率
     */
    private function calculateGrowthRate(array $record, string $metric): float
    {
        $current = $record[$metric] ?? 0;
        $previous = $record[$metric . '_previous'] ?? 1;
        
        return round(($current - $previous) / $previous, 4);
    }

    /**
     * 分配绩效等级
     */
    private function assignPerformanceGrade(array $record): string
    {
        $score = 0;
        
        // 销售得分 (40%)
        $salesScore = $this->normalizeScore($record['total_revenue'] ?? 0, 0, 100000);
        $score += $salesScore * 0.4;
        
        // 利润得分 (30%)
        $profitScore = $this->normalizeScore($record['profit_margin'] ?? 0, 0, 0.5);
        $score += $profitScore * 0.3;
        
        // 客户满意度得分 (20%)
        $satisfactionScore = $this->normalizeScore($record['customer_satisfaction'] ?? 0, 0, 5);
        $score += $satisfactionScore * 0.2;
        
        // 库存健康得分 (10%)
        $inventoryScore = $this->normalizeScore($record['inventory_health_score'] ?? 0, 0, 100);
        $score += $inventoryScore * 0.1;
        
        return match (true) {
            $score >= 90 => 'A+',
            $score >= 85 => 'A',
            $score >= 80 => 'A-',
            $score >= 75 => 'B+',
            $score >= 70 => 'B',
            $score >= 65 => 'B-',
            $score >= 60 => 'C+',
            $score >= 55 => 'C',
            $score >= 50 => 'C-',
            $score >= 45 => 'D+',
            $score >= 40 => 'D',
            default => 'F'
        };
    }

    /**
     * 标准化得分
     */
    private function normalizeScore(float $value, float $min, float $max): float
    {
        if ($max <= $min) return 0;
        return min(100, max(0, (($value - $min) / ($max - $min)) * 100));
    }

    /**
     * 评估流失风险
     */
    private function assessChurnRisk(array $record): string
    {
        $probability = $record['churn_probability'] ?? 0;
        
        return match (true) {
            $probability >= 0.8 => 'Critical',
            $probability >= 0.6 => 'High',
            $probability >= 0.4 => 'Medium',
            $probability >= 0.2 => 'Low',
            default => 'Very Low'
        };
    }

    /**
     * 评估缺货风险
     */
    private function assessStockoutRisk(array $record): string
    {
        $currentStock = $record['current_stock'] ?? 0;
        $reorderPoint = $record['reorder_point'] ?? 0;
        $dailyDemand = $record['daily_demand'] ?? 1;
        
        $daysOfSupply = $dailyDemand > 0 ? $currentStock / $dailyDemand : 0;
        
        return match (true) {
            $currentStock <= $reorderPoint => 'Critical',
            $daysOfSupply <= 7 => 'High',
            $daysOfSupply <= 14 => 'Medium',
            $daysOfSupply <= 30 => 'Low',
            default => 'Very Low'
        };
    }

    /**
     * 评估库存过剩风险
     */
    private function assessExcessStockRisk(array $record): string
    {
        $currentStock = $record['current_stock'] ?? 0;
        $optimalStock = $record['optimal_stock'] ?? 1;
        $ratio = $currentStock / $optimalStock;
        
        return match (true) {
            $ratio >= 3 => 'Critical',
            $ratio >= 2 => 'High',
            $ratio >= 1.5 => 'Medium',
            $ratio >= 1.2 => 'Low',
            default => 'Very Low'
        };
    }

    /**
     * 计算销售速度
     */
    private function calculateSalesVelocity(array $record): string
    {
        $salesPerDay = $record['sales_per_day'] ?? 0;
        
        return match (true) {
            $salesPerDay >= 100 => 'Very Fast',
            $salesPerDay >= 50 => 'Fast',
            $salesPerDay >= 20 => 'Medium',
            $salesPerDay >= 5 => 'Slow',
            default => 'Very Slow'
        };
    }

    /**
     * 生成警报
     */
    private function generateAlerts(array $record): array
    {
        $alerts = [];
        
        // 收入警报
        if (($record['kpi_revenue_growth'] ?? 0) < -0.1) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Revenue decline超过10%',
                'severity' => 'high',
                'metric' => 'revenue_growth',
                'value' => $record['kpi_revenue_growth'] ?? 0
            ];
        }
        
        // 客户流失警报
        if (($record['churn_probability'] ?? 0) > 0.7) {
            $alerts[] = [
                'type' => 'critical',
                'message' => '高客户流失风险',
                'severity' => 'critical',
                'metric' => 'churn_probability',
                'value' => $record['churn_probability'] ?? 0
            ];
        }
        
        // 库存警报
        if (($record['stockout_risk'] ?? '') === 'Critical') {
            $alerts[] = [
                'type' => 'urgent',
                'message' => '关键库存缺货风险',
                'severity' => 'urgent',
                'metric' => 'stockout_risk',
                'value' => 'Critical'
            ];
        }
        
        return $alerts;
    }

    /**
     * 生成建议
     */
    private function generateRecommendations(array $record): array
    {
        $recommendations = [];
        
        // 增长建议
        if (($record['kpi_revenue_growth'] ?? 0) < 0) {
            $recommendations[] = [
                'category' => 'growth',
                'priority' => 'high',
                'action' => '推出促销活动',
                'description' => '考虑推出限时促销或折扣活动来刺激销售增长',
                'expected_impact' => '提升5-15%收入'
            ];
        }
        
        // 客户保留建议
        if (($record['churn_probability'] ?? 0) > 0.5) {
            $recommendations[] = [
                'category' => 'retention',
                'priority' => 'high',
                'action' => '客户关怀计划',
                'description' => '实施客户关怀计划，提供个性化服务',
                'expected_impact' => '降低20-30%流失率'
            ];
        }
        
        // 库存优化建议
        if (($record['excess_stock_risk'] ?? '') === 'High') {
            $recommendations[] = [
                'category' => 'inventory',
                'priority' => 'medium',
                'action' => '库存清理',
                'description' '通过促销或捆绑销售清理过剩库存',
                'expected_impact' => '减少15-25%库存成本'
            ];
        }
        
        return $recommendations;
    }

    /**
     * 生成行动建议
     */
    private function generateActionRecommendations(array $record): array
    {
        return [
            'immediate_actions' => $this->getImmediateActions($record),
            'short_term_actions' => $this->getShortTermActions($record),
            'long_term_actions' => $this->getLongTermActions($record)
        ];
    }

    /**
     * 获取立即行动建议
     */
    private function getImmediateActions(array $record): array
    {
        $actions = [];
        
        if (($record['stockout_risk'] ?? '') === 'Critical') {
            $actions[] = '立即补货关键产品';
        }
        
        if (($record['churn_probability'] ?? 0) > 0.8) {
            $actions[] = '联系高风险客户';
        }
        
        return $actions;
    }

    /**
     * 获取短期行动建议
     */
    private function getShortTermActions(array $record): array
    {
        $actions = [];
        
        if (($record['kpi_revenue_growth'] ?? 0) < -0.05) {
            $actions[] = '制定营销推广计划';
        }
        
        if (($record['conversion_rate'] ?? 0) < 0.02) {
            $actions[] = '优化网站用户体验';
        }
        
        return $actions;
    }

    /**
     * 获取长期行动建议
     */
    private function getLongTermActions(array $record): array
    {
        $actions = [];
        
        $actions[] = '产品线扩展规划';
        $actions[] = '客户忠诚度计划设计';
        $actions[] = '供应链优化项目';
        
        return $actions;
    }
}