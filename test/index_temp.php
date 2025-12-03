<?php
echo "<h1>雅虎 B2B 采购门户</h1>";
echo "<p>服务器正在运行！</p>";
echo "<p>当前时间: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP 版本: " . phpversion() . "</p>";
echo "<hr>";
echo "<h2>项目状态</h2>";
echo "<p>✅ PHP 服务器已启动</p>";
echo "<p>⚠️ 需要安装 Composer 依赖</p>";
echo "<p>📝 请运行以下命令安装依赖:</p>";
echo "<pre>cd:D:\\しょうけつ\\Programme\\php\\phpwebtest
composer install</pre>";
echo "<hr>";
echo "<h2>测试链接</h2>";
echo "<ul>";
echo "<li><a href='/index_temp.php'>首页</a></li>";
echo "<li><a href='/test.php'>测试页面</a></li>";
echo "<li><a href='/status.php'>项目状态</a></li>";
echo "</ul>";
?>