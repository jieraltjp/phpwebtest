<?php

// 简化的启动脚本
echo "正在启动 phpwebtest 项目...\n";

// 检查必要的文件
if (!file_exists('.env')) {
    echo "错误: .env 文件不存在\n";
    exit(1);
}

// 设置基本配置
$_ENV['APP_KEY'] = 'base64:' . base64_encode(random_bytes(32));
$_ENV['JWT_SECRET'] = 'base64:' . base64_encode(random_bytes(32));

echo "环境配置完成\n";
echo "项目准备就绪！\n";
echo "请使用以下命令启动服务器:\n";
echo "php -S localhost:8000 -t public\n";
echo "\n访问地址: http://localhost:8000\n";
echo "\n注意: 由于缺少依赖包，某些功能可能无法正常工作。\n";
echo "建议安装完整的 Composer 依赖以获得完整功能。\n";

?>