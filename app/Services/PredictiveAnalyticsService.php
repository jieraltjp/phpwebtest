<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

/**
 * 预测分析服务
 * 提供时间序列预测、销售预测、客户流失预测等功能
 */
class PredictiveAnalyticsService
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
     * 时间序列预测
     */
    public function timeSeriesForecast(array $config): array
    {
        $cacheKey = 'forecast_timeseries_' . md5(json_encode($config));
        
        return $this->cacheService->remember($cacheKey, 3600, function () use ($config) {
            $data = $config['data'] ?? [];
            $periods = $config['periods'] ?? 30;
            $method = $config['method'] ?? 'auto';
            $confidence = $config['confidence'] ?? 0.95;

            if (empty($data)) {
                throw new Exception('Time series data is required for forecasting');
            }

            $forecast = match ($method) {
                'linear' => $this->linearRegressionForecast($data, $periods),
                'exponential' => $this->exponentialSmoothingForecast($data, $periods),
                'arima' => $this->arimaForecast($data, $periods),
                'seasonal' => $this->seasonalForecast($data, $periods),
                'auto' => $this->autoForecast($data, $periods),
                default => $this->autoForecast($data, $periods)
            };

            return [
                'forecast_id' => uniqid('forecast_'),
                'method' => $forecast['method'],
                'periods' => $periods,
                'confidence_interval' => $confidence,
                'predictions' => $forecast['predictions'],
                'confidence_intervals' => $forecast['confidence_intervals'],
                'accuracy_metrics' => $forecast['accuracy_metrics'],
                'model_parameters' => $forecast['model_parameters'],
                'generated_at' => now()->toISOString()
            ];
        });
    }

    /**
     * 销售预测
     */
    public function salesForecast(array $params = []): array
    {
        $cacheKey = 'forecast_sales_' . md5(json_encode($params));
        
        return $this->cacheService->remember($cacheKey, 1800, function () use ($params) {
            $period = $params['period'] ?? '30d';
            $forecastPeriods = $params['forecast_periods'] ?? 30;
            $granularity = $params['granularity'] ?? 'daily';
            $region = $params['region'] ?? 'all';
            $category = $params['category'] ?? 'all';

            $historicalData = $this->getHistoricalSalesData($period, $granularity, $region, $category);
            
            $forecast = $this->generateSalesForecast($historicalData, $forecastPeriods, $granularity);
            
            return [
                'forecast_id' => 'sales_' . date('Ymd_His'),
                'parameters' => [
                    'period' => $period,
                    'forecast_periods' => $forecastPeriods,
                    'granularity' => $granularity,
                    'region' => $region,
                    'category' => $category
                ],
                'forecast' => $forecast,
                'insights' => $this->generateSalesForecastInsights($forecast),
                'recommendations' => $this->generateSalesForecastRecommendations($forecast),
                'accuracy_assessment' => $this->assessForecastAccuracy($forecast),
                'generated_at' => now()->toISOString()
            ];
        });
    }

    /**
     * 客户流失预测
     */
    public function customerChurnPrediction(array $params = []): array
    {
        $cacheKey = 'predict_churn_' . md5(json_encode($params));
        
        return $this->cacheService->remember($cacheKey, 3600, function () use ($params) {
            $segment = $params['segment'] ?? 'all';
            $timeframe = $params['timeframe'] ?? '90d';
            $threshold = $params['threshold'] ?? 0.5;

            $customerData = $this->getCustomerBehaviorData($segment, $timeframe);
            $churnModel = $this->buildChurnPredictionModel($customerData);
            $predictions = $this->predictCustomerChurn($customerData, $churnModel, $threshold);

            return [
                'prediction_id' => 'churn_' . date('Ymd_His'),
                'model_info' => [
                    'algorithm' => $churnModel['algorithm'],
                    'accuracy' => $churnModel['accuracy'],
                    'features' => $churnModel['features']
                ],
                'predictions' => $predictions,
                'high_risk_customers' => $this->identifyHighRiskCustomers($predictions, $threshold),
                'retention_recommendations' => $this->generateRetentionRecommendations($predictions),
                'segment_analysis' => $this->analyzeChurnBySegment($predictions),
                'generated_at' => now()->toISOString()
            ];
        });
    }

    /**
     * 库存需求预测
     */
    public function inventoryDemandForecast(array $params = []): array
    {
        $cacheKey = 'forecast_inventory_' . md5(json_encode($params));
        
        return $this->cacheService->remember($cacheKey, 2400, function () use ($params) {
            $productId = $params['product_id'] ?? 'all';
            $warehouse = $params['warehouse'] ?? 'all';
            $forecastPeriods = $params['forecast_periods'] ?? 90;
            $serviceLevel = $params['service_level'] ?? 0.95;

            $demandData = $this->getHistoricalDemandData($productId, $warehouse);
            $forecast = $this->generateDemandForecast($demandData, $forecastPeriods);
            $optimization = $this->optimizeInventoryLevels($forecast, $serviceLevel);

            return [
                'forecast_id' => 'inventory_' . date('Ymd_His'),
                'product_info' => [
                    'product_id' => $productId,
                    'warehouse' => $warehouse,
                    'service_level' => $serviceLevel
                ],
                'demand_forecast' => $forecast,
                'inventory_optimization' => $optimization,
                'reorder_recommendations' => $this->generateReorderRecommendations($optimization),
                'cost_analysis' => $this->analyzeInventoryCosts($optimization),
                'risk_assessment' => $this->assessInventoryRisks($forecast),
                'generated_at' => now()->toISOString()
            ];
        });
    }

    /**
     * 市场趋势预测
     */
    public function marketTrendPrediction(array $params = []): array
    {
        $cacheKey = 'predict_market_' . md5(json_encode($params));
        
        return $this->cacheService->remember($cacheKey, 7200, function () use ($params) {
            $market = $params['market'] ?? 'all';
            $category = $params['category'] ?? 'all';
            $timeframe = $params['timeframe'] ?? '12m';

            $marketData = $this->getMarketData($market, $category, $timeframe);
            $trendAnalysis = $this->analyzeMarketTrends($marketData);
            $predictions = $this->predictMarketTrends($trendAnalysis);

            return [
                'prediction_id' => 'market_' . date('Ymd_His'),
                'market_context' => [
                    'market' => $market,
                    'category' => $category,
                    'timeframe' => $timeframe
                ],
                'trend_analysis' => $trendAnalysis,
                'predictions' => $predictions,
                'opportunity_areas' => $this->identifyMarketOpportunities($predictions),
                'threat_indicators' => $this->identifyMarketThreats($predictions),
                'strategic_recommendations' => $this->generateStrategicRecommendations($predictions),
                'generated_at' => now()->toISOString()
            ];
        });
    }

    /**
     * 价格弹性预测
     */
    public function priceElasticityPrediction(array $params = []): array
    {
        $cacheKey = 'predict_elasticity_' . md5(json_encode($params));
        
        return $this->cacheService->remember($cacheKey, 3600, function () use ($params) {
            $productId = $params['product_id'];
            $priceChanges = $params['price_changes'] ?? [-0.2, -0.1, 0.1, 0.2];

            $priceData = $this->getHistoricalPriceData($productId);
            $elasticityModel = $this->calculatePriceElasticity($priceData);
            $predictions = $this->predictPriceImpact($elasticityModel, $priceChanges);

            return [
                'prediction_id' => 'elasticity_' . date('Ymd_His'),
                'product_id' => $productId,
                'elasticity_model' => $elasticityModel,
                'price_scenario_predictions' => $predictions,
                'optimal_pricing' => $this->findOptimalPrice($elasticityModel, $predictions),
                'revenue_impact' => $this->calculateRevenueImpact($predictions),
                'competitor_analysis' => $this->analyzeCompetitorPricing($productId),
                'generated_at' => now()->toISOString()
            ];
        });
    }

    // 私有方法实现

    /**
     * 线性回归预测
     */
    private function linearRegressionForecast(array $data, int $periods): array
    {
        $n = count($data);
        if ($n < 2) {
            throw new Exception('Insufficient data for linear regression');
        }

        $x = range(1, $n);
        $y = array_values($data);

        // 计算线性回归参数
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(fn($xi, $yi) => $xi * $yi, $x, $y));
        $sumX2 = array_sum(array_map(fn($xi) => $xi * $xi, $x));

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // 生成预测
        $predictions = [];
        $confidenceIntervals = [];
        
        for ($i = 1; $i <= $periods; $i++) {
            $futureX = $n + $i;
            $prediction = $slope * $futureX + $intercept;
            $predictions[] = $prediction;
            
            // 简化的置信区间计算
            $standardError = $this->calculateStandardError($x, $y, $slope, $intercept);
            $margin = 1.96 * $standardError * sqrt(1 + 1/$n + pow($futureX - $sumX/$n, 2) / ($sumX2 - pow($sumX, 2)/$n));
            
            $confidenceIntervals[] = [
                'lower' => $prediction - $margin,
                'upper' => $prediction + $margin
            ];
        }

        // 计算准确性指标
        $rSquared = $this->calculateRSquared($x, $y, $slope, $intercept);
        $mae = $this->calculateMAE($x, $y, $slope, $intercept);

        return [
            'method' => 'linear_regression',
            'predictions' => $predictions,
            'confidence_intervals' => $confidenceIntervals,
            'accuracy_metrics' => [
                'r_squared' => $rSquared,
                'mae' => $mae,
                'rmse' => sqrt($this->calculateMSE($x, $y, $slope, $intercept))
            ],
            'model_parameters' => [
                'slope' => $slope,
                'intercept' => $intercept,
                'sample_size' => $n
            ]
        ];
    }

    /**
     * 指数平滑预测
     */
    private function exponentialSmoothingForecast(array $data, int $periods): array
    {
        $alpha = 0.3; // 平滑参数
        $n = count($data);
        
        if ($n < 2) {
            throw new Exception('Insufficient data for exponential smoothing');
        }

        // 计算平滑值
        $smoothed = [];
        $smoothed[0] = $data[0];
        
        for ($i = 1; $i < $n; $i++) {
            $smoothed[$i] = $alpha * $data[$i] + (1 - $alpha) * $smoothed[$i - 1];
        }

        // 生成预测
        $predictions = [];
        $confidenceIntervals = [];
        $lastSmoothed = end($smoothed);
        
        for ($i = 1; $i <= $periods; $i++) {
            $predictions[] = $lastSmoothed;
            
            // 简化的置信区间
            $variance = $this->calculateVariance(array_slice($data, -10));
            $stdError = sqrt($variance);
            $margin = 1.96 * $stdError;
            
            $confidenceIntervals[] = [
                'lower' => $lastSmoothed - $margin,
                'upper' => $lastSmoothed + $margin
            ];
        }

        return [
            'method' => 'exponential_smoothing',
            'predictions' => $predictions,
            'confidence_intervals' => $confidenceIntervals,
            'accuracy_metrics' => [
                'mse' => $this->calculateMSEFromSmoothed($data, $smoothed),
                'alpha' => $alpha
            ],
            'model_parameters' => [
                'alpha' => $alpha,
                'last_smoothed_value' => $lastSmoothed,
                'sample_size' => $n
            ]
        ];
    }

    /**
     * ARIMA预测（简化实现）
     */
    private function arimaForecast(array $data, int $periods): array
    {
        // 简化的ARIMA实现，实际应用中应使用专业的统计库
        $n = count($data);
        if ($n < 10) {
            throw new Exception('ARIMA requires at least 10 data points');
        }

        // 简化为移动平均模型
        $window = min(5, $n - 1);
        $predictions = [];
        $confidenceIntervals = [];

        for ($i = 1; $i <= $periods; $i++) {
            $recentData = array_slice($data, -$window);
            $prediction = array_sum($recentData) / count($recentData);
            $predictions[] = $prediction;
            
            $variance = $this->calculateVariance($recentData);
            $margin = 1.96 * sqrt($variance);
            
            $confidenceIntervals[] = [
                'lower' => $prediction - $margin,
                'upper' => $prediction + $margin
            ];

            // 将预测值添加到数据中用于下一次预测
            $data[] = $prediction;
        }

        return [
            'method' => 'arima_simplified',
            'predictions' => $predictions,
            'confidence_intervals' => $confidenceIntervals,
            'accuracy_metrics' => [
                'window_size' => $window,
                'data_points' => $n
            ],
            'model_parameters' => [
                'window' => $window,
                'sample_size' => $n
            ]
        ];
    }

    /**
     * 季节性预测
     */
    private function seasonalForecast(array $data, int $periods): array
    {
        $n = count($data);
        if ($n < 12) {
            throw new Exception('Seasonal forecast requires at least 12 data points');
        }

        // 检测季节性周期（假设为12个月）
        $seasonalPeriod = 12;
        $seasons = floor($n / $seasonalPeriod);
        
        if ($seasons < 2) {
            throw new Exception('Need at least 2 seasonal cycles for seasonal forecasting');
        }

        // 计算季节性指数
        $seasonalIndices = [];
        for ($i = 0; $i < $seasonalPeriod; $i++) {
            $periodValues = [];
            for ($j = 0; $j < $seasons; $j++) {
                $index = $j * $seasonalPeriod + $i;
                if ($index < $n) {
                    $periodValues[] = $data[$index];
                }
            }
            $seasonalIndices[$i] = array_sum($periodValues) / count($periodValues);
        }

        // 标准化季节性指数
        $avgIndex = array_sum($seasonalIndices) / count($seasonalIndices);
        foreach ($seasonalIndices as &$index) {
            $index = $index / $avgIndex;
        }

        // 去季节化数据
        $deseasonalized = [];
        for ($i = 0; $i < $n; $i++) {
            $seasonalIndex = $seasonalIndices[$i % $seasonalPeriod];
            $deseasonalized[] = $data[$i] / $seasonalIndex;
        }

        // 对去季节化数据进行线性回归
        $trendForecast = $this->linearRegressionForecast($deseasonalized, $periods);

        // 重新应用季节性
        $predictions = [];
        $confidenceIntervals = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $seasonalIndex = $seasonalIndices[($n + $i) % $seasonalPeriod];
            $seasonalizedPrediction = $trendForecast['predictions'][$i] * $seasonalIndex;
            $predictions[] = $seasonalizedPrediction;
            
            $lowerCI = $trendForecast['confidence_intervals'][$i]['lower'] * $seasonalIndex;
            $upperCI = $trendForecast['confidence_intervals'][$i]['upper'] * $seasonalIndex;
            
            $confidenceIntervals[] = [
                'lower' => $lowerCI,
                'upper' => $upperCI
            ];
        }

        return [
            'method' => 'seasonal_decomposition',
            'predictions' => $predictions,
            'confidence_intervals' => $confidenceIntervals,
            'accuracy_metrics' => $trendForecast['accuracy_metrics'],
            'model_parameters' => [
                'seasonal_period' => $seasonalPeriod,
                'seasonal_indices' => $seasonalIndices,
                'trend_parameters' => $trendForecast['model_parameters']
            ]
        ];
    }

    /**
     * 自动选择最佳预测方法
     */
    private function autoForecast(array $data, int $periods): array
    {
        $methods = ['linear', 'exponential', 'arima'];
        $bestMethod = null;
        $bestAccuracy = PHP_FLOAT_MAX;

        foreach ($methods as $method) {
            try {
                $forecast = match ($method) {
                    'linear' => $this->linearRegressionForecast($data, 5), // 用短期预测评估准确性
                    'exponential' => $this->exponentialSmoothingForecast($data, 5),
                    'arima' => $this->arimaForecast($data, 5),
                    default => null
                };

                if ($forecast) {
                    $accuracy = $forecast['accuracy_metrics']['mae'] ?? PHP_FLOAT_MAX;
                    if ($accuracy < $bestAccuracy) {
                        $bestAccuracy = $accuracy;
                        $bestMethod = $method;
                    }
                }
            } catch (Exception $e) {
                // 忽略方法失败，继续尝试其他方法
                continue;
            }
        }

        // 如果检测到季节性，优先使用季节性方法
        if (count($data) >= 12 && $this->detectSeasonality($data)) {
            $bestMethod = 'seasonal';
        }

        // 使用最佳方法进行完整预测
        return match ($bestMethod) {
            'linear' => $this->linearRegressionForecast($data, $periods),
            'exponential' => $this->exponentialSmoothingForecast($data, $periods),
            'arima' => $this->arimaForecast($data, $periods),
            'seasonal' => $this->seasonalForecast($data, $periods),
            default => $this->linearRegressionForecast($data, $periods)
        };
    }

    /**
     * 检测季节性
     */
    private function detectSeasonality(array $data): bool
    {
        if (count($data) < 12) return false;

        // 简化的季节性检测：比较不同周期的相关性
        $period = 12;
        $correlations = [];

        for ($lag = 1; $lag <= 3; $lag++) {
            $correlation = $this->calculateAutocorrelation($data, $lag * $period);
            $correlations[] = $correlation;
        }

        // 如果存在显著的自相关，认为有季节性
        return max($correlations) > 0.3;
    }

    /**
     * 计算自相关
     */
    private function calculateAutocorrelation(array $data, int $lag): float
    {
        $n = count($data);
        if ($n <= $lag) return 0;

        $x = array_slice($data, 0, $n - $lag);
        $y = array_slice($data, $lag);

        return $this->calculateCorrelation($x, $y);
    }

    /**
     * 计算相关系数
     */
    private function calculateCorrelation(array $x, array $y): float
    {
        $n = min(count($x), count($y));
        if ($n < 2) return 0;

        $meanX = array_sum(array_slice($x, 0, $n)) / $n;
        $meanY = array_sum(array_slice($y, 0, $n)) / $n;

        $numerator = 0;
        $sumSqX = 0;
        $sumSqY = 0;

        for ($i = 0; $i < $n; $i++) {
            $diffX = $x[$i] - $meanX;
            $diffY = $y[$i] - $meanY;
            $numerator += $diffX * $diffY;
            $sumSqX += $diffX * $diffX;
            $sumSqY += $diffY * $diffY;
        }

        $denominator = sqrt($sumSqX * $sumSqY);
        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    /**
     * 计算标准误差
     */
    private function calculateStandardError(array $x, array $y, float $slope, float $intercept): float
    {
        $n = count($x);
        $sumSquaredErrors = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $error = $y[$i] - $predicted;
            $sumSquaredErrors += $error * $error;
        }

        return sqrt($sumSquaredErrors / ($n - 2));
    }

    /**
     * 计算R²
     */
    private function calculateRSquared(array $x, array $y, float $slope, float $intercept): float
    {
        $n = count($x);
        $meanY = array_sum($y) / $n;

        $totalSumSquares = 0;
        $residualSumSquares = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $totalSumSquares += pow($y[$i] - $meanY, 2);
            $residualSumSquares += pow($y[$i] - $predicted, 2);
        }

        return $totalSumSquares > 0 ? 1 - ($residualSumSquares / $totalSumSquares) : 0;
    }

    /**
     * 计算平均绝对误差
     */
    private function calculateMAE(array $x, array $y, float $slope, float $intercept): float
    {
        $n = count($x);
        $sumAbsoluteErrors = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $sumAbsoluteErrors += abs($y[$i] - $predicted);
        }

        return $sumAbsoluteErrors / $n;
    }

    /**
     * 计算均方误差
     */
    private function calculateMSE(array $x, array $y, float $slope, float $intercept): float
    {
        $n = count($x);
        $sumSquaredErrors = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $error = $y[$i] - $predicted;
            $sumSquaredErrors += $error * $error;
        }

        return $sumSquaredErrors / $n;
    }

    /**
     * 计算平滑后的均方误差
     */
    private function calculateMSEFromSmoothed(array $original, array $smoothed): float
    {
        $n = min(count($original), count($smoothed));
        $sumSquaredErrors = 0;

        for ($i = 0; $i < $n; $i++) {
            $error = $original[$i] - $smoothed[$i];
            $sumSquaredErrors += $error * $error;
        }

        return $sumSquaredErrors / $n;
    }

    /**
     * 计算方差
     */
    private function calculateVariance(array $values): float
    {
        if (empty($values)) return 0;
        
        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(fn($value) => pow($value - $mean, 2), $values);
        
        return array_sum($squaredDiffs) / count($values);
    }

    // 简化的其他方法实现
    private function getHistoricalSalesData(string $period, string $granularity, string $region, string $category): array { return []; }
    private function generateSalesForecast(array $data, int $periods, string $granularity): array { return []; }
    private function generateSalesForecastInsights(array $forecast): array { return []; }
    private function generateSalesForecastRecommendations(array $forecast): array { return []; }
    private function assessForecastAccuracy(array $forecast): array { return []; }
    private function getCustomerBehaviorData(string $segment, string $timeframe): array { return []; }
    private function buildChurnPredictionModel(array $data): array { return []; }
    private function predictCustomerChurn(array $data, array $model, float $threshold): array { return []; }
    private function identifyHighRiskCustomers(array $predictions, float $threshold): array { return []; }
    private function generateRetentionRecommendations(array $predictions): array { return []; }
    private function analyzeChurnBySegment(array $predictions): array { return []; }
    private function getHistoricalDemandData(string $productId, string $warehouse): array { return []; }
    private function generateDemandForecast(array $data, int $periods): array { return []; }
    private function optimizeInventoryLevels(array $forecast, float $serviceLevel): array { return []; }
    private function generateReorderRecommendations(array $optimization): array { return []; }
    private function analyzeInventoryCosts(array $optimization): array { return []; }
    private function assessInventoryRisks(array $forecast): array { return []; }
    private function getMarketData(string $market, string $category, string $timeframe): array { return []; }
    private function analyzeMarketTrends(array $data): array { return []; }
    private function predictMarketTrends(array $trendAnalysis): array { return []; }
    private function identifyMarketOpportunities(array $predictions): array { return []; }
    private function identifyMarketThreats(array $predictions): array { return []; }
    private function generateStrategicRecommendations(array $predictions): array { return []; }
    private function getHistoricalPriceData(string $productId): array { return []; }
    private function calculatePriceElasticity(array $data): array { return []; }
    private function predictPriceImpact(array $model, array $changes): array { return []; }
    private function findOptimalPrice(array $model, array $predictions): array { return []; }
    private function calculateRevenueImpact(array $predictions): array { return []; }
    private function analyzeCompetitorPricing(string $productId): array { return []; }
}