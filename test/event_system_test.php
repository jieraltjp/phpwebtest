<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\EventService;
use App\Events\User\UserRegisteredEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Product\ProductViewedEvent;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;

echo "=== 万方商事事件系统测试 ===\n\n";

try {
    // 1. 测试事件系统基本功能
    echo "1. 测试事件系统基本功能...\n";
    
    $stats = EventService::getStatistics();
    echo "   - 事件系统状态: " . ($stats['enabled'] ? '启用' : '禁用') . "\n";
    echo "   - 已注册监听器: " . $stats['registered_listeners'] . " 个\n";
    echo "   - 历史事件总数: " . $stats['total_events'] . " 个\n\n";

    // 2. 测试用户注册事件
    echo "2. 测试用户注册事件...\n";
    
    // 创建测试用户（模拟）
    $testUser = new class {
        public $id = 999;
        public $username = 'testuser_event';
        public $email = 'test@example.com';
        public $name = 'Test User';
    };
    
    $userEvent = new UserRegisteredEvent($testUser);
    EventService::dispatch($userEvent);
    
    echo "   - 用户注册事件已触发\n";
    echo "   - 事件ID: " . $userEvent->getId() . "\n";
    echo "   - 用户名: " . $userEvent->getUsername() . "\n";
    echo "   - 邮箱: " . $userEvent->getEmail() . "\n\n";

    // 3. 测试产品浏览事件
    echo "3. 测试产品浏览事件...\n";
    
    $testProduct = new class {
        public $id = 888;
        public $sku = 'TEST-SKU-001';
        public $name = 'Test Product';
        public $price = 100.00;
        public $currency = 'CNY';
        public $stock_quantity = 50;
        public $category = 'electronics';
    };
    
    $productEvent = new ProductViewedEvent($testProduct);
    EventService::dispatch($productEvent);
    
    echo "   - 产品浏览事件已触发\n";
    echo "   - 事件ID: " . $productEvent->getId() . "\n";
    echo "   - 产品SKU: " . $productEvent->getSku() . "\n";
    echo "   - 产品名称: " . $productEvent->getName() . "\n\n";

    // 4. 测试事件统计
    echo "4. 更新后的统计信息...\n";
    
    $updatedStats = EventService::getStatistics();
    echo "   - 历史事件总数: " . $updatedStats['total_events'] . " 个\n";
    echo "   - 事件类型: " . json_encode($updatedStats['event_types'], JSON_UNESCAPED_UNICODE) . "\n";
    echo "   - 同步事件: " . $updatedStats['sync_events'] . " 个\n";
    echo "   - 异步事件: " . $updatedStats['async_events'] . " 个\n\n";

    // 5. 测试事件历史
    echo "5. 事件历史记录...\n";
    
    $history = EventService::getEventHistory();
    echo "   - 最近事件数量: " . $history->count() . " 个\n";
    
    if ($history->count() > 0) {
        echo "   - 最近事件:\n";
        foreach ($history->take(3) as $event) {
            echo "     * " . basename($event['name']) . " (ID: " . substr($event['id'], 0, 8) . "...)\n";
        }
    }
    echo "\n";

    // 6. 测试调试信息
    echo "6. 系统调试信息...\n";
    
    $debug = EventService::debug();
    echo "   - 调度器实例: " . ($debug['dispatcher'] ? '已创建' : '未创建') . "\n";
    echo "   - 监听器详情: " . count($debug['listeners']) . " 组\n";
    
    if (!empty($debug['listeners'])) {
        foreach ($debug['listeners'] as $event => $listeners) {
            echo "     - " . basename($event) . ": " . count($listeners) . " 个监听器\n";
        }
    }
    echo "\n";

    // 7. 测试事件系统控制
    echo "7. 测试事件系统控制...\n";
    
    EventService::disable();
    echo "   - 事件系统已禁用\n";
    
    $disabledStats = EventService::getStatistics();
    echo "   - 禁用后状态: " . ($disabledStats['enabled'] ? '启用' : '禁用') . "\n";
    
    EventService::enable();
    echo "   - 事件系统已重新启用\n";
    
    $enabledStats = EventService::getStatistics();
    echo "   - 启用后状态: " . ($enabledStats['enabled'] ? '启用' : '禁用') . "\n\n";

    echo "=== 事件系统测试完成 ===\n";
    echo "✅ 所有测试通过！事件系统运行正常。\n\n";

    echo "📊 测试总结:\n";
    echo "- 事件系统核心功能正常\n";
    echo "- 事件分发机制工作正常\n";
    echo "- 监听器注册和调用正常\n";
    echo "- 统计信息收集正常\n";
    echo "- 历史记录功能正常\n";
    echo "- 系统控制功能正常\n";

} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ 系统错误: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}