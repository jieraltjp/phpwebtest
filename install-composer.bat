@echo off
title 安装 Composer 依赖
color 0B

echo.
echo =====================================
echo   雅虎 B2B 采购门户 - 安装依赖
echo =====================================
echo.

cd /d "D:\しょうけつ\Programme\php\phpwebtest"

echo [1] 检查 Composer 是否已安装...
composer --version >nul 2>&1
if %errorlevel% equ 0 (
    echo Composer 已安装
    goto :install_deps
)

echo.
echo [2] 下载并安装 Composer...
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
if %errorlevel% neq 0 (
    echo Composer 安装失败
    pause
    exit /b 1
)

:install_deps
echo.
echo [3] 安装项目依赖...
echo 这可能需要几分钟时间，请耐心等待...
echo.

composer install --no-plugins --no-scripts
if %errorlevel% neq 0 (
    echo.
    echo 安装失败，尝试使用忽略平台要求的方式...
    composer install --ignore-platform-reqs --no-plugins --no-scripts
)

echo.
echo [4] 生成应用密钥...
php artisan key:generate

echo.
echo [5] 运行数据库迁移...
php artisan migrate --force

echo.
echo [6] 填充测试数据...
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ProductSeeder

echo.
echo =====================================
echo   安装完成！
echo =====================================
echo.
echo 现在可以启动项目了：
echo   php artisan serve
echo.
echo 或者运行快速启动脚本：
echo   quick-start.bat
echo.

pause