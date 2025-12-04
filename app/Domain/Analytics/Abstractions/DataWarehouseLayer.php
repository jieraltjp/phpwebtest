<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Abstractions;

/**
 * 数据仓库层级抽象类
 * 定义 ODS、DWD、DWS、ADS 四层架构的基础接口
 */
abstract class DataWarehouseLayer
{
    protected string $layerName;
    protected array $schema;
    protected array $indexes;

    public function __construct(string $layerName)
    {
        $this->layerName = $layerName;
        $this->schema = $this->defineSchema();
        $this->indexes = $this->defineIndexes();
    }

    /**
     * 定义表结构
     */
    abstract protected function defineSchema(): array;

    /**
     * 定义索引
     */
    abstract protected function defineIndexes(): array;

    /**
     * 获取层级名称
     */
    public function getLayerName(): string
    {
        return $this->layerName;
    }

    /**
     * 获取表结构
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * 获取索引定义
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * 数据验证规则
     */
    abstract public function getValidationRules(): array;

    /**
     * 数据转换逻辑
     */
    abstract public function transform(array $rawData): array;

    /**
     * 数据质量检查
     */
    public function validateDataQuality(array $data): array
    {
        $issues = [];
        $rules = $this->getValidationRules();

        foreach ($data as $record) {
            foreach ($rules as $field => $rule) {
                if (!$this->validateField($record[$field] ?? null, $rule)) {
                    $issues[] = [
                        'record_id' => $record['id'] ?? 'unknown',
                        'field' => $field,
                        'value' => $record[$field] ?? null,
                        'rule' => $rule,
                        'message' => "Field {$field} validation failed"
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * 字段验证
     */
    private function validateField($value, string $rule): bool
    {
        return match ($rule) {
            'required' => !empty($value),
            'numeric' => is_numeric($value),
            'string' => is_string($value),
            'date' => strtotime($value) !== false,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'positive' => is_numeric($value) && $value > 0,
            default => true
        };
    }
}