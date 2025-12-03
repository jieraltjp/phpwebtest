@echo off
echo 正在安装雅虎 B2B 采购门户依赖...
cd /d "D:\しょうけつ\Programme\php\phpwebtest"

echo.
echo 设置 PHP 环境变量...
set PATH=D:\xxamp\php;%PATH%

echo.
echo 检查 PHP 版本...
php -v

echo.
echo 尝试使用本地 composer.phar 安装依赖...
if exist composer.phar (
    echo 找到 composer.phar，尝试安装...
    php composer.phar install --no-ssl --ignore-platform-reqs
    if %errorlevel% equ 0 (
        echo 依赖安装成功！
        goto :success
    )
)

echo.
echo 尝试使用全局 composer 安装依赖...
composer install --ignore-platform-reqs
if %errorlevel% equ 0 (
    echo 依赖安装成功！
    goto :success
)

echo.
echo 所有安装方法都失败了，请手动安装依赖：
echo 1. 下载 Composer: https://getcomposer.org/Composer-Setup.exe
echo 2. 运行: composer install
echo 3. 或者使用 XAMPP 的 Shell: composer install
goto :end

:success
echo.
echo 依赖安装完成！
echo 正在启动服务器...
php artisan serve

:end
echo.
echo 按任意键退出...
pause > nul