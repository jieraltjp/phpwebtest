<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

/**
 * 报表生成服务
 * 提供动态报表生成、多维度分析和导出功能
 */
class ReportGenerationService
{
    private CacheService $cacheService;
    private BusinessAnalyticsService $analyticsService;

    public function __construct(
        CacheService $cacheService,
        BusinessAnalyticsService $analyticsService
    ) {
        $this->cacheService = $cacheService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * 生成动态报表
     */
    public function generateDynamicReport(array $config): array
    {
        $reportId = $config['report_id'] ?? uniqid('report_');
        $reportType = $config['type'] ?? 'standard';
        $timeRange = $config['time_range'] ?? '30d';
        $dimensions = $config['dimensions'] ?? [];
        $metrics = $config['metrics'] ?? [];
        $filters = $config['filters'] ?? [];

        $cacheKey = "report_{$reportId}_" . md5(json_encode($config));

        return $this->cacheService->remember($cacheKey, 1800, function () use ($config) {
            $data = $this->collectReportData($config);
            $processedData = $this->processReportData($data, $config);
            
            return [
                'report_id' => $config['report_id'],
                'type' => $config['type'],
                'generated_at' => now()->toISOString(),
                'data' => $processedData,
                'summary' => $this->generateReportSummary($processedData),
                'charts' => $this->generateChartData($processedData, $config),
                'metadata' => $this->generateReportMetadata($config)
            ];
        });
    }

    /**
     * 生成销售报表
     */
    public function generateSalesReport(array $params = []): array
    {
        $config = [
            'report_id' => 'sales_report_' . date('Ymd_His'),
            'type' => 'sales',
            'time_range' => $params['period'] ?? '30d',
            'dimensions' => ['date', 'region', 'category'],
            'metrics' => ['revenue', 'orders', 'customers', 'avg_order_value'],
            'filters' => $params['filters'] ?? []
        ];

        return $this->generateDynamicReport($config);
    }

    /**
     * 生成客户分析报表
     */
    public function generateCustomerReport(array $params = []): array
    {
        $config = [
            'report_id' => 'customer_report_' . date('Ymd_His'),
            'type' => 'customer',
            'time_range' => $params['period'] ?? '90d',
            'dimensions' => ['segment', 'region', 'acquisition_channel'],
            'metrics' => ['count', 'lifetime_value', 'retention_rate', 'churn_rate'],
            'filters' => $params['filters'] ?? []
        ];

        return $this->generateDynamicReport($config);
    }

    /**
     * 生成库存报表
     */
    public function generateInventoryReport(array $params = []): array
    {
        $config = [
            'report_id' => 'inventory_report_' . date('Ymd_His'),
            'type' => 'inventory',
            'time_range' => 'current',
            'dimensions' => ['warehouse', 'category', 'product'],
            'metrics' => ['stock_level', 'turnover', 'value', 'reorder_point'],
            'filters' => $params['filters'] ?? []
        ];

        return $this->generateDynamicReport($config);
    }

    /**
     * 生成财务报表
     */
    public function generateFinancialReport(array $params = []): array
    {
        $config = [
            'report_id' => 'financial_report_' . date('Ymd_His'),
            'type' => 'financial',
            'time_range' => $params['period'] ?? '12m',
            'dimensions' => ['month', 'category', 'region'],
            'metrics' => ['revenue', 'costs', 'profit', 'margin', 'cash_flow'],
            'filters' => $params['filters'] ?? []
        ];

        return $this->generateDynamicReport($config);
    }

    /**
     * 生成自定义报表
     */
    public function generateCustomReport(array $config): array
    {
        $this->validateCustomReportConfig($config);
        
        $config['report_id'] = $config['report_id'] ?? 'custom_' . date('Ymd_His');
        $config['type'] = 'custom';

        return $this->generateDynamicReport($config);
    }

    /**
     * 导出报表到PDF
     */
    public function exportToPDF(array $reportData): string
    {
        $pdfContent = $this->generatePDFContent($reportData);
        $filename = "report_{$reportData['report_id']}_" . date('Ymd') . '.pdf';
        
        // 保存PDF文件
        $filePath = storage_path("app/reports/{$filename}");
        file_put_contents($filePath, $pdfContent);
        
        return $filePath;
    }

    /**
     * 导出报表到Excel
     */
    public function exportToExcel(array $reportData): string
    {
        $spreadsheet = $this->createExcelSpreadsheet($reportData);
        $filename = "report_{$reportData['report_id']}_" . date('Ymd') . '.xlsx';
        
        $filePath = storage_path("app/reports/{$filename}");
        $spreadsheet->save($filePath);
        
        return $filePath;
    }

    /**
     * 获取报表模板
     */
    public function getReportTemplates(): array
    {
        return [
            'sales_performance' => [
                'name' => '销售绩效报表',
                'description' => '分析销售趋势、区域表现和产品销售情况',
                'type' => 'sales',
                'default_dimensions' => ['date', 'region', 'category'],
                'default_metrics' => ['revenue', 'orders', 'customers', 'avg_order_value'],
                'suggested_time_ranges' => ['7d', '30d', '90d', '12m']
            ],
            'customer_analysis' => [
                'name' => '客户分析报表',
                'description' => 'RFM客户分析、客户细分和留存分析',
                'type' => 'customer',
                'default_dimensions' => ['segment', 'region', 'acquisition_channel'],
                'default_metrics' => ['count', 'lifetime_value', 'retention_rate', 'churn_rate'],
                'suggested_time_ranges' => ['30d', '90d', '12m']
            ],
            'inventory_status' => [
                'name' => '库存状态报表',
                'description' => '库存水平、周转率和优化建议',
                'type' => 'inventory',
                'default_dimensions' => ['warehouse', 'category', 'product'],
                'default_metrics' => ['stock_level', 'turnover', 'value', 'reorder_point'],
                'suggested_time_ranges' => ['current']
            ],
            'financial_summary' => [
                'name' => '财务汇总报表',
                'description' => '收入、成本、利润和现金流分析',
                'type' => 'financial',
                'default_dimensions' => ['month', 'category', 'region'],
                'default_metrics' => ['revenue', 'costs', 'profit', 'margin', 'cash_flow'],
                'suggested_time_ranges' => ['1m', '3m', '6m', '12m']
            ],
            'product_performance' => [
                'name' => '产品绩效报表',
                'description' => '产品销售、盈利能力和市场表现',
                'type' => 'product',
                'default_dimensions' => ['product', 'category', 'brand'],
                'default_metrics' => ['sales', 'revenue', 'profit', 'rating', 'return_rate'],
                'suggested_time_ranges' => ['30d', '90d', '12m']
            ]
        ];
    }

    /**
     * 收集报表数据
     */
    private function collectReportData(array $config): array
    {
        $reportType = $config['type'];
        $timeRange = $config['time_range'];
        $dimensions = $config['dimensions'];
        $metrics = $config['metrics'];
        $filters = $config['filters'];

        return match ($reportType) {
            'sales' => $this->collectSalesData($timeRange, $dimensions, $metrics, $filters),
            'customer' => $this->collectCustomerData($timeRange, $dimensions, $metrics, $filters),
            'inventory' => $this->collectInventoryData($dimensions, $metrics, $filters),
            'financial' => $this->collectFinancialData($timeRange, $dimensions, $metrics, $filters),
            'product' => $this->collectProductData($timeRange, $dimensions, $metrics, $filters),
            'custom' => $this->collectCustomData($config),
            default => throw new Exception("Unsupported report type: {$reportType}")
        };
    }

    /**
     * 收集销售数据
     */
    private function collectSalesData(string $timeRange, array $dimensions, array $metrics, array $filters): array
    {
        $dateRange = $this->parseTimeRange($timeRange);
        
        $query = DB::table('dws_sales_daily')
            ->select($this->buildSelectColumns($dimensions, $metrics))
            ->whereBetween('date_key', [
                (int)$dateRange['start']->format('Ymd'),
                (int)$dateRange['end']->format('Ymd')
            ]);

        $this->applyFilters($query, $filters);
        
        if (!empty($dimensions)) {
            $query->groupBy($dimensions);
        }

        $query->orderBy('date_key');

        return $query->get()->toArray();
    }

    /**
     * 收集客户数据
     */
    private function collectCustomerData(string $timeRange, array $dimensions, array $metrics, array $filters): array
    {
        $query = DB::table('dws_customer_rfm')
            ->select($this->buildSelectColumns($dimensions, $metrics));

        $this->applyFilters($query, $filters);
        
        if (!empty($dimensions)) {
            $query->groupBy($dimensions);
        }

        $query->orderBy('lifetime_value', 'desc');

        return $query->get()->toArray();
    }

    /**
     * 收集库存数据
     */
    private function collectInventoryData(array $dimensions, array $metrics, array $filters): array
    {
        $query = DB::table('dws_inventory_analysis')
            ->select($this->buildSelectColumns($dimensions, $metrics))
            ->where('date_key', (int)date('Ymd'));

        $this->applyFilters($query, $filters);
        
        if (!empty($dimensions)) {
            $query->groupBy($dimensions);
        }

        return $query->get()->toArray();
    }

    /**
     * 收集财务数据
     */
    private function collectFinancialData(string $timeRange, array $dimensions, array $metrics, array $filters): array
    {
        $dateRange = $this->parseTimeRange($timeRange);
        
        $query = DB::table('dws_financial_summary')
            ->select($this->buildSelectColumns($dimensions, $metrics))
            ->whereBetween('period_start', [$dateRange['start'], $dateRange['end']]);

        $this->applyFilters($query, $filters);
        
        if (!empty($dimensions)) {
            $query->groupBy($dimensions);
        }

        return $query->get()->toArray();
    }

    /**
     * 收集产品数据
     */
    private function collectProductData(string $timeRange, array $dimensions, array $metrics, array $filters): array
    {
        $dateRange = $this->parseTimeRange($timeRange);
        
        $query = DB::table('dws_product_performance')
            ->select($this->buildSelectColumns($dimensions, $metrics))
            ->whereBetween('analysis_date', [$dateRange['start'], $dateRange['end']]);

        $this->applyFilters($query, $filters);
        
        if (!empty($dimensions)) {
            $query->groupBy($dimensions);
        }

        return $query->get()->toArray();
    }

    /**
     * 收集自定义数据
     */
    private function collectCustomData(array $config): array
    {
        // 根据自定义配置收集数据
        $dataSource = $config['data_source'] ?? 'dws_sales_daily';
        $dimensions = $config['dimensions'] ?? [];
        $metrics = $config['metrics'] ?? [];
        $filters = $config['filters'] ?? [];

        $query = DB::table($dataSource)
            ->select($this->buildSelectColumns($dimensions, $metrics));

        $this->applyFilters($query, $filters);
        
        if (!empty($dimensions)) {
            $query->groupBy($dimensions);
        }

        return $query->get()->toArray();
    }

    /**
     * 构建选择列
     */
    private function buildSelectColumns(array $dimensions, array $metrics): array
    {
        $columns = [];
        
        // 添加维度列
        foreach ($dimensions as $dimension) {
            $columns[] = $dimension;
        }
        
        // 添加指标列，通常需要聚合函数
        foreach ($metrics as $metric) {
            if (in_array($metric, ['revenue', 'total_revenue', 'sales'])) {
                $columns[] = DB::raw('SUM(' . $metric . ') as ' . $metric);
            } elseif (in_array($metric, ['orders', 'customers', 'quantity'])) {
                $columns[] = DB::raw('SUM(' . $metric . ') as ' . $metric);
            } elseif (in_array($metric, ['avg_order_value', 'avg_price'])) {
                $columns[] = DB::raw('AVG(' . $metric . ') as ' . $metric);
            } elseif (str_contains($metric, 'rate') || str_contains($metric, 'ratio')) {
                $columns[] = DB::raw('AVG(' . $metric . ') as ' . $metric);
            } else {
                $columns[] = $metric;
            }
        }
        
        return $columns;
    }

    /**
     * 应用过滤器
     */
    private function applyFilters($query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * 处理报表数据
     */
    private function processReportData(array $data, array $config): array
    {
        $processedData = [
            'raw_data' => $data,
            'summary_data' => $this->calculateSummaryData($data, $config),
            'comparison_data' => $this->calculateComparisonData($data, $config),
            'trend_data' => $this->calculateTrendData($data, $config),
            'distribution_data' => $this->calculateDistributionData($data, $config)
        ];

        return $processedData;
    }

    /**
     * 计算汇总数据
     */
    private function calculateSummaryData(array $data, array $config): array
    {
        if (empty($data)) {
            return [];
        }

        $metrics = $config['metrics'];
        $summary = [];

        foreach ($metrics as $metric) {
            $values = array_column($data, $metric);
            $summary[$metric] = [
                'total' => array_sum($values),
                'average' => count($values) > 0 ? array_sum($values) / count($values) : 0,
                'min' => count($values) > 0 ? min($values) : 0,
                'max' => count($values) > 0 ? max($values) : 0,
                'count' => count($values)
            ];
        }

        return $summary;
    }

    /**
     * 计算比较数据
     */
    private function calculateComparisonData(array $data, array $config): array
    {
        // 简化实现：比较第一个和最后一个时期的数据
        if (count($data) < 2) {
            return [];
        }

        $first = $data[0];
        $last = end($data);
        $metrics = $config['metrics'];
        $comparison = [];

        foreach ($metrics as $metric) {
            $firstValue = $first[$metric] ?? 0;
            $lastValue = $last[$metric] ?? 0;
            
            $change = $lastValue - $firstValue;
            $changePercent = $firstValue != 0 ? ($change / $firstValue) * 100 : 0;

            $comparison[$metric] = [
                'first_period' => $firstValue,
                'last_period' => $lastValue,
                'change' => $change,
                'change_percent' => round($changePercent, 2)
            ];
        }

        return $comparison;
    }

    /**
     * 计算趋势数据
     */
    private function calculateTrendData(array $data, array $config): array
    {
        if (count($data) < 3) {
            return [];
        }

        $metrics = $config['metrics'];
        $trends = [];

        foreach ($metrics as $metric) {
            $values = array_column($data, $metric);
            $trend = $this->calculateLinearTrend($values);
            
            $trends[$metric] = [
                'slope' => $trend['slope'],
                'direction' => $trend['direction'],
                'strength' => $trend['strength'],
                'r_squared' => $trend['r_squared']
            ];
        }

        return $trends;
    }

    /**
     * 计算线性趋势
     */
    private function calculateLinearTrend(array $values): array
    {
        $n = count($values);
        if ($n < 2) {
            return ['slope' => 0, 'direction' => 'stable', 'strength' => 0, 'r_squared' => 0];
        }

        $x = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($values);
        $sumXY = array_sum(array_map(fn($xi, $yi) => $xi * $yi, $x, $values));
        $sumX2 = array_sum(array_map(fn($xi) => $xi * $xi, $x));
        $sumY2 = array_sum(array_map(fn($yi) => $yi * $yi, $values));

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // 计算R²
        $yMean = $sumY / $n;
        $ssTotal = array_sum(array_map(fn($yi) => pow($yi - $yMean, 2), $values));
        $ssResidual = array_sum(array_map(function($xi, $yi) use ($slope, $intercept) {
            $predicted = $slope * $xi + $intercept;
            return pow($yi - $predicted, 2);
        }, $x, $values));

        $rSquared = $ssTotal > 0 ? 1 - ($ssResidual / $ssTotal) : 0;

        $direction = $slope > 0.01 ? 'up' : ($slope < -0.01 ? 'down' : 'stable');
        $strength = abs($slope);

        return [
            'slope' => $slope,
            'direction' => $direction,
            'strength' => $strength,
            'r_squared' => $rSquared
        ];
    }

    /**
     * 计算分布数据
     */
    private function calculateDistributionData(array $data, array $config): array
    {
        $dimensions = $config['dimensions'];
        $distribution = [];

        foreach ($dimensions as $dimension) {
            $dimensionValues = array_column($data, $dimension);
            $valueCounts = array_count_values($dimensionValues);
            
            $distribution[$dimension] = [
                'unique_values' => count($valueCounts),
                'distribution' => $valueCounts,
                'top_values' => $this->getTopValues($valueCounts, 5)
            ];
        }

        return $distribution;
    }

    /**
     * 获取顶级值
     */
    private function getTopValues(array $valueCounts, int $limit): array
    {
        arsort($valueCounts);
        return array_slice($valueCounts, 0, $limit, true);
    }

    /**
     * 生成报表汇总
     */
    private function generateReportSummary(array $processedData): array
    {
        return [
            'total_records' => count($processedData['raw_data']),
            'key_insights' => $this->extractKeyInsights($processedData),
            'recommendations' => $this->generateRecommendations($processedData),
            'data_quality' => $this->assessDataQuality($processedData['raw_data'])
        ];
    }

    /**
     * 提取关键洞察
     */
    private function extractKeyInsights(array $processedData): array
    {
        $insights = [];

        // 从趋势数据中提取洞察
        if (!empty($processedData['trend_data'])) {
            foreach ($processedData['trend_data'] as $metric => $trend) {
                if ($trend['strength'] > 0.1) {
                    $insights[] = [
                        'type' => 'trend',
                        'metric' => $metric,
                        'description' => "{$metric}呈{$trend['direction']}趋势，强度为" . round($trend['strength'], 3),
                        'confidence' => round($trend['r_squared'], 2)
                    ];
                }
            }
        }

        return $insights;
    }

    /**
     * 生成建议
     */
    private function generateRecommendations(array $processedData): array
    {
        $recommendations = [];

        // 基于比较数据生成建议
        if (!empty($processedData['comparison_data'])) {
            foreach ($processedData['comparison_data'] as $metric => $comparison) {
                if ($comparison['change_percent'] < -10) {
                    $recommendations[] = [
                        'priority' => 'high',
                        'metric' => $metric,
                        'action' => '调查下降原因',
                        'description' => "{$metric}下降了" . abs($comparison['change_percent']) . "%"
                    ];
                }
            }
        }

        return $recommendations;
    }

    /**
     * 评估数据质量
     */
    private function assessDataQuality(array $data): array
    {
        $totalRecords = count($data);
        $nullCount = 0;
        $duplicateCount = 0;

        // 检查空值
        foreach ($data as $record) {
            foreach ($record as $value) {
                if ($value === null || $value === '') {
                    $nullCount++;
                    break;
                }
            }
        }

        // 检查重复记录
        $serializedData = array_map('serialize', $data);
        $uniqueData = array_unique($serializedData);
        $duplicateCount = $totalRecords - count($uniqueData);

        return [
            'total_records' => $totalRecords,
            'null_records' => $nullCount,
            'duplicate_records' => $duplicateCount,
            'completeness_score' => $totalRecords > 0 ? (($totalRecords - $nullCount) / $totalRecords) : 1,
            'uniqueness_score' => $totalRecords > 0 ? (count($uniqueData) / $totalRecords) : 1
        ];
    }

    /**
     * 生成图表数据
     */
    private function generateChartData(array $processedData, array $config): array
    {
        $charts = [];

        // 时间序列图表
        if (in_array('date', $config['dimensions'])) {
            $charts['time_series'] = $this->generateTimeSeriesChart($processedData['raw_data'], $config['metrics']);
        }

        // 柱状图
        if (!empty($config['dimensions']) && count($config['dimensions']) === 1) {
            $dimension = $config['dimensions'][0];
            $charts['bar_chart'] = $this->generateBarChart($processedData['raw_data'], $dimension, $config['metrics']);
        }

        // 饼图
        if (!empty($config['dimensions'])) {
            $dimension = $config['dimensions'][0];
            $charts['pie_chart'] = $this->generatePieChart($processedData['raw_data'], $dimension, $config['metrics'][0] ?? null);
        }

        return $charts;
    }

    /**
     * 生成时间序列图表
     */
    private function generateTimeSeriesChart(array $data, array $metrics): array
    {
        $labels = array_column($data, 'date');
        $datasets = [];

        foreach ($metrics as $metric) {
            $values = array_column($data, $metric);
            $datasets[] = [
                'label' => $metric,
                'data' => $values,
                'borderColor' => $this->getColorForMetric($metric),
                'backgroundColor' => $this->getColorForMetric($metric, 0.2)
            ];
        }

        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => ['beginAtZero' => true]
                ]
            ]
        ];
    }

    /**
     * 生成柱状图
     */
    private function generateBarChart(array $data, string $dimension, array $metrics): array
    {
        $labels = array_column($data, $dimension);
        $datasets = [];

        foreach ($metrics as $metric) {
            $values = array_column($data, $metric);
            $datasets[] = [
                'label' => $metric,
                'data' => $values,
                'backgroundColor' => $this->getColorForMetric($metric, 0.8)
            ];
        }

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => ['beginAtZero' => true]
                ]
            ]
        ];
    }

    /**
     * 生成饼图
     */
    private function generatePieChart(array $data, string $dimension, ?string $metric): array
    {
        $labels = array_column($data, $dimension);
        $values = $metric ? array_column($data, $metric) : array_count_values($labels);

        return [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => array_values($values),
                    'backgroundColor' => array_map(fn($i) => $this->getColorForIndex($i), array_keys($labels))
                ]]
            ],
            'options' => [
                'responsive' => true
            ]
        ];
    }

    /**
     * 获取指标颜色
     */
    private function getColorForMetric(string $metric, float $alpha = 1): string
    {
        $colors = [
            'revenue' => "rgba(54, 162, 235, {$alpha})",
            'orders' => "rgba(255, 99, 132, {$alpha})",
            'customers' => "rgba(255, 205, 86, {$alpha})",
            'profit' => "rgba(75, 192, 192, {$alpha})",
            'default' => "rgba(153, 102, 255, {$alpha})"
        ];

        return $colors[$metric] ?? $colors['default'];
    }

    /**
     * 获取索引颜色
     */
    private function getColorForIndex(int $index): string
    {
        $baseColors = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 205, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)'
        ];

        return $baseColors[$index % count($baseColors)];
    }

    /**
     * 生成报表元数据
     */
    private function generateReportMetadata(array $config): array
    {
        return [
            'report_id' => $config['report_id'],
            'type' => $config['type'],
            'time_range' => $config['time_range'],
            'dimensions' => $config['dimensions'],
            'metrics' => $config['metrics'],
            'filters' => $config['filters'],
            'generated_at' => now()->toISOString(),
            'data_source' => $this->getDataSourceForType($config['type']),
            'cache_duration' => 1800
        ];
    }

    /**
     * 获取类型对应的数据源
     */
    private function getDataSourceForType(string $type): string
    {
        return match ($type) {
            'sales' => 'dws_sales_daily',
            'customer' => 'dws_customer_rfm',
            'inventory' => 'dws_inventory_analysis',
            'financial' => 'dws_financial_summary',
            'product' => 'dws_product_performance',
            default => 'custom'
        };
    }

    /**
     * 解析时间范围
     */
    private function parseTimeRange(string $timeRange): array
    {
        $end = now();
        $start = match ($timeRange) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '6m' => now()->subMonths(6),
            '12m' => now()->subMonths(12),
            'current' => now(),
            default => now()->subDays(30)
        };

        return ['start' => $start, 'end' => $end];
    }

    /**
     * 验证自定义报表配置
     */
    private function validateCustomReportConfig(array $config): void
    {
        $required = ['data_source', 'dimensions', 'metrics'];
        
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
    }

    /**
     * 生成PDF内容
     */
    private function generatePDFContent(array $reportData): string
    {
        // 简化实现：返回HTML内容，实际应用中应使用PDF库
        $html = $this->generateReportHTML($reportData);
        
        // 这里应该使用DOMPDF或类似库将HTML转换为PDF
        // 为了演示，返回HTML内容
        return $html;
    }

    /**
     * 生成Excel电子表格
     */
    private function createExcelSpreadsheet(array $reportData)
    {
        // 简化实现：实际应用中应使用PhpSpreadsheet
        // 返回模拟对象
        return new class {
            public function save(string $filePath): void {
                // 模拟保存
            }
        };
    }

    /**
     * 生成报表HTML
     */
    private function generateReportHTML(array $reportData): string
    {
        $title = "报表 - {$reportData['report_id']}";
        $generatedAt = $reportData['generated_at'];
        
        $html = "<html><head><title>{$title}</title></head><body>";
        $html .= "<h1>{$title}</h1>";
        $html .= "<p>生成时间: {$generatedAt}</p>";
        
        // 添加数据表格
        if (!empty($reportData['data']['raw_data'])) {
            $html .= "<h2>数据详情</h2>";
            $html .= "<table border='1'>";
            
            // 表头
            $headers = array_keys($reportData['data']['raw_data'][0]);
            $html .= "<tr>";
            foreach ($headers as $header) {
                $html .= "<th>{$header}</th>";
            }
            $html .= "</tr>";
            
            // 数据行
            foreach ($reportData['data']['raw_data'] as $row) {
                $html .= "<tr>";
                foreach ($row as $value) {
                    $html .= "<td>{$value}</td>";
                }
                $html .= "</tr>";
            }
            
            $html .= "</table>";
        }
        
        $html .= "</body></html>";
        
        return $html;
    }
}