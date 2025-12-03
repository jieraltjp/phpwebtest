<?php
echo "<h1>项目测试页面</h1>";
echo "<h2>环境检查</h2>";

// 检查 PHP 版本
echo "<p>PHP 版本: " . phpversion() . "</p>";

// 检查必需的扩展
$required_extensions = ['pdo', 'pdo_sqlite', 'mbstring', 'openssl', 'tokenizer', 'xml'];
echo "<h3>PHP 扩展检查</h3>";
echo "<ul>";
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<li>✅ $ext - 已安装</li>";
    } else {
        echo "<li>❌ $ext - 未安装</li>";
    }
}
echo "</ul>";

// 检查目录权限
$directories = ['storage', 'bootstrap/cache'];
echo "<h3>目录权限检查</h3>";
echo "<ul>";
foreach ($directories as $dir) {
    $fullPath = __DIR__ . "/../$dir";
    if (is_dir($fullPath)) {
        if (is_writable($fullPath)) {
            echo "<li>✅ $dir - 可写</li>";
        } else {
            echo "<li>⚠️ $dir - 不可写</li>";
        }
    } else {
        echo "<li>❌ $dir - 不存在</li>";
    }
}
echo "</ul>";

// 检查配置文件
echo "<h3>配置文件检查</h3>";
echo "<ul>";
if (file_exists(__DIR__ . "/../.env")) {
    echo "<li>✅ .env 文件存在</li>";
} else {
    echo "<li>⚠️ .env 文件不存在</li>";
}

if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
    echo "<li>✅ vendor/autoload.php 存在</li>";
} else {
    echo "<li>❌ vendor/autoload.php 不存在</li>";
}
echo "</ul>";

echo "<hr>";
echo "<p><a href='/index_temp.php'>返回首页</a></p>";
?>