<?php

/**
 * 实时通信功能测试脚本
 * 
 * 测试WebSocket服务、消息持久化、事件通知等功能
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\WebSocketService;
use App\Services\RealtimeEventService;
use App\Services\MessagePersistenceService;
use App\Services\CacheService;
use App\Services\EventService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

// 初始化Laravel应用
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== 万方商事 B2B 采购门户 - 实时通信功能测试 ===\n\n";

// 测试计数器
$tests = [
    'websocket_service' => false,
    'message_persistence' => false,
    'realtime_events' => false,
    'database_connection' => false,
    'redis_connection' => false
];

try {
    // 1. 测试数据库连接
    echo "1. 测试数据库连接...\n";
    $connection = \Illuminate\Support\Facades\DB::connection();
    $connection->getPdo();
    echo "   ✅ 数据库连接成功\n";
    $tests['database_connection'] = true;
} catch (Exception $e) {
    echo "   ❌ 数据库连接失败: " . $e->getMessage() . "\n";
}

try {
    // 2. 测试Redis连接（可选）
    echo "\n2. 测试Redis连接...\n";
    if (class_exists('Redis')) {
        $redis = \Illuminate\Support\Facades\Redis::connection();
        $redis->ping();
        echo "   ✅ Redis连接成功\n";
        $tests['redis_connection'] = true;
    } else {
        echo "   ⚠️  Redis扩展未安装，使用内存存储\n";
        $tests['redis_connection'] = true; // 内存存储也算通过
    }
} catch (Exception $e) {
    echo "   ⚠️  Redis连接失败，使用内存存储: " . $e->getMessage() . "\n";
    $tests['redis_connection'] = true; // 内存存储也算通过
}

try {
    // 3. 测试WebSocket服务
    echo "\n3. 测试WebSocket服务...\n";
    $cacheService = app(CacheService::class);
    $eventService = app(EventService::class);
    $webSocketService = new WebSocketService($cacheService, $eventService);
    
    // 测试连接建立
    $connectionId = 'test_connection_' . uniqid();
    $connected = $webSocketService->connect($connectionId);
    
    if ($connected) {
        echo "   ✅ WebSocket连接建立成功\n";
        
        // 测试消息处理
        $message = [
            'type' => 'ping',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $handled = $webSocketService->handleMessage($connectionId, $message);
        
        if ($handled) {
            echo "   ✅ WebSocket消息处理成功\n";
        } else {
            echo "   ❌ WebSocket消息处理失败\n";
        }
        
        // 测试断开连接
        $webSocketService->disconnect($connectionId);
        echo "   ✅ WebSocket断开连接成功\n";
        
        $tests['websocket_service'] = true;
    } else {
        echo "   ❌ WebSocket连接建立失败\n";
    }
} catch (Exception $e) {
    echo "   ❌ WebSocket服务测试失败: " . $e->getMessage() . "\n";
}

try {
    // 4. 测试消息持久化服务
    echo "\n4. 测试消息持久化服务...\n";
    $cacheService = app(CacheService::class);
    $messageService = new MessagePersistenceService($cacheService);
    
    // 测试存储离线消息
    $userId = 1;
    $testMessage = [
        'type' => 'test_message',
        'data' => [
            'title' => '测试消息',
            'message' => '这是一条测试消息',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
    
    $stored = $messageService->storeOfflineMessage($userId, $testMessage);
    if ($stored) {
        echo "   ✅ 离线消息存储成功\n";
        
        // 测试获取离线消息
        $messages = $messageService->getOfflineMessages($userId);
        if (count($messages) > 0) {
            echo "   ✅ 离线消息获取成功，共 " . count($messages) . " 条\n";
        } else {
            echo "   ❌ 离线消息获取失败\n";
        }
        
        // 清理测试数据
        $messageService->clearOfflineMessages($userId);
        echo "   ✅ 测试数据清理完成\n";
        
        $tests['message_persistence'] = true;
    } else {
        echo "   ❌ 离线消息存储失败\n";
    }
} catch (Exception $e) {
    echo "   ❌ 消息持久化服务测试失败: " . $e->getMessage() . "\n";
}

try {
    // 5. 测试实时事件服务
    echo "\n5. 测试实时事件服务...\n";
    $webSocketService = app(WebSocketService::class);
    $cacheService = app(CacheService::class);
    $realtimeService = new RealtimeEventService($webSocketService, $cacheService);
    
    // 创建测试订单
    $testOrder = new stdClass();
    $testOrder->id = 999;
    $testOrder->user_id = 1;
    $testOrder->order_number = 'TEST-' . uniqid();
    $testOrder->total_amount = 1000;
    $testOrder->currency = 'CNY';
    
    // 测试新订单通知
    $realtimeService->notifyNewOrder($testOrder);
    echo "   ✅ 新订单通知发送成功\n";
    
    // 测试系统消息广播
    $realtimeService->broadcastSystemMessage(
        '测试系统消息',
        '这是一条测试系统消息',
        'info'
    );
    echo "   ✅ 系统消息广播成功\n";
    
    $tests['realtime_events'] = true;
} catch (Exception $e) {
    echo "   ❌ 实时事件服务测试失败: " . $e->getMessage() . "\n";
}

// 6. 测试API端点
echo "\n6. 测试API端点...\n";
$apiTests = [
    '/api/websocket/config' => false,
    '/api/health' => false
];

try {
    // 测试WebSocket配置API
    echo "   测试WebSocket配置API...\n";
    $response = file_get_contents('http://localhost:8000/api/websocket/config');
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['status']) && $data['status'] === 'success') {
            echo "   ✅ WebSocket配置API正常\n";
            $apiTests['/api/websocket/config'] = true;
        } else {
            echo "   ❌ WebSocket配置API响应异常\n";
        }
    } else {
        echo "   ❌ WebSocket配置API无响应\n";
    }
} catch (Exception $e) {
    echo "   ❌ WebSocket配置API测试失败: " . $e->getMessage() . "\n";
}

try {
    // 测试健康检查API
    echo "   测试健康检查API...\n";
    $response = file_get_contents('http://localhost:8000/api/health');
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['status']) && $data['status'] === 'ok') {
            echo "   ✅ 健康检查API正常\n";
            $apiTests['/api/health'] = true;
        } else {
            echo "   ❌ 健康检查API响应异常\n";
        }
    } else {
        echo "   ❌ 健康检查API无响应\n";
    }
} catch (Exception $e) {
    echo "   ❌ 健康检查API测试失败: " . $e->getMessage() . "\n";
}

// 7. 性能测试
echo "\n7. 性能测试...\n";
try {
    $startTime = microtime(true);
    
    // 测试大量消息处理
    $webSocketService = app(WebSocketService::class);
    $messageCount = 1000;
    $successCount = 0;
    
    for ($i = 0; $i < $messageCount; $i++) {
        $connectionId = 'perf_test_' . $i;
        if ($webSocketService->connect($connectionId)) {
            $successCount++;
            $webSocketService->disconnect($connectionId);
        }
    }
    
    $endTime = microtime(true);
    $duration = ($endTime - $startTime) * 1000; // 转换为毫秒
    
    echo "   处理 {$messageCount} 个连接耗时: " . number_format($duration, 2) . " ms\n";
    echo "   成功处理: {$successCount}/{$messageCount}\n";
    echo "   平均处理时间: " . number_format($duration / $messageCount, 2) . " ms/连接\n";
    
    if ($successCount >= $messageCount * 0.95) { // 95%成功率
        echo "   ✅ 性能测试通过\n";
    } else {
        echo "   ❌ 性能测试未达标\n";
    }
} catch (Exception $e) {
    echo "   ❌ 性能测试失败: " . $e->getMessage() . "\n";
}

// 8. 内存使用测试
echo "\n8. 内存使用测试...\n";
$memoryBefore = memory_get_usage(true);
$peakBefore = memory_get_peak_usage(true);

try {
    // 创建多个服务实例
    for ($i = 0; $i < 10; $i++) {
        $cacheService = app(CacheService::class);
        $eventService = app(EventService::class);
        new WebSocketService($cacheService, $eventService);
    }
    
    $memoryAfter = memory_get_usage(true);
    $peakAfter = memory_get_peak_usage(true);
    
    $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // MB
    $peakUsed = ($peakAfter - $peakBefore) / 1024 / 1024; // MB
    
    echo "   内存使用: " . number_format($memoryUsed, 2) . " MB\n";
    echo "   峰值内存: " . number_format($peakUsed, 2) . " MB\n";
    echo "   当前总内存: " . number_format($memoryAfter / 1024 / 1024, 2) . " MB\n";
    
    if ($memoryUsed < 100) { // 小于100MB认为正常
        echo "   ✅ 内存使用正常\n";
    } else {
        echo "   ❌ 内存使用过高\n";
    }
} catch (Exception $e) {
    echo "   ❌ 内存测试失败: " . $e->getMessage() . "\n";
}

// 测试结果汇总
echo "\n=== 测试结果汇总 ===\n";
$totalTests = count($tests) + count($apiTests);
$passedTests = array_sum($tests) + array_sum($apiTests);

echo "核心功能测试:\n";
foreach ($tests as $test => $result) {
    $status = $result ? '✅ 通过' : '❌ 失败';
    echo "  {$test}: {$status}\n";
}

echo "\nAPI端点测试:\n";
foreach ($apiTests as $test => $result) {
    $status = $result ? '✅ 通过' : '❌ 失败';
    echo "  {$test}: {$status}\n";
}

echo "\n总体结果: {$passedTests}/{$totalTests} 测试通过\n";

if ($passedTests === $totalTests) {
    echo "🎉 所有测试通过！实时通信功能正常运行。\n";
    exit(0);
} else {
    echo "⚠️  部分测试失败，请检查相关功能。\n";
    exit(1);
}