<?php

/**
 * 路由测试脚本
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;

echo "=== 路由测试 ===\n\n";

try {
    // 初始化应用
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    // 获取路由服务
    $routes = $app->make('router')->getRoutes();

    echo "注册的路由数量: " . count($routes) . "\n\n";

    // 查找版本化路由
    $versionRoutes = [];
    $apiRoutes = [];

    foreach ($routes as $route) {
        $uri = $route->uri();
        
        if (strpos($uri, 'versions') !== false) {
            $versionRoutes[] = $uri;
        }
        
        if (strpos($uri, 'api/') === 0) {
            $apiRoutes[] = $uri;
        }
    }

    echo "版本管理路由:\n";
    foreach ($versionRoutes as $route) {
        echo "  ✅ {$route}\n";
    }

    echo "\nAPI 路由 (前20个):\n";
    $apiRoutes = array_slice($apiRoutes, 0, 20);
    foreach ($apiRoutes as $route) {
        echo "  📡 {$route}\n";
    }

    echo "\n=== 测试完成 ===\n";

} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}
