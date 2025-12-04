<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 分析系统配置
    |--------------------------------------------------------------------------
    |
    | 高级分析系统的配置选项，包括性能优化、缓存策略和资源限制
    |
    */

    // 数据仓库配置
    'data_warehouse' => [
        'layers' => [
            'ods' => [
                'enabled' => true,
                'retention_days' => 90,
                'batch_size' => 1000,
                'connection' => 'mysql'
            ],
            'dwd' => [
                'enabled' => true,
                'retention_days' => 365,
                'batch_size' => 500,
                'connection' => 'mysql'
            ],
            'dws' => [
                'enabled' => true,
                'retention_days' => 730,
                'batch_size' => 200,
                'connection' => 'mysql'
            ],
            'ads' => [
                'enabled' => true,
                'retention_days' => 1095,
                'batch_size' => 100,
                'connection' => 'mysql'
            ]
        ],
        'etl' => [
            'max_execution_time' => 3600, // 1小时
            'memory_limit' => '2G',
            'parallel_processes' => 4,
            'error_threshold' => 0.05, // 5%错误率阈值
            'retry_attempts' => 3,
            'retry_delay' => 30 // 秒
        ]
    ],

    // 缓存配置
    'cache' => [
        'default_ttl' => 3600, // 1小时
        'analytics_ttl' => [
            'sales_trends' => 1800, // 30分钟
            'customer_value' => 3600, // 1小时
            'inventory_optimization' => 900, // 15分钟
            'financials' => 7200, // 2小时
            'product_performance' => 2700, // 45分钟
            'market_analysis' => 7200, // 2小时
            'dashboards' => 300, // 5分钟
            'reports' => 1800, // 30分钟
            'predictions' => 86400 // 24小时
        ],
        'prefix' => 'analytics_',
        'tags' => [
            'sales',
            'customer',
            'inventory',
            'financial',
            'product',
            'market'
        ],
        'max_keys' => 10000,
        'memory_limit' => '512M'
    ],

    // 性能配置
    'performance' => [
        'query_timeout' => 30, // 秒
        'max_result_rows' => 100000,
        'memory_limit' => '1G',
        'cpu_limit' => 80, // 百分比
        'concurrent_requests' => 50,
        'slow_query_threshold' => 1000, // 毫秒
        'enable_profiling' => env('APP_DEBUG', false),
        'optimize_large_queries' => true,
        'use_index_hints' => true
    ],

    // 预测分析配置
    'prediction' => [
        'models' => [
            'time_series' => [
                'enabled' => true,
                'default_method' => 'auto',
                'max_data_points' => 10000,
                'min_data_points' => 10,
                'confidence_level' => 0.95,
                'forecast_horizon' => 365 // 最大预测天数
            ],
            'churn' => [
                'enabled' => true,
                'algorithm' => 'random_forest',
                'features' => [
                    'recency',
                    'frequency',
                    'monetary',
                    'demographics',
                    'behavior'
                ],
                'retraining_interval' => 7, // 天
                'min_samples' => 100
            ],
            'inventory' => [
                'enabled' => true,
                'service_level' => 0.95,
                'lead_time_days' => 30,
                'demand_variability' => 0.2,
                'holding_cost_rate' => 0.25
            ]
        ],
        'cache_predictions' => true,
        'prediction_ttl' => 86400, // 24小时
        'max_concurrent_predictions' => 5
    ],

    // 报表配置
    'reports' => [
        'max_report_size' => 100, // MB
        'export_formats' => ['pdf', 'excel', 'csv'],
        'template_cache_ttl' => 86400, // 24小时
        'async_generation' => true,
        'max_concurrent_reports' => 10,
        'cleanup_interval' => 24, // 小时
        'retention_days' => 30,
        'compression' => true
    ],

    // 可视化配置
    'visualization' => {
        'chart_cache_ttl' => 300, // 5分钟
        'max_chart_points' => 1000,
        'real_time_interval' => 5, // 秒
        'websocket_enabled' => true,
        'dashboard_refresh_interval' => 60, // 秒
        'max_concurrent_dashboards' => 100,
        'image_export_quality' => 90,
        'default_chart_theme' => 'light'
    ],

    // 数据质量配置
    'data_quality' => [
        'enabled' => true,
        'validation_rules' => [
            'required_fields' => ['id', 'created_at', 'updated_at'],
            'numeric_ranges' => [
                'order_amount' => ['min' => 0, 'max' => 1000000],
                'quantity' => ['min' => 1, 'max' => 10000],
                'price' => ['min' => 0.01, 'max' => 100000]
            ],
            'date_formats' => ['Y-m-d H:i:s', 'Y-m-d'],
            'email_validation' => true,
            'phone_validation' => false
        ],
        'anomaly_detection' => [
            'enabled' => true,
            'method' => 'statistical',
            'threshold' => 3, // 标准差倍数
            'min_samples' => 30
        ],
        'duplicate_detection' => [
            'enabled' => true,
            'fields' => ['order_number', 'email'],
            'tolerance' => 300 // 秒
        ]
    ],

    // 监控配置
    'monitoring' => [
        'enabled' => true,
        'metrics' => [
            'query_performance',
            'cache_hit_rate',
            'memory_usage',
            'cpu_usage',
            'error_rate',
            'response_time'
        ],
        'alerts' => [
            'slow_query_threshold' => 5000, // 毫秒
            'error_rate_threshold' => 0.05, // 5%
            'memory_usage_threshold' => 0.8, // 80%
            'cpu_usage_threshold' => 0.9, // 90%
            'disk_usage_threshold' => 0.85 // 85%
        ],
        'logging' => [
            'level' => 'info',
            'max_files' => 30,
            'max_size' => '100M',
            'format' => 'json'
        ]
    ],

    // 安全配置
    'security' => [
        'rate_limiting' => [
            'enabled' => true,
            'requests_per_minute' => 60,
            'burst_limit' => 100,
            'whitelist' => [],
            'blacklist' => []
        ],
        'access_control' => [
            'role_based_access' => true,
            'ip_whitelist' => [],
            'session_timeout' => 3600, // 秒
            'max_concurrent_sessions' => 5
        ],
        'data_encryption' => [
            'enabled' => false,
            'sensitive_fields' => ['email', 'phone', 'address'],
            'algorithm' => 'AES-256-GCM'
        ]
    ],

    // 集成配置
    'integrations' => [
        'external_apis' => [
            'payment_gateway' => [
                'enabled' => false,
                'timeout' => 30,
                'retry_attempts' => 3
            ],
            'shipping_api' => [
                'enabled' => false,
                'timeout' => 15,
                'retry_attempts' => 2
            ]
        ],
        'webhooks' => [
            'enabled' => true,
            'max_payload_size' => '1M',
            'timeout' => 30,
            'retry_attempts' => 3
        ],
        'message_queue' => [
            'enabled' => true,
            'queue_name' => 'analytics',
            'max_retries' => 5,
            'delay_seconds' => 60
        ]
    ],

    // 开发和调试配置
    'development' => [
        'debug_mode' => env('APP_DEBUG', false),
        'query_logging' => env('APP_DEBUG', false),
        'performance_profiling' => env('APP_DEBUG', false),
        'mock_data' => env('ANALYTICS_MOCK_DATA', false),
        'test_mode' => env('APP_ENV') === 'testing',
        'sandbox_mode' => false
    ],

    // 扩展配置
    'extensions' => [
        'machine_learning' => [
            'enabled' => false,
            'python_integration' => false,
            'model_path' => storage_path('app/models')
        ],
        'advanced_analytics' => [
            'enabled' => true,
            'statistical_methods' => [
                'regression',
                'correlation',
                'clustering',
                'classification'
            ]
        ],
        'real_time_processing' => [
            'enabled' => true,
            'stream_processing' => false,
            'event_sourcing' => false
        ]
    ]
];