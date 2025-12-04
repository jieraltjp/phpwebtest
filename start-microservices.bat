@echo off
REM =============================================================================
REM 万方商事 B2B 采购门户微服务架构快速启动脚本 (Windows版本)
REM =============================================================================
REM 
REM 使用方法:
REM   start-microservices.bat [环境] [阶段]
REM   
REM 环境:
REM   dev     - 开发环境 (默认)
REM   staging - 测试环境
REM   prod    - 生产环境
REM
REM 阶段:
REM   infra   - 基础设施阶段 (默认)
REM   core    - 核心服务阶段
REM   business - 业务服务阶段
REM   full    - 完整部署
REM
REM 示例:
REM   start-microservices.bat dev infra
REM   start-microservices.bat prod full
REM =============================================================================

setlocal enabledelayedexpansion

REM 设置颜色代码
set "RED=[91m"
set "GREEN=[92m"
set "YELLOW=[93m"
set "BLUE=[94m"
set "NC=[0m"

REM 默认参数
set ENVIRONMENT=%1
if "%ENVIRONMENT%"=="" set ENVIRONMENT=dev

set STAGE=%2
if "%STAGE%"=="" set STAGE=infra

REM 显示横幅
echo %BLUE%
echo ==============================================================================
echo                     万方商事 B2B 采购门户微服务架构
echo                          Microservices Architecture
echo ==============================================================================
echo %NC%

REM 检查 Docker
echo %BLUE%[INFO]%NC% 检查系统依赖...
docker --version >nul 2>&1
if errorlevel 1 (
    echo %RED%[ERROR]%NC% Docker 未安装，请先安装 Docker Desktop
    pause
    exit /b 1
)

REM 检查 Docker Compose
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo %RED%[ERROR]%NC% Docker Compose 未安装，请先安装 Docker Compose
    pause
    exit /b 1
)

echo %GREEN%[SUCCESS]%NC% 依赖检查通过

REM 设置环境变量
echo %BLUE%[INFO]%NC% 设置环境: %ENVIRONMENT%

if "%ENVIRONMENT%"=="dev" (
    set COMPOSE_FILE=docker-compose.dev.yml
    set NAMESPACE=banho-portal-dev
) else if "%ENVIRONMENT%"=="staging" (
    set COMPOSE_FILE=docker-compose.staging.yml
    set NAMESPACE=banho-portal-staging
) else if "%ENVIRONMENT%"=="prod" (
    set COMPOSE_FILE=docker-compose.prod.yml
    set NAMESPACE=banho-portal
    REM 检查 kubectl (生产环境需要)
    kubectl version >nul 2>&1
    if errorlevel 1 (
        echo %RED%[ERROR]%NC% kubectl 未安装，生产环境需要 kubectl
        pause
        exit /b 1
    )
) else (
    echo %RED%[ERROR]%NC% 未知环境: %ENVIRONMENT%
    pause
    exit /b 1
)

echo %GREEN%[SUCCESS]%NC% 环境设置完成

REM 创建必要的目录
echo %BLUE%[INFO]%NC% 创建必要的目录...

if not exist logs mkdir logs
if not exist logs\user-service mkdir logs\user-service
if not exist logs\product-service mkdir logs\product-service
if not exist logs\order-service mkdir logs\order-service
if not exist logs\gateway mkdir logs\gateway

if not exist data mkdir data
if not exist data\mysql mkdir data\mysql
if not exist data\redis mkdir data\redis
if not exist data\elasticsearch mkdir data\elasticsearch
if not exist data\rabbitmq mkdir data\rabbitmq

if not exist config mkdir config
if not exist config\nginx mkdir config\nginx
if not exist config\prometheus mkdir config\prometheus
if not exist config\grafana mkdir config\grafana

if not exist scripts mkdir scripts
if not exist k8s mkdir k8s
if not exist k8s\infrastructure mkdir k8s\infrastructure
if not exist k8s\services mkdir k8s\services
if not exist k8s\monitoring mkdir k8s\monitoring

echo %GREEN%[SUCCESS]%NC% 目录创建完成

REM 生成配置文件
echo %BLUE%[INFO]%NC% 生成配置文件...

REM 生成环境变量文件
echo # 万方商事 B2B 采购门户微服务环境配置 > .env.microservices
echo ENVIRONMENT=%ENVIRONMENT% >> .env.microservices
echo NAMESPACE=%NAMESPACE% >> .env.microservices
echo. >> .env.microservices
echo # 数据库配置 >> .env.microservices
echo MYSQL_ROOT_PASSWORD=banho123 >> .env.microservices
echo MYSQL_USER=banho >> .env.microservices
echo MYSQL_PASSWORD=banho123 >> .env.microservices
echo. >> .env.microservices
echo # Redis 配置 >> .env.microservices
echo REDIS_PASSWORD=banho123 >> .env.microservices
echo. >> .env.microservices
echo # JWT 配置 >> .env.microservices
echo JWT_SECRET=your-jwt-secret-key-here >> .env.microservices
echo JWT_TTL=3600 >> .env.microservices
echo JWT_REFRESH_TTL=604800 >> .env.microservices
echo. >> .env.microservices
echo # 服务端口配置 >> .env.microservices
echo API_GATEWAY_PORT=8080 >> .env.microservices
echo USER_SERVICE_PORT=8001 >> .env.microservices
echo PRODUCT_SERVICE_PORT=8002 >> .env.microservices
echo ORDER_SERVICE_PORT=8003 >> .env.microservices
echo INQUIRY_SERVICE_PORT=8004 >> .env.microservices
echo PURCHASE_SERVICE_PORT=8005 >> .env.microservices
echo NOTIFICATION_SERVICE_PORT=8006 >> .env.microservices
echo CONFIG_SERVICE_PORT=8007 >> .env.microservices
echo PAYMENT_SERVICE_PORT=8008 >> .env.microservices
echo LOGISTICS_SERVICE_PORT=8009 >> .env.microservices
echo ANALYTICS_SERVICE_PORT=8010 >> .env.microservices
echo. >> .env.microservices
echo # Consul 配置 >> .env.microservices
echo CONSUL_HOST=consul >> .env.microservices
echo CONSUL_PORT=8500 >> .env.microservices
echo. >> .env.microservices
echo # RabbitMQ 配置 >> .env.microservices
echo RABBITMQ_HOST=rabbitmq >> .env.microservices
echo RABBITMQ_PORT=5672 >> .env.microservices
echo RABBITMQ_USER=banho >> .env.microservices
echo RABBITMQ_PASSWORD=banho123 >> .env.microservices
echo. >> .env.microservices
echo # Elasticsearch 配置 >> .env.microservices
echo ELASTICSEARCH_HOST=elasticsearch >> .env.microservices
echo ELASTICSEARCH_PORT=9200 >> .env.microservices
echo. >> .env.microservices
echo # 监控配置 >> .env.microservices
echo PROMETHEUS_PORT=9090 >> .env.microservices
echo GRAFANA_PORT=3000 >> .env.microservices
echo JAEGER_PORT=16686 >> .env.microservices
echo. >> .env.microservices
echo # 应用配置 >> .env.microservices
echo APP_NAME=Banho B2B Portal >> .env.microservices
echo APP_VERSION=2.0.0 >> .env.microservices
echo APP_DEBUG=false >> .env.microservices
echo APP_TIMEZONE=Asia/Tokyo >> .env.microservices
echo. >> .env.microservices
echo # 万方商事品牌配置 >> .env.microservices
echo BANHO_COMPANY_NAME=万方商事株式会社 >> .env.microservices
echo BANHO_WEBSITE=https://manpou.jp/ >> .env.microservices
echo BANHO_SUPPORT_EMAIL=support@manpou.jp >> .env.microservices

echo %GREEN%[SUCCESS]%NC% 配置文件生成完成

REM 根据阶段执行部署
if "%STAGE%"=="infra" goto :deploy_infrastructure
if "%STAGE%"=="core" goto :deploy_core_services
if "%STAGE%"=="business" goto :deploy_business_services
if "%STAGE%"=="full" goto :deploy_full

echo %RED%[ERROR]%NC% 未知阶段: %STAGE%
pause
exit /b 1

:deploy_infrastructure
echo %BLUE%[INFO]%NC% 部署基础设施服务...

REM 启动基础设施服务
docker-compose -f %COMPOSE_FILE% up -d consul redis mysql-user mysql-product mysql-order rabbitmq elasticsearch

echo %BLUE%[INFO]%NC% 等待基础设施服务启动...
timeout /t 30 /nobreak >nul

REM 初始化数据库
echo %BLUE%[INFO]%NC% 初始化数据库...
timeout /t 20 /nobreak >nul

docker-compose -f %COMPOSE_FILE% exec mysql-user mysql -uroot -pbanho123 -e "CREATE DATABASE IF NOT EXISTS user_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON user_db.* TO 'banho'@'%';"

docker-compose -f %COMPOSE_FILE% exec mysql-product mysql -uroot -pbanho123 -e "CREATE DATABASE IF NOT EXISTS product_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON product_db.* TO 'banho'@'%';"

docker-compose -f %COMPOSE_FILE% exec mysql-order mysql -uroot -pbanho123 -e "CREATE DATABASE IF NOT EXISTS order_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS inquiry_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS purchase_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON *.* TO 'banho'@'%';"

REM 启动监控服务
docker-compose -f %COMPOSE_FILE% up -d prometheus grafana jaeger

echo %GREEN%[SUCCESS]%NC% 基础设施部署完成
goto :end

:deploy_core_services
call :deploy_infrastructure

echo %BLUE%[INFO]%NC% 部署核心业务服务...

REM 构建服务镜像
echo %BLUE%[INFO]%NC% 构建服务镜像...
docker build -t banho-portal/user-service:latest -f docker/user-service/Dockerfile .
docker build -t banho-portal/product-service:latest -f docker/product-service/Dockerfile .
docker build -t banho-portal/order-service:latest -f docker/order-service/Dockerfile .

REM 启动核心服务
docker-compose -f %COMPOSE_FILE% up -d user-service product-service order-service

echo %BLUE%[INFO]%NC% 等待核心服务启动...
timeout /t 30 /nobreak >nul

REM 启动 API 网关
docker-compose -f %COMPOSE_FILE% up -d api-gateway

echo %GREEN%[SUCCESS]%NC% 核心服务部署完成
goto :end

:deploy_business_services
call :deploy_core_services

echo %BLUE%[INFO]%NC% 部署业务服务...

REM 构建业务服务镜像
echo %BLUE%[INFO]%NC% 构建业务服务镜像...
docker build -t banho-portal/inquiry-service:latest -f docker/inquiry-service/Dockerfile .
docker build -t banho-portal/purchase-service:latest -f docker/purchase-service/Dockerfile .
docker build -t banho-portal/notification-service:latest -f docker/notification-service/Dockerfile .
docker build -t banho-portal/config-service:latest -f docker/config-service/Dockerfile .

REM 启动业务服务
docker-compose -f %COMPOSE_FILE% up -d inquiry-service purchase-service notification-service config-service

echo %BLUE%[INFO]%NC% 等待业务服务启动...
timeout /t 30 /nobreak >nul

echo %GREEN%[SUCCESS]%NC% 业务服务部署完成
goto :end

:deploy_full
call :deploy_business_services

echo %BLUE%[INFO]%NC% 部署支撑服务...

REM 构建支撑服务镜像
echo %BLUE%[INFO]%NC% 构建支撑服务镜像...
docker build -t banho-portal/payment-service:latest -f docker/payment-service/Dockerfile .
docker build -t banho-portal/logistics-service:latest -f docker/logistics-service/Dockerfile .
docker build -t banho-portal/analytics-service:latest -f docker/analytics-service/Dockerfile .

REM 启动支撑服务
docker-compose -f %COMPOSE_FILE% up -d payment-service logistics-service analytics-service

echo %BLUE%[INFO]%NC% 等待支撑服务启动...
timeout /t 30 /nobreak >nul

echo %GREEN%[SUCCESS]%NC% 支撑服务部署完成
goto :end

:end
REM 运行健康检查
echo %BLUE%[INFO]%NC% 运行系统健康检查...
echo.
echo ==================== 服务状态 ====================
docker-compose -f %COMPOSE_FILE% ps
echo.

REM 显示访问信息
echo.
echo %GREEN%==================== 访问信息 ====================%NC%
echo %BLUE%API 网关:%NC%        http://localhost:8080
echo %BLUE%用户服务:%NC%        http://localhost:8001
echo %BLUE%产品服务:%NC%        http://localhost:8002
echo %BLUE%订单服务:%NC%        http://localhost:8003
echo %BLUE%Consul UI:%NC%       http://localhost:8500
echo %BLUE%Grafana:%NC%         http://localhost:3000 (admin/admin123)
echo %BLUE%Prometheus:%NC%      http://localhost:9090
echo %BLUE%Jaeger:%NC%          http://localhost:16686
echo %BLUE%RabbitMQ:%NC%        http://localhost:15672 (banho/banho123)
echo.
echo %GREEN%==================================================%NC%

echo.
echo %GREEN%[SUCCESS]%NC% 微服务架构部署完成！
echo.
echo 查看日志: docker-compose -f %COMPOSE_FILE% logs -f [service-name]
echo 停止服务: docker-compose -f %COMPOSE_FILE% down
echo 清理环境: start-microservices.bat --cleanup
echo.

pause