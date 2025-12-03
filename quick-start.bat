@echo off
title 雅虎 B2B 采购门户 - 快速启动
color 0A

echo.
echo =====================================
echo   雅虎 B2B 采购门户 - 快速启动
echo =====================================
echo.

cd /d "D:\しょうけつ\Programme\php\phpwebtest"

echo [1] 检查 PHP 环境...
"D:\xxamp\php\php.exe" -v >nul 2>&1
if %errorlevel% neq 0 (
    echo 错误: PHP 未找到或不可用
    echo 请确保 PHP 已安装并添加到 PATH 环境变量
    pause
    exit /b 1
)
echo PHP 环境正常

echo.
echo [2] 检查依赖状态...
if not exist "vendor\autoload.php" (
    echo 警告: Composer 依赖未安装
    echo.
    echo 选择操作:
    echo 1. 尝试自动安装依赖
    echo 2. 启动基础服务器 (功能受限)
    echo 3. 退出
    echo.
    set /p choice="请输入选择 (1-3): "
    
    if "%choice%"=="1" (
        echo 正在尝试安装依赖...
        call install-deps.bat
    ) else if "%choice%"=="2" (
        echo 启动基础服务器...
        goto :start_server
    ) else (
        echo 退出
        exit /b 0
    )
)

echo.
echo [3] 启动服务器...
:start_server
echo 正在启动 PHP 开发服务器...
echo 服务器地址: http://localhost:8000
echo 按 Ctrl+C 停止服务器
echo.

if exist "vendor\autoload.php" (
    echo 启动完整 Laravel 应用...
    "D:\xxamp\php\php.exe" artisan serve --host=localhost --port=8000
) else (
    echo 启动基础 PHP 服务器...
    "D:\xxamp\php\php.exe" -S localhost:8000 -t public
)

echo.
echo 服务器已停止
pause