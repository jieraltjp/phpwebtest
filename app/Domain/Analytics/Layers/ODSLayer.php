<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Layers;

use App\Domain\Analytics\Abstractions\DataWarehouseLayer;

/**
 * ODS (Operational Data Store) 操作数据存储层
 * 原始数据存储，保持与源系统相同的结构
 */
class ODSLayer extends DataWarehouseLayer
{
    public function __construct()
    {
        parent::__construct('ODS');
    }

    /**
     * 定义 ODS 表结构
     */
    protected function defineSchema(): array
    {
        return [
            'ods_orders' => [
                'id' => 'BIGINT PRIMARY KEY',
                'order_number' => 'VARCHAR(50) UNIQUE',
                'user_id' => 'BIGINT',
                'total_amount' => 'DECIMAL(10,2)',
                'currency' => 'VARCHAR(3)',
                'status' => 'VARCHAR(20)',
                'order_date' => 'TIMESTAMP',
                'created_at' => 'TIMESTAMP',
                'updated_at' => 'TIMESTAMP',
                'raw_data' => 'JSON'
            ],
            'ods_order_items' => [
                'id' => 'BIGINT PRIMARY KEY',
                'order_id' => 'BIGINT',
                'product_id' => 'BIGINT',
                'sku' => 'VARCHAR(100)',
                'quantity' => 'INT',
                'unit_price' => 'DECIMAL(10,2)',
                'total_price' => 'DECIMAL(10,2)',
                'currency' => 'VARCHAR(3)',
                'created_at' => 'TIMESTAMP',
                'raw_data' => 'JSON'
            ],
            'ods_products' => [
                'id' => 'BIGINT PRIMARY KEY',
                'sku' => 'VARCHAR(100) UNIQUE',
                'name' => 'VARCHAR(255)',
                'category' => 'VARCHAR(100)',
                'price' => 'DECIMAL(10,2)',
                'currency' => 'VARCHAR(3)',
                'stock_quantity' => 'INT',
                'supplier_id' => 'BIGINT',
                'created_at' => 'TIMESTAMP',
                'updated_at' => 'TIMESTAMP',
                'raw_data' => 'JSON'
            ],
            'ods_users' => [
                'id' => 'BIGINT PRIMARY KEY',
                'username' => 'VARCHAR(100) UNIQUE',
                'email' => 'VARCHAR(255) UNIQUE',
                'first_name' => 'VARCHAR(100)',
                'last_name' => 'VARCHAR(100)',
                'company' => 'VARCHAR(255)',
                'phone' => 'VARCHAR(50)',
                'country' => 'VARCHAR(50)',
                'registration_date' => 'TIMESTAMP',
                'last_login' => 'TIMESTAMP',
                'created_at' => 'TIMESTAMP',
                'updated_at' => 'TIMESTAMP',
                'raw_data' => 'JSON'
            ],
            'ods_inquiries' => [
                'id' => 'BIGINT PRIMARY KEY',
                'inquiry_number' => 'VARCHAR(50) UNIQUE',
                'user_id' => 'BIGINT',
                'subject' => 'VARCHAR(255)',
                'status' => 'VARCHAR(20)',
                'priority' => 'VARCHAR(10)',
                'estimated_value' => 'DECIMAL(10,2)',
                'currency' => 'VARCHAR(3)',
                'inquiry_date' => 'TIMESTAMP',
                'created_at' => 'TIMESTAMP',
                'updated_at' => 'TIMESTAMP',
                'raw_data' => 'JSON'
            ]
        ];
    }

    /**
     * 定义索引
     */
    protected function defineIndexes(): array
    {
        return [
            'ods_orders' => [
                'idx_user_id' => 'user_id',
                'idx_order_date' => 'order_date',
                'idx_status' => 'status',
                'idx_created_at' => 'created_at'
            ],
            'ods_order_items' => [
                'idx_order_id' => 'order_id',
                'idx_product_id' => 'product_id',
                'idx_sku' => 'sku'
            ],
            'ods_products' => [
                'idx_sku' => 'sku',
                'idx_category' => 'category',
                'idx_supplier_id' => 'supplier_id'
            ],
            'ods_users' => [
                'idx_email' => 'email',
                'idx_username' => 'username',
                'idx_registration_date' => 'registration_date',
                'idx_country' => 'country'
            ],
            'ods_inquiries' => [
                'idx_user_id' => 'user_id',
                'idx_inquiry_date' => 'inquiry_date',
                'idx_status' => 'status',
                'idx_priority' => 'priority'
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
            'order_number' => 'required|string',
            'user_id' => 'required|numeric|positive',
            'total_amount' => 'required|numeric|positive',
            'currency' => 'required|string',
            'status' => 'required|string',
            'order_date' => 'required|date',
            'email' => 'required|email',
            'sku' => 'required|string',
            'quantity' => 'required|numeric|positive',
            'unit_price' => 'required|numeric|positive'
        ];
    }

    /**
     * 数据转换逻辑（ODS层基本不转换，保持原始格式）
     */
    public function transform(array $rawData): array
    {
        // ODS层保持原始数据，只做基本格式化
        return array_map(function ($record) {
            return [
                ...$record,
                'created_at' => $record['created_at'] ?? now(),
                'updated_at' => now(),
                'raw_data' => json_encode($record)
            ];
        }, $rawData);
    }

    /**
     * 数据抽取方法
     */
    public function extractFromSource(string $sourceTable, array $filters = []): array
    {
        $query = $this->buildExtractQuery($sourceTable, $filters);
        
        // 这里应该连接到源数据库进行数据抽取
        // 为了演示，返回模拟数据
        return $this->getMockData($sourceTable);
    }

    /**
     * 构建抽取查询
     */
    private function buildExtractQuery(string $sourceTable, array $filters): string
    {
        $baseQuery = "SELECT * FROM {$sourceTable}";
        
        if (!empty($filters)) {
            $whereConditions = [];
            foreach ($filters as $field => $value) {
                $whereConditions[] = "{$field} = '{$value}'";
            }
            $baseQuery .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        return $baseQuery;
    }

    /**
     * 模拟数据（实际应用中应该从源系统获取）
     */
    private function getMockData(string $sourceTable): array
    {
        return match ($sourceTable) {
            'orders' => [
                [
                    'id' => 1,
                    'order_number' => 'ORD-2024-001',
                    'user_id' => 1,
                    'total_amount' => 1250.50,
                    'currency' => 'CNY',
                    'status' => 'completed',
                    'order_date' => '2024-01-15 10:30:00'
                ]
            ],
            default => []
        };
    }
}