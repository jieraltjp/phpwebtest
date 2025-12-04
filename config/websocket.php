<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebSocket 服务器配置
    |--------------------------------------------------------------------------
    |
    | WebSocket服务器的基本配置，包括监听地址、端口、工作进程等
    |
    */

    'server' => [
        // 监听地址
        'host' => env('WEBSOCKET_HOST', '0.0.0.0'),
        
        // 监听端口
        'port' => env('WEBSOCKET_PORT', 8080),
        
        // 工作进程数量
        'workers' => env('WEBSOCKET_WORKERS', 4),
        
        // 最大连接数
        'max_connections' => env('WEBSOCKET_MAX_CONNECTIONS', 5000),
        
        // 内存限制
        'memory_limit' => env('WEBSOCKET_MEMORY_LIMIT', '512M'),
        
        // 连接超时时间（秒）
        'connection_timeout' => env('WEBSOCKET_CONNECTION_TIMEOUT', 60),
        
        // 心跳间隔（秒）
        'heartbeat_interval' => env('WEBSOCKET_HEARTBEAT_INTERVAL', 30),
        
        // 心跳超时（秒）
        'heartbeat_timeout' => env('WEBSOCKET_HEARTBEAT_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | 认证配置
    |--------------------------------------------------------------------------
    |
    | WebSocket连接的认证相关配置
    |
    */

    'auth' => [
        // 是否启用认证
        'enabled' => env('WEBSOCKET_AUTH_ENABLED', true),
        
        // 认证令牌有效期（分钟）
        'token_ttl' => env('WEBSOCKET_TOKEN_TTL', 60),
        
        // 认证中间件
        'middleware' => [
            'jwt.auth',
            'throttle:60,1'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 频道配置
    |--------------------------------------------------------------------------
    |
    | WebSocket频道的访问控制和权限配置
    |
    */

    'channels' => [
        // 公共频道（无需认证）
        'public' => [
            'system_announcements',
            'feature_announcements',
            'public_chat'
        ],
        
        // 私有频道（需要认证）
        'private' => [
            'user.{userId}',
            'admin_orders',
            'admin_stats',
            'inventory',
            'inventory_alerts',
            'admin_alerts',
            'sales_alerts',
            'sales_inquiries',
            'customer_service',
            'admin_system'
        ],
        
        // 在线频道（需要认证，显示在线用户）
        'presence' => [
            'presence_general',
            'presence_support',
            'presence_sales'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 消息配置
    |--------------------------------------------------------------------------
    |
    | 消息处理、持久化和限制配置
    |
    */

    'messages' => [
        // 每秒每连接最大消息数
        'rate_limit' => env('WEBSOCKET_MESSAGE_RATE_LIMIT', 100),
        
        // 单个消息最大大小（字节）
        'max_size' => env('WEBSOCKET_MAX_MESSAGE_SIZE', 65536),
        
        // 消息历史保留天数
        'history_retention_days' => env('WEBSOCKET_HISTORY_RETENTION_DAYS', 7),
        
        // 每频道最大历史消息数
        'max_history_per_channel' => env('WEBSOCKET_MAX_HISTORY_PER_CHANNEL', 1000),
        
        // 每用户最大离线消息数
        'max_offline_messages_per_user' => env('WEBSOCKET_MAX_OFFLINE_MESSAGES', 100),
        
        // 离线消息过期时间（秒）
        'offline_message_ttl' => env('WEBSOCKET_OFFLINE_MESSAGE_TTL', 604800), // 7天
    ],

    /*
    |--------------------------------------------------------------------------
    | 性能配置
    |--------------------------------------------------------------------------
    |
    | 性能优化和监控相关配置
    |
    */

    'performance' => [
        // 启用性能监控
        'monitoring_enabled' => env('WEBSOCKET_MONITORING_ENABLED', true),
        
        // 统计信息更新间隔（秒）
        'stats_update_interval' => env('WEBSOCKET_STATS_UPDATE_INTERVAL', 30),
        
        // 连接清理间隔（秒）
        'cleanup_interval' => env('WEBSOCKET_CLEANUP_INTERVAL', 60),
        
        // 连接超时时间（秒）
        'connection_timeout' => env('WEBSOCKET_CONNECTION_TIMEOUT', 300), // 5分钟
        
        // 启用消息压缩
        'compression_enabled' => env('WEBSOCKET_COMPRESSION_ENABLED', true),
        
        // 压缩阈值（字节）
        'compression_threshold' => env('WEBSOCKET_COMPRESSION_THRESHOLD', 1024),
    ],

    /*
    |--------------------------------------------------------------------------
    | 安全配置
    |--------------------------------------------------------------------------
    |
    | WebSocket安全相关配置
    |
    */

    'security' => [
        // 允许的来源
        'allowed_origins' => explode(',', env('WEBSOCKET_ALLOWED_ORIGINS', '*')),
        
        // 每IP最大连接数
        'max_connections_per_ip' => env('WEBSOCKET_MAX_CONNECTIONS_PER_IP', 10),
        
        // 启用IP白名单
        'ip_whitelist_enabled' => env('WEBSOCKET_IP_WHITELIST_ENABLED', false),
        
        // IP白名单
        'ip_whitelist' => explode(',', env('WEBSOCKET_IP_WHITELIST', '')),
        
        // 启用IP黑名单
        'ip_blacklist_enabled' => env('WEBSOCKET_IP_BLACKLIST_ENABLED', false),
        
        // IP黑名单
        'ip_blacklist' => explode(',', env('WEBSOCKET_IP_BLACKLIST', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | 日志配置
    |--------------------------------------------------------------------------
    |
    | WebSocket相关日志配置
    |
    */

    'logging' => [
        // 启用连接日志
        'log_connections' => env('WEBSOCKET_LOG_CONNECTIONS', true),
        
        // 启用消息日志
        'log_messages' => env('WEBSOCKET_LOG_MESSAGES', false),
        
        // 启用错误日志
        'log_errors' => env('WEBSOCKET_LOG_ERRORS', true),
        
        // 日志级别
        'log_level' => env('WEBSOCKET_LOG_LEVEL', 'info'),
        
        // 日志通道
        'log_channel' => env('WEBSOCKET_LOG_CHANNEL', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis配置
    |--------------------------------------------------------------------------
    |
    | WebSocket使用的Redis相关配置
    |
    */

    'redis' => [
        // 连接名称
        'connection' => env('WEBSOCKET_REDIS_CONNECTION', 'default'),
        
        // 键前缀
        'key_prefix' => env('WEBSOCKET_REDIS_PREFIX', 'websocket:'),
        
        // 队列配置
        'queues' => [
            'incoming' => 'websocket_incoming',
            'outgoing' => 'websocket_outgoing',
            'events' => 'websocket_events'
        ],
        
        // 缓存配置
        'cache' => [
            'stats' => 'websocket_stats',
            'connections' => 'websocket_connections',
            'channels' => 'websocket_channels'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 通知配置
    |--------------------------------------------------------------------------
    |
    | 实时通知的默认配置
    |
    */

    'notifications' => [
        // 默认显示时长（毫秒）
        'default_duration' => 5000,
        
        // 最大同时显示通知数
        'max_notifications' => 5,
        
        // 启用声音提醒
        'sound_enabled' => env('WEBSOCKET_NOTIFICATION_SOUND_ENABLED', true),
        
        // 默认位置
        'default_position' => 'top-right',
        
        // 通知类型
        'types' => [
            'info' => [
                'icon' => 'fas fa-info-circle',
                'color' => '#3498db',
                'sound' => 'notification-info.mp3'
            ],
            'success' => [
                'icon' => 'fas fa-check-circle',
                'color' => '#27ae60',
                'sound' => 'notification-success.mp3'
            ],
            'warning' => [
                'icon' => 'fas fa-exclamation-triangle',
                'color' => '#f39c12',
                'sound' => 'notification-warning.mp3'
            ],
            'error' => [
                'icon' => 'fas fa-times-circle',
                'color' => '#e74c3c',
                'sound' => 'notification-error.mp3'
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 开发配置
    |--------------------------------------------------------------------------
    |
    | 开发环境下的特殊配置
    |
    */

    'development' => [
        // 启用调试模式
        'debug_mode' => env('APP_DEBUG', false),
        
        // 启用详细日志
        'verbose_logging' => env('WEBSOCKET_VERBOSE_LOGGING', false),
        
        // 启用性能分析
        'profiling_enabled' => env('WEBSOCKET_PROFILING_ENABLED', false),
        
        // 模拟延迟（毫秒）
        'simulate_latency' => env('WEBSOCKET_SIMULATE_LATENCY', 0),
    ],
];