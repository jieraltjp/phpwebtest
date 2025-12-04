<?php
// 简单的页面测试脚本

echo "=== 万方商事 B2B 采购门户页面测试 ===\n\n";

// 测试基本页面访问
$pages = [
    '/' => '万方商事首页',
    '/banho' => '万方商事品牌页',
    '/banho/dashboard' => '万方商事仪表板',
    '/auth' => '认证页面',
    '/dashboard' => '原仪表板',
    '/products' => '产品页面',
    '/orders' => '订单页面',
    '/docs' => 'API文档页面',
];

foreach ($pages as $url => $description) {
    echo "测试: $description ($url)\n";
    $ch = curl_init("http://localhost:8000$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✓ 成功 (HTTP $httpCode)\n";
    } else {
        echo "✗ 失败 (HTTP $httpCode)\n";
    }
    echo "\n";
}

// 测试API端点
echo "=== API端点测试 ===\n";
$apis = [
    '/api/health' => '健康检查',
    '/api/banho/config' => '万方商事配置',
    '/api/test' => '测试接口',
];

foreach ($apis as $url => $description) {
    echo "测试: $description ($url)\n";
    $ch = curl_init("http://localhost:8000$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✓ 成功 (HTTP $httpCode)\n";
        echo "响应: " . substr($response, 0, 100) . "...\n";
    } else {
        echo "✗ 失败 (HTTP $httpCode)\n";
    }
    echo "\n";
}

echo "=== 测试完成 ===\n";
?>