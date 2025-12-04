@echo off
echo ========================================
echo 万方商事 B2B 采购门户 - 测试运行脚本
echo ========================================
echo.

:: 检查 PHP 是否安装
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [错误] PHP 未安装或未添加到 PATH
    echo 请先安装 PHP 8.2 或更高版本
    pause
    exit /b 1
)

:: 检查 Composer 是否安装
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [错误] Composer 未安装或未添加到 PATH
    echo 请先安装 Composer
    pause
    exit /b 1
)

:: 检查是否在项目根目录
if not exist "composer.json" (
    echo [错误] 请在项目根目录运行此脚本
    pause
    exit /b 1
)

echo [信息] 开始运行测试套件...
echo.

:: 创建必要的目录
if not exist "build" mkdir build
if not exist "build\coverage" mkdir build\coverage
if not exist "build\logs" mkdir build\logs

:: 运行测试前的准备工作
echo [步骤 1] 准备测试环境...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1

:: 运行测试
echo [步骤 2] 执行自动化测试...
echo.

:: 运行所有测试并生成覆盖率报告
php artisan test --coverage --coverage-html=build/coverage --coverage-text=build/coverage.txt --coverage-clover=build/logs/clover.xml --log-junit=build/report.junit.xml

set TEST_EXIT_CODE=%errorlevel%

echo.
echo ========================================
echo 测试完成
echo ========================================

:: 显示测试结果摘要
if %TEST_EXIT_CODE% equ 0 (
    echo [成功] 所有测试通过 ✓
) else (
    echo [失败] 部分测试失败 ✗
)

echo.
echo [报告] 测试报告已生成:
echo   - HTML 覆盖率报告: build\coverage\index.html
echo   - 文本覆盖率报告: build\coverage.txt
echo   - XML 覆盖率报告: build\logs\clover.xml
echo   - JUnit 测试报告: build\report.junit.xml
echo.

:: 询问是否打开覆盖率报告
set /p OPEN_REPORT="是否打开 HTML 覆盖率报告? (y/n): "
if /i "%OPEN_REPORT%"=="y" (
    if exist "build\coverage\index.html" (
        start build\coverage\index.html
        echo [信息] 已打开覆盖率报告
    ) else (
        echo [警告] 覆盖率报告文件不存在
    )
)

echo.
echo [完成] 测试执行完成
pause
exit /b %TEST_EXIT_CODE%