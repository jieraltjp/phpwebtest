#!/bin/bash

# 万方商事 B2B 采购门户部署脚本
# 自动化部署和回滚机制

set -euo pipefail

# ================================
# 配置变量
# ================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
ENVIRONMENT="${1:-staging}"
BACKUP_DIR="/opt/backups/banho-b2b"
DEPLOY_USER="${DEPLOY_USER:-deploy}"
LOG_FILE="/var/log/banho-b2b-deploy.log"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ================================
# 日志函数
# ================================
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

# ================================
# 检查前置条件
# ================================
check_prerequisites() {
    log "检查部署前置条件..."
    
    # 检查 Docker 和 Docker Compose
    if ! command -v docker &> /dev/null; then
        error "Docker 未安装"
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        error "Docker Compose 未安装"
    fi
    
    # 检查环境变量
    if [[ ! -f "$PROJECT_ROOT/.env.$ENVIRONMENT" ]]; then
        error "环境配置文件不存在: .env.$ENVIRONMENT"
    fi
    
    # 检查磁盘空间
    local available_space
    available_space=$(df / | awk 'NR==2 {print $4}')
    if (( available_space < 2097152 )); then  # 2GB in KB
        warning "磁盘空间不足 2GB，可能影响部署"
    fi
    
    # 检查内存
    local available_memory
    available_memory=$(free -m | awk 'NR==2{printf "%.0f", $7}')
    if (( available_memory < 1024 )); then
        warning "可用内存不足 1GB，可能影响部署"
    fi
    
    success "前置条件检查完成"
}

# ================================
# 备份当前版本
# ================================
backup_current_version() {
    log "备份当前版本..."
    
    local backup_name="banho-b2b-$(date +%Y%m%d-%H%M%S)"
    local backup_path="$BACKUP_DIR/$backup_name"
    
    mkdir -p "$backup_path"
    
    # 备份数据库
    if docker-compose ps mysql | grep -q "Up"; then
        log "备份数据库..."
        docker-compose exec -T mysql mysqldump \
            -u root \
            -p"$MYSQL_ROOT_PASSWORD" \
            --single-transaction \
            --routines \
            --triggers \
            "$DB_DATABASE" > "$backup_path/database.sql"
        
        gzip "$backup_path/database.sql"
        success "数据库备份完成"
    fi
    
    # 备份应用数据
    if [[ -d "$PROJECT_ROOT/storage/app" ]]; then
        log "备份应用数据..."
        cp -r "$PROJECT_ROOT/storage/app" "$backup_path/"
        success "应用数据备份完成"
    fi
    
    # 备份配置文件
    if [[ -f "$PROJECT_ROOT/.env.$ENVIRONMENT" ]]; then
        cp "$PROJECT_ROOT/.env.$ENVIRONMENT" "$backup_path/"
    fi
    
    # 备份当前镜像标签
    if docker images | grep -q "banho/b2b-portal"; then
        docker images banho/b2b-portal --format "table {{.Repository}}:{{.Tag}}\t{{.CreatedAt}}" > "$backup_path/images.txt"
    fi
    
    # 创建符号链接指向最新备份
    ln -sfn "$backup_name" "$BACKUP_DIR/latest"
    
    success "备份完成: $backup_path"
}

# ================================
# 拉取最新代码
# ================================
pull_latest_code() {
    log "拉取最新代码..."
    
    cd "$PROJECT_ROOT"
    
    # 检查是否有未提交的更改
    if ! git diff-index --quiet HEAD --; then
        warning "检测到未提交的更改，将被覆盖"
    fi
    
    # 拉取最新代码
    git fetch origin
    git reset --hard "origin/$ENVIRONMENT"
    
    success "代码拉取完成"
}

# ================================
# 构建和部署
# ================================
build_and_deploy() {
    log "构建和部署应用..."
    
    cd "$PROJECT_ROOT"
    
    # 复制环境配置
    cp ".env.$ENVIRONMENT" .env
    
    # 停止旧服务
    log "停止旧服务..."
    docker-compose down
    
    # 拉取新镜像
    log "拉取新镜像..."
    docker-compose pull
    
    # 构建自定义镜像
    log "构建自定义镜像..."
    docker-compose build --no-cache
    
    # 启动服务
    log "启动新服务..."
    docker-compose up -d
    
    # 等待服务启动
    log "等待服务启动..."
    sleep 30
    
    success "应用部署完成"
}

# ================================
# 健康检查
# ================================
health_check() {
    log "执行健康检查..."
    
    local max_attempts=30
    local attempt=1
    local health_url="http://localhost:${APP_PORT:-8000}/api/health"
    
    while (( attempt <= max_attempts )); do
        if curl -f -s "$health_url" > /dev/null; then
            success "健康检查通过"
            return 0
        fi
        
        log "健康检查失败，重试 $attempt/$max_attempts..."
        sleep 10
        ((attempt++))
    done
    
    error "健康检查失败，部署可能存在问题"
}

# ================================
# 数据库迁移
# ================================
run_migrations() {
    log "执行数据库迁移..."
    
    cd "$PROJECT_ROOT"
    
    # 检查数据库连接
    if ! docker-compose exec -T mysql mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SELECT 1" "$DB_DATABASE" > /dev/null 2>&1; then
        error "数据库连接失败"
    fi
    
    # 执行迁移
    if ! docker-compose exec -T app php artisan migrate --force; then
        error "数据库迁移失败"
    fi
    
    # 清理缓存
    docker-compose exec -T app php artisan config:clear
    docker-compose exec -T app php artisan cache:clear
    docker-compose exec -T app php artisan route:clear
    docker-compose exec -T app php artisan view:clear
    
    success "数据库迁移完成"
}

# ================================
# 回滚功能
# ================================
rollback() {
    local backup_name="${1:-latest}"
    local backup_path="$BACKUP_DIR/$backup_name"
    
    log "开始回滚到备份: $backup_name"
    
    if [[ ! -d "$backup_path" ]]; then
        error "备份不存在: $backup_path"
    fi
    
    # 停止当前服务
    docker-compose down
    
    # 恢复数据库
    if [[ -f "$backup_path/database.sql.gz" ]]; then
        log "恢复数据库..."
        gunzip -c "$backup_path/database.sql.gz" | docker-compose exec -T mysql mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$DB_DATABASE"
    fi
    
    # 恢复应用数据
    if [[ -d "$backup_path/app" ]]; then
        log "恢复应用数据..."
        rm -rf "$PROJECT_ROOT/storage/app"
        cp -r "$backup_path/app" "$PROJECT_ROOT/storage/"
    fi
    
    # 恢复配置文件
    if [[ -f "$backup_path/.env.$ENVIRONMENT" ]]; then
        cp "$backup_path/.env.$ENVIRONMENT" "$PROJECT_ROOT/.env"
    fi
    
    # 启动服务
    docker-compose up -d
    
    # 健康检查
    sleep 30
    health_check
    
    success "回滚完成"
}

# ================================
# 清理旧备份
# ================================
cleanup_old_backups() {
    log "清理旧备份..."
    
    # 保留最近 10 个备份
    find "$BACKUP_DIR" -maxdepth 1 -type d -name "banho-b2b-*" | \
        sort -r | \
        tail -n +11 | \
        xargs -r rm -rf
    
    # 清理旧镜像
    docker image prune -f
    
    success "清理完成"
}

# ================================
# 发送通知
# ================================
send_notification() {
    local status="$1"
    local message="$2"
    
    # Slack 通知
    if [[ -n "${SLACK_WEBHOOK_URL:-}" ]]; then
        local color="good"
        if [[ "$status" == "error" ]]; then
            color="danger"
        elif [[ "$status" == "warning" ]]; then
            color="warning"
        fi
        
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"$message\", \"color\":\"$color\"}" \
            "$SLACK_WEBHOOK_URL" > /dev/null 2>&1 || true
    fi
    
    # 邮件通知
    if [[ -n "${DEPLOY_EMAIL:-}" ]]; then
        echo "$message" | mail -s "万方商事 B2B 采购门户部署通知" "$DEPLOY_EMAIL" || true
    fi
}

# ================================
# 主函数
# ================================
main() {
    local start_time=$(date +%s)
    
    log "开始部署到 $ENVIRONMENT 环境..."
    
    # 检查是否为回滚操作
    if [[ "${2:-}" == "rollback" ]]; then
        rollback "${3:-}"
        send_notification "success" "✅ 万方商事 B2B 采购门户回滚到 $ENVIRONMENT 环境成功"
        exit 0
    fi
    
    # 执行部署流程
    check_prerequisites
    backup_current_version
    pull_latest_code
    build_and_deploy
    run_migrations
    health_check
    cleanup_old_backups
    
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    
    success "部署完成，耗时: ${duration} 秒"
    send_notification "success" "✅ 万方商事 B2B 采购门户部署到 $ENVIRONMENT 环境成功，耗时: ${duration} 秒"
}

# ================================
# 错误处理
# ================================
trap 'error "部署过程中发生错误"; send_notification "error" "❌ 万方商事 B2B 采购门户部署到 $ENVIRONMENT 环境失败"' ERR

# ================================
# 执行主函数
# ================================
main "$@"