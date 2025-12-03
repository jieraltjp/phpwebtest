<?php
// 简单的路由器，用于在没有完整 Laravel 的情况下处理基本路由

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// 解析 URL
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

// 路由映射
$routes = [
    '/' => 'index_laravel.php',
    '/home' => 'index_laravel.php',
    '/dashboard' => 'index_laravel.php',
    '/products' => 'index_laravel.php',
    '/orders' => 'index_laravel.php',
    '/admin' => 'index_laravel.php',
    '/docs' => 'index_laravel.php',
    '/api/health' => 'api/health.php',
    '/api/test' => 'api/health.php',
];

// 检查是否有匹配的路由
if (isset($routes[$path])) {
    $file = __DIR__ . '/' . $routes[$path];
    if (file_exists($file)) {
        include $file;
        exit;
    }
}

// 处理 API 路径
if (strpos($path, '/api/') === 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'API endpoint not found',
        'message' => '请安装完整的 Laravel 依赖以获得完整的 API 功能',
        'path' => $path
    ]);
    exit;
}

// 默认重定向到首页
header('Location: /');
exit();
?>