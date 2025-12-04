#!/bin/bash

# =============================================================================
# 万方商事 B2B 采购门户微服务架构快速启动脚本
# =============================================================================
# 
# 使用方法:
#   ./start-microservices.sh [环境] [阶段]
#   
# 环境:
#   dev     - 开发环境 (默认)
#   staging - 测试环境
#   prod    - 生产环境
#
# 阶段:
#   infra   - 基础设施阶段 (默认)
#   core    - 核心服务阶段
#   business - 业务服务阶段
#   full    - 完整部署
#
# 示例:
#   ./start-microservices.sh dev infra
#   ./start-microservices.sh prod full
# =============================================================================

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 显示横幅
show_banner() {
    echo -e "${BLUE}"
    echo "=============================================================================="
    echo "                    万方商事 B2B 采购门户微服务架构"
    echo "                         Microservices Architecture"
    echo "=============================================================================="
    echo -e "${NC}"
}

# 检查依赖
check_dependencies() {
    log_info "检查系统依赖..."
    
    # 检查 Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker 未安装，请先安装 Docker"
        exit 1
    fi
    
    # 检查 Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose 未安装，请先安装 Docker Compose"
        exit 1
    fi
    
    # 检查 kubectl (如果需要)
    if [[ "$ENVIRONMENT" == "prod" ]]; then
        if ! command -v kubectl &> /dev/null; then
            log_error "kubectl 未安装，生产环境需要 kubectl"
            exit 1
        fi
    fi
    
    log_success "依赖检查通过"
}

# 设置环境变量
setup_environment() {
    local env=${1:-dev}
    
    log_info "设置环境: $env"
    
    case $env in
        "dev")
            export COMPOSE_FILE="docker-compose.dev.yml"
            export NAMESPACE="banho-portal-dev"
            ;;
        "staging")
            export COMPOSE_FILE="docker-compose.staging.yml"
            export NAMESPACE="banho-portal-staging"
            ;;
        "prod")
            export COMPOSE_FILE="docker-compose.prod.yml"
            export NAMESPACE="banho-portal"
            ;;
        *)
            log_error "未知环境: $env"
            exit 1
            ;;
    esac
    
    export ENVIRONMENT=$env
    log_success "环境设置完成"
}

# 创建必要的目录
create_directories() {
    log_info "创建必要的目录..."
    
    mkdir -p logs/{user-service,product-service,order-service,gateway}
    mkdir -p data/{mysql,redis,elasticsearch,rabbitmq}
    mkdir -p config/{nginx,prometheus,grafana}
    mkdir -p scripts
    mkdir -p k8s/{infrastructure,services,monitoring}
    
    log_success "目录创建完成"
}

# 生成配置文件
generate_configs() {
    log_info "生成配置文件..."
    
    # 生成环境变量文件
    cat > .env.microservices << EOF
# 万方商事 B2B 采购门户微服务环境配置
ENVIRONMENT=$ENVIRONMENT
NAMESPACE=$NAMESPACE

# 数据库配置
MYSQL_ROOT_PASSWORD=banho123
MYSQL_USER=banho
MYSQL_PASSWORD=banho123

# Redis 配置
REDIS_PASSWORD=banho123

# JWT 配置
JWT_SECRET=$(openssl rand -base64 32)
JWT_TTL=3600
JWT_REFRESH_TTL=604800

# 服务端口配置
API_GATEWAY_PORT=8080
USER_SERVICE_PORT=8001
PRODUCT_SERVICE_PORT=8002
ORDER_SERVICE_PORT=8003
INQUIRY_SERVICE_PORT=8004
PURCHASE_SERVICE_PORT=8005
NOTIFICATION_SERVICE_PORT=8006
CONFIG_SERVICE_PORT=8007
PAYMENT_SERVICE_PORT=8008
LOGISTICS_SERVICE_PORT=8009
ANALYTICS_SERVICE_PORT=8010

# Consul 配置
CONSUL_HOST=consul
CONSUL_PORT=8500

# RabbitMQ 配置
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=banho
RABBITMQ_PASSWORD=banho123

# Elasticsearch 配置
ELASTICSEARCH_HOST=elasticsearch
ELASTICSEARCH_PORT=9200

# 监控配置
PROMETHEUS_PORT=9090
GRAFANA_PORT=3000
JAEGER_PORT=16686

# 应用配置
APP_NAME="Banho B2B Portal"
APP_VERSION="2.0.0"
APP_DEBUG=false
APP_TIMEZONE=Asia/Tokyo

# 万方商事品牌配置
BANHO_COMPANY_NAME="万方商事株式会社"
BANHO_WEBSITE="https://manpou.jp/"
BANHO_SUPPORT_EMAIL="support@manpou.jp"
EOF

    log_success "配置文件生成完成"
}

# 阶段1: 基础设施部署
deploy_infrastructure() {
    log_info "部署基础设施服务..."
    
    # 启动基础设施服务
    docker-compose -f $COMPOSE_FILE up -d consul redis mysql-user mysql-product mysql-order rabbitmq elasticsearch
    
    log_info "等待基础设施服务启动..."
    sleep 30
    
    # 检查服务健康状态
    check_service_health "consul" "8500"
    check_service_health "redis" "6379"
    check_service_health "mysql-user" "3306"
    check_service_health "rabbitmq" "15672"
    check_service_health "elasticsearch" "9200"
    
    # 初始化数据库
    init_databases
    
    # 配置 Consul
    setup_consul
    
    # 启动监控服务
    docker-compose -f $COMPOSE_FILE up -d prometheus grafana jaeger
    
    log_success "基础设施部署完成"
}

# 阶段2: 核心服务部署
deploy_core_services() {
    log_info "部署核心业务服务..."
    
    # 构建服务镜像
    build_service_images "user-service product-service order-service"
    
    # 启动核心服务
    docker-compose -f $COMPOSE_FILE up -d user-service product-service order-service
    
    log_info "等待核心服务启动..."
    sleep 30
    
    # 检查服务健康状态
    check_service_health "user-service" "8001"
    check_service_health "product-service" "8002"
    check_service_health "order-service" "8003"
    
    # 启动 API 网关
    docker-compose -f $COMPOSE_FILE up -d api-gateway
    
    sleep 15
    check_service_health "api-gateway" "8080"
    
    log_success "核心服务部署完成"
}

# 阶段3: 业务服务部署
deploy_business_services() {
    log_info "部署业务服务..."
    
    # 构建业务服务镜像
    build_service_images "inquiry-service purchase-service notification-service config-service"
    
    # 启动业务服务
    docker-compose -f $COMPOSE_FILE up -d inquiry-service purchase-service notification-service config-service
    
    log_info "等待业务服务启动..."
    sleep 30
    
    # 检查服务健康状态
    check_service_health "inquiry-service" "8004"
    check_service_health "purchase-service" "8005"
    check_service_health "notification-service" "8006"
    check_service_health "config-service" "8007"
    
    log_success "业务服务部署完成"
}

# 阶段4: 支撑服务部署
deploy_supporting_services() {
    log_info "部署支撑服务..."
    
    # 构建支撑服务镜像
    build_service_images "payment-service logistics-service analytics-service"
    
    # 启动支撑服务
    docker-compose -f $COMPOSE_FILE up -d payment-service logistics-service analytics-service
    
    log_info "等待支撑服务启动..."
    sleep 30
    
    # 检查服务健康状态
    check_service_health "payment-service" "8008"
    check_service_health "logistics-service" "8009"
    check_service_health "analytics-service" "8010"
    
    log_success "支撑服务部署完成"
}

# 构建服务镜像
build_service_images() {
    local services=$1
    
    log_info "构建服务镜像: $services"
    
    for service in $services; do
        log_info "构建 $service 镜像..."
        docker build -t banho-portal/$service:latest -f docker/$service/Dockerfile .
    done
    
    log_success "服务镜像构建完成"
}

# 检查服务健康状态
check_service_health() {
    local service=$1
    local port=$2
    local max_attempts=30
    local attempt=1
    
    log_info "检查 $service 健康状态..."
    
    while [ $attempt -le $max_attempts ]; do
        if curl -f http://localhost:$port/health &> /dev/null; then
            log_success "$service 健康检查通过"
            return 0
        fi
        
        log_warning "$service 健康检查失败，重试 $attempt/$max_attempts..."
        sleep 10
        ((attempt++))
    done
    
    log_error "$service 健康检查失败，请检查服务状态"
    return 1
}

# 初始化数据库
init_databases() {
    log_info "初始化数据库..."
    
    # 等待 MySQL 启动
    sleep 20
    
    # 创建数据库和用户
    docker-compose -f $COMPOSE_FILE exec mysql-user mysql -uroot -pbanho123 -e "
        CREATE DATABASE IF NOT EXISTS user_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        GRANT ALL PRIVILEGES ON user_db.* TO 'banho'@'%';
    "
    
    docker-compose -f $COMPOSE_FILE exec mysql-product mysql -uroot -pbanho123 -e "
        CREATE DATABASE IF NOT EXISTS product_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        GRANT ALL PRIVILEGES ON product_db.* TO 'banho'@'%';
    "
    
    docker-compose -f $COMPOSE_FILE exec mysql-order mysql -uroot -pbanho123 -e "
        CREATE DATABASE IF NOT EXISTS order_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        CREATE DATABASE IF NOT EXISTS inquiry_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        CREATE DATABASE IF NOT EXISTS purchase_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        GRANT ALL PRIVILEGES ON *.* TO 'banho'@'%';
    "
    
    log_success "数据库初始化完成"
}

# 配置 Consul
setup_consul() {
    log_info "配置 Consul..."
    
    # 等待 Consul 启动
    sleep 10
    
    # 导入配置
    curl -X PUT -d 'Banho B2B Portal' http://localhost:8500/v1/kv/banho-portal/config/app_name
    curl -X PUT -d '2.0.0' http://localhost:8500/v1/kv/banho-portal/config/app_version
    curl -X PUT -d 'production' http://localhost:8500/v1/kv/banho-portal/config/app_env
    
    log_success "Consul 配置完成"
}

# 运行健康检查
run_health_check() {
    log_info "运行系统健康检查..."
    
    echo ""
    echo "==================== 服务状态 ===================="
    docker-compose -f $COMPOSE_FILE ps
    echo ""
    
    echo "==================== 健康检查 ===================="
    
    # 检查 API 网关
    if curl -f http://localhost:8080/health &> /dev/null; then
        log_success "API 网关: 正常"
    else
        log_error "API 网关: 异常"
    fi
    
    # 检查核心服务
    check_service_health "user-service" "8001"
    check_service_health "product-service" "8002"
    check_service_health "order-service" "8003"
    
    # 检查监控服务
    if curl -f http://localhost:3000 &> /dev/null; then
        log_success "Grafana: 正常 (http://localhost:3000)"
    else
        log_error "Grafana: 异常"
    fi
    
    if curl -f http://localhost:9090 &> /dev/null; then
        log_success "Prometheus: 正常 (http://localhost:9090)"
    else
        log_error "Prometheus: 异常"
    fi
    
    echo "=================================================="
}

# 显示访问信息
show_access_info() {
    echo ""
    echo -e "${GREEN}==================== 访问信息 ====================${NC}"
    echo -e "${BLUE}API 网关:${NC}        http://localhost:8080"
    echo -e "${BLUE}用户服务:${NC}        http://localhost:8001"
    echo -e "${BLUE}产品服务:${NC}        http://localhost:8002"
    echo -e "${BLUE}订单服务:${NC}        http://localhost:8003"
    echo -e "${BLUE}Consul UI:${NC}       http://localhost:8500"
    echo -e "${BLUE}Grafana:${NC}         http://localhost:3000 (admin/admin123)"
    echo -e "${BLUE}Prometheus:${NC}      http://localhost:9090"
    echo -e "${BLUE}Jaeger:${NC}          http://localhost:16686"
    echo -e "${BLUE}RabbitMQ:${NC}        http://localhost:15672 (banho/banho123)"
    echo ""
    echo -e "${GREEN}==================================================${NC}"
}

# 清理环境
cleanup() {
    log_warning "清理环境..."
    
    docker-compose -f $COMPOSE_FILE down -v
    docker system prune -f
    
    log_success "环境清理完成"
}

# 显示帮助信息
show_help() {
    echo "万方商事 B2B 采购门户微服务架构快速启动脚本"
    echo ""
    echo "使用方法:"
    echo "  $0 [环境] [阶段] [选项]"
    echo ""
    echo "环境:"
    echo "  dev     - 开发环境 (默认)"
    echo "  staging - 测试环境"
    echo "  prod    - 生产环境"
    echo ""
    echo "阶段:"
    echo "  infra     - 基础设施阶段 (默认)"
    echo "  core      - 核心服务阶段"
    echo "  business  - 业务服务阶段"
    echo "  full      - 完整部署"
    echo ""
    echo "选项:"
    echo "  --cleanup  - 清理环境"
    echo "  --health   - 运行健康检查"
    echo "  --help     - 显示帮助信息"
    echo ""
    echo "示例:"
    echo "  $0 dev infra"
    echo "  $0 staging full"
    echo "  $0 prod core"
    echo "  $0 --cleanup"
}

# 主函数
main() {
    show_banner
    
    # 解析参数
    ENVIRONMENT=${1:-dev}
    STAGE=${2:-infra}
    
    # 处理特殊选项
    case $1 in
        "--cleanup")
            cleanup
            exit 0
            ;;
        "--health")
            run_health_check
            show_access_info
            exit 0
            ;;
        "--help")
            show_help
            exit 0
            ;;
    esac
    
    # 检查依赖
    check_dependencies
    
    # 设置环境
    setup_environment $ENVIRONMENT
    
    # 创建目录
    create_directories
    
    # 生成配置
    generate_configs
    
    # 根据阶段执行部署
    case $STAGE in
        "infra")
            deploy_infrastructure
            ;;
        "core")
            deploy_infrastructure
            deploy_core_services
            ;;
        "business")
            deploy_infrastructure
            deploy_core_services
            deploy_business_services
            ;;
        "full")
            deploy_infrastructure
            deploy_core_services
            deploy_business_services
            deploy_supporting_services
            ;;
        *)
            log_error "未知阶段: $STAGE"
            show_help
            exit 1
            ;;
    esac
    
    # 运行健康检查
    run_health_check
    
    # 显示访问信息
    show_access_info
    
    log_success "微服务架构部署完成！"
}

# 脚本入口
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi