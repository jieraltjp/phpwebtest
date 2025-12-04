<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Layers;

use App\Domain\Analytics\Abstractions\DataWarehouseLayer;

/**
 * DWD (Data Warehouse Detail) 数据仓库明细层
 * 对 ODS 数据进行清洗、标准化和维度退化
 */
class DWDLayer extends DataWarehouseLayer
{
    public function __construct()
    {
        parent::__construct('DWD');
    }

    /**
     * 定义 DWD 表结构
     */
    protected function defineSchema(): array
    {
        return [
            'dwd_fact_orders' => [
                'fact_id' => 'BIGINT PRIMARY KEY',
                'order_id' => 'BIGINT',
                'order_date_key' => 'INT',
                'user_key' => 'BIGINT',
                'product_key' => 'BIGINT',
                'order_amount' => 'DECIMAL(12,2)',
                'order_amount_cny' => 'DECIMAL(12,2)',
                'quantity' => 'INT',
                'unit_price' => 'DECIMAL(10,2)',
                'discount_amount' => 'DECIMAL(10,2)',
                'status_code' => 'VARCHAR(10)',
                'payment_method' => 'VARCHAR(20)',
                'shipping_method' => 'VARCHAR(20)',
                'region_code' => 'VARCHAR(10)',
                'etl_time' => 'TIMESTAMP',
                'etl_batch_id' => 'VARCHAR(50)'
            ],
            'dwd_dim_date' => [
                'date_key' => 'INT PRIMARY KEY',
                'full_date' => 'DATE',
                'year' => 'INT',
                'quarter' => 'INT',
                'month' => 'INT',
                'week' => 'INT',
                'day_of_month' => 'INT',
                'day_of_week' => 'INT',
                'day_of_year' => 'INT',
                'is_weekend' => 'BOOLEAN',
                'is_holiday' => 'BOOLEAN',
                'season' => 'VARCHAR(10)',
                'month_name' => 'VARCHAR(20)',
                'weekday_name' => 'VARCHAR(20)'
            ],
            'dwd_dim_user' => [
                'user_key' => 'BIGINT PRIMARY KEY',
                'user_id' => 'BIGINT',
                'username' => 'VARCHAR(100)',
                'email' => 'VARCHAR(255)',
                'full_name' => 'VARCHAR(255)',
                'company_name' => 'VARCHAR(255)',
                'industry' => 'VARCHAR(100)',
                'country_code' => 'VARCHAR(5)',
                'country_name' => 'VARCHAR(100)',
                'region' => 'VARCHAR(50)',
                'city' => 'VARCHAR(100)',
                'customer_segment' => 'VARCHAR(20)',
                'registration_date_key' => 'INT',
                'is_active' => 'BOOLEAN',
                'loyalty_tier' => 'VARCHAR(20)',
                'effective_from' => 'DATE',
                'effective_to' => 'DATE',
                'is_current' => 'BOOLEAN'
            ],
            'dwd_dim_product' => [
                'product_key' => 'BIGINT PRIMARY KEY',
                'product_id' => 'BIGINT',
                'sku' => 'VARCHAR(100)',
                'product_name' => 'VARCHAR(255)',
                'category_l1' => 'VARCHAR(100)',
                'category_l2' => 'VARCHAR(100)',
                'category_l3' => 'VARCHAR(100)',
                'brand' => 'VARCHAR(100)',
                'supplier_name' => 'VARCHAR(255)',
                'unit_price' => 'DECIMAL(10,2)',
                'cost_price' => 'DECIMAL(10,2)',
                'weight' => 'DECIMAL(8,2)',
                'dimensions' => 'VARCHAR(100)',
                'color' => 'VARCHAR(50)',
                'size' => 'VARCHAR(50)',
                'is_active' => 'BOOLEAN',
                'effective_from' => 'DATE',
                'effective_to' => 'DATE',
                'is_current' => 'BOOLEAN'
            ],
            'dwd_fact_inventory' => [
                'fact_id' => 'BIGINT PRIMARY KEY',
                'product_key' => 'BIGINT',
                'date_key' => 'INT',
                'warehouse_code' => 'VARCHAR(20)',
                'quantity_on_hand' => 'INT',
                'quantity_reserved' => 'INT',
                'quantity_available' => 'INT',
                'reorder_point' => 'INT',
                'reorder_quantity' => 'INT',
                'unit_cost' => 'DECIMAL(10,2)',
                'total_value' => 'DECIMAL(12,2)',
                'etl_time' => 'TIMESTAMP',
                'etl_batch_id' => 'VARCHAR(50)'
            ]
        ];
    }

    /**
     * 定义索引
     */
    protected function defineIndexes(): array
    {
        return [
            'dwd_fact_orders' => [
                'idx_order_date_key' => 'order_date_key',
                'idx_user_key' => 'user_key',
                'idx_product_key' => 'product_key',
                'idx_status_code' => 'status_code',
                'idx_region_code' => 'region_code',
                'idx_etl_time' => 'etl_time'
            ],
            'dwd_dim_date' => [
                'idx_year' => 'year',
                'idx_month' => 'month',
                'idx_quarter' => 'quarter',
                'idx_week' => 'week'
            ],
            'dwd_dim_user' => [
                'idx_user_id' => 'user_id',
                'idx_email' => 'email',
                'idx_country_code' => 'country_code',
                'idx_customer_segment' => 'customer_segment',
                'idx_is_current' => 'is_current'
            ],
            'dwd_dim_product' => [
                'idx_product_id' => 'product_id',
                'idx_sku' => 'sku',
                'idx_category_l1' => 'category_l1',
                'idx_category_l2' => 'category_l2',
                'idx_brand' => 'brand',
                'idx_is_current' => 'is_current'
            ],
            'dwd_fact_inventory' => [
                'idx_product_key' => 'product_key',
                'idx_date_key' => 'date_key',
                'idx_warehouse_code' => 'warehouse_code',
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
            'fact_id' => 'required|numeric|positive',
            'order_id' => 'required|numeric|positive',
            'order_date_key' => 'required|numeric',
            'user_key' => 'required|numeric|positive',
            'product_key' => 'required|numeric|positive',
            'order_amount' => 'required|numeric',
            'quantity' => 'required|numeric|positive',
            'unit_price' => 'required|numeric|positive',
            'date_key' => 'required|numeric',
            'full_date' => 'required|date'
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
                'order_amount_cny' => $this->convertToCNY($record['order_amount'] ?? 0, $record['currency'] ?? 'CNY'),
                'full_name' => $this->buildFullName($record),
                'customer_segment' => $this->determineCustomerSegment($record),
                'loyalty_tier' => $this->calculateLoyaltyTier($record)
            ];
        }, $rawData);
    }

    /**
     * 生成批次ID
     */
    private function generateBatchId(): string
    {
        return 'BATCH_' . date('Ymd_His') . '_' . uniqid();
    }

    /**
     * 货币转换到人民币
     */
    private function convertToCNY(float $amount, string $currency): float
    {
        $exchangeRates = [
            'CNY' => 1.0,
            'USD' => 7.2,
            'JPY' => 0.048,
            'EUR' => 7.8
        ];

        return $amount * ($exchangeRates[$currency] ?? 1.0);
    }

    /**
     * 构建全名
     */
    private function buildFullName(array $record): string
    {
        return trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''));
    }

    /**
     * 确定客户细分
     */
    private function determineCustomerSegment(array $record): string
    {
        $totalSpent = $record['total_spent'] ?? 0;
        $orderCount = $record['order_count'] ?? 0;

        if ($totalSpent > 100000 || $orderCount > 50) {
            return 'VIP';
        } elseif ($totalSpent > 50000 || $orderCount > 20) {
            return 'Premium';
        } elseif ($totalSpent > 10000 || $orderCount > 5) {
            return 'Regular';
        } else {
            return 'New';
        }
    }

    /**
     * 计算忠诚度等级
     */
    private function calculateLoyaltyTier(array $record): string
    {
        $points = $record['loyalty_points'] ?? 0;

        if ($points >= 10000) {
            return 'Platinum';
        } elseif ($points >= 5000) {
            return 'Gold';
        } elseif ($points >= 2000) {
            return 'Silver';
        } elseif ($points >= 500) {
            return 'Bronze';
        } else {
            return 'Basic';
        }
    }

    /**
     * 生成日期维度数据
     */
    public function generateDateDimensions(\DateTime $startDate, \DateTime $endDate): array
    {
        $dimensions = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dateKey = (int)$current->format('Ymd');
            $dimensions[] = [
                'date_key' => $dateKey,
                'full_date' => $current->format('Y-m-d'),
                'year' => (int)$current->format('Y'),
                'quarter' => (int)ceil($current->format('n') / 3),
                'month' => (int)$current->format('n'),
                'week' => (int)$current->format('W'),
                'day_of_month' => (int)$current->format('j'),
                'day_of_week' => (int)$current->format('N'),
                'day_of_year' => (int)$current->format('z') + 1,
                'is_weekend' => in_array($current->format('N'), ['6', '7']),
                'is_holiday' => $this->isHoliday($current),
                'season' => $this->getSeason($current),
                'month_name' => $current->format('F'),
                'weekday_name' => $current->format('l')
            ];

            $current->modify('+1 day');
        }

        return $dimensions;
    }

    /**
     * 判断是否为节假日
     */
    private function isHoliday(\DateTime $date): bool
    {
        // 简化的节假日判断，实际应用中应该从节假日表获取
        $holidays = [
            '01-01', // 元旦
            '05-01', // 劳动节
            '10-01', // 国庆节
            '12-25'  // 圣诞节
        ];

        return in_array($date->format('m-d'), $holidays);
    }

    /**
     * 获取季节
     */
    private function getSeason(\DateTime $date): string
    {
        $month = (int)$date->format('n');

        return match (true) {
            $month >= 3 && $month <= 5 => 'Spring',
            $month >= 6 && $month <= 8 => 'Summer',
            $month >= 9 && $month <= 11 => 'Autumn',
            default => 'Winter'
        };
    }
}