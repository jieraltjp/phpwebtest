# 万方商事 B2B 采购门户 DevOps 操作指南

## 📋 概述

本文档详细介绍了万方商事 B2B 采购门户的 DevOps 流程，包括 CI/CD 流水线、容器化部署、监控告警、代码质量工具和部署自动化。

## 🚀 CI/CD 流水线

### GitHub Actions 工作流

#### 主要工作流文件

1. **`.github/workflows/ci-cd.yml`** - 完整的 CI/CD 流水线
2. **`.github/workflows/docker-build.yml`** - Docker 镜像构建和推送

#### 流水线阶段

1. **代码质量检查**
   - Laravel Pint 代码风格检查
   - PHPStan 静态分析 (Level 8)
   - Psalm 类型检查
   - Composer 安全扫描
   - ESLint JavaScript 代码检查

2. **测试套件**
   - PHPUnit 单元测试和集成测试
   - 前端测试 (Vitest)
   - 代码覆盖率报告
   - 测试结果上传

3. **构建和部署**
   - Docker 镜像构建
   - 多环境部署 (staging/production)
   - 健康检查
   - 部署通知

4. **安全扫描**
   - Trivy 漏洞扫描
   - Semgrep 代码安全分析
   - 依赖安全检查

#### 触发条件

```yaml
on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]
  release:
    types: [ published ]
```

#### 环境变量配置

在 GitHub 仓库设置中配置以下 Secrets：

```bash
# 应用配置
APP_KEY=your_app_key
JWT_SECRET=your_jwt_secret

# 数据库配置
DB_HOST_staging=staging_db_host
DB_USERNAME_staging=staging_user
DB_PASSWORD_staging=staging_password
DB_DATABASE_staging=banho_b2b_staging

DB_HOST_production=prod_db_host
DB_USERNAME_production=prod_user
DB_PASSWORD_production=prod_password
DB_DATABASE_production=banho_b2b_production

# Redis 配置
REDIS_HOST_staging=staging_redis_host
REDIS_PASSWORD_staging=staging_redis_password

REDIS_HOST_production=prod_redis_host
REDIS_PASSWORD_production=prod_redis_password

# Docker 配置
DOCKER_USERNAME=your_docker_username
DOCKER_PASSWORD=your_docker_password

# 部署配置
HOST_staging=staging.manpou.jp
HOST_production=prod.manpou.jp
SSH_USERNAME=deploy
SSH_KEY=your_private_ssh_key

# 通知配置
SLACK_WEBHOOK_URL=your_slack_webhook_url
```

## 🐳 容器化部署

### Docker 配置文件

#### 主要配置文件

1. **`Dockerfile`** - 多阶段构建配置
2. **`docker-compose.yml`** - 生产环境服务编排
3. **`docker-compose.override.yml`** - 开发环境覆盖配置

#### 服务架构

```yaml
services:
  app:          # PHP 应用
  nginx:        # 反向代理
  mysql:        # 数据库
  redis:        # 缓存
  queue:        # 队列处理器
  scheduler:    # 任务调度器
  prometheus:   # 监控
  grafana:      # 可视化
  elasticsearch: # 日志存储
  kibana:       # 日志可视化
```

#### 环境配置

##### 生产环境启动

```bash
# 复制环境配置
cp .env.production.example .env.production

# 编辑配置文件
vim .env.production

# 启动服务
docker-compose -f docker-compose.yml --env-file .env.production up -d
```

##### 开发环境启动

```bash
# 使用覆盖配置自动启动开发工具
docker-compose up -d

# 包含的开发工具:
# - Mailpit (邮件测试)
# - Redis Commander (Redis 管理)
# - Adminer (数据库管理)
# - Xdebug (调试)
# - Node.js 热重载
```

#### 性能优化配置

##### Nginx 配置

- Gzip 压缩
- 静态文件缓存
- 速率限制
- 安全头部
- 负载均衡

##### MySQL 配置

- InnoDB 优化
- 查询缓存
- 连接池配置
- 慢查询日志

##### Redis 配置

- 内存策略
- 持久化配置
- 安全设置
- 集群支持

## 📊 监控告警系统

### Prometheus 配置

#### 主要配置文件

1. **`docker/prometheus/prometheus.yml`** - 主配置文件
2. **`docker/prometheus/alert_rules.yml`** - 系统告警规则
3. **`docker/prometheus/business_rules.yml`** - 业务指标告警

#### 监控目标

```yaml
scrape_configs:
  - job_name: 'banho-b2b-app'      # 应用程序
  - job_name: 'nginx'              # Web 服务器
  - job_name: 'php-fpm'            # PHP-FPM
  - job_name: 'mysql'              # 数据库
  - job_name: 'redis'              # 缓存
  - job_name: 'business-metrics'   # 业务指标
  - job_name: 'blackbox-http'      # 外部服务监控
```

#### 告警规则

##### 系统告警

- CPU 使用率 > 80%
- 内存使用率 > 85%
- 磁盘空间 > 80%
- 服务宕机

##### 应用告警

- 响应时间 > 2s
- 错误率 > 5%
- 队列积压 > 100
- 认证失败率 > 20%

##### 业务告警

- 用户注册率异常
- 订单创建率下降
- 支付失败率过高
- 库存不足

### Grafana 仪表板

#### 访问地址

- **Grafana**: http://localhost:3001
- **默认账号**: admin / admin123

#### 预配置仪表板

1. **系统概览** - 基础设施监控
2. **应用性能** - 响应时间和错误率
3. **业务指标** - 用户和订单数据
4. **数据库监控** - MySQL 性能指标
5. **缓存监控** - Redis 使用情况

## 🔍 代码质量工具

### PHP 静态分析

#### PHPStan 配置

```bash
# 运行分析
vendor/bin/phpstan analyse

# 配置文件
phpstan.neon  # Level 8 严格分析
```

#### 特性

- 严格类型检查
- 死代码检测
- 依赖分析
- 自定义规则

#### Psalm 配置

```bash
# 运行分析
vendor/bin/psalm

# 配置文件
psalm.xml  # 类型安全检查
```

#### 特性

- 类型推断
- 内存泄漏检测
- 可变变量检查
- 混合类型分析

### 代码格式化

#### PHP CS Fixer

```bash
# 检查代码风格
vendor/bin/php-cs-fixer fix --dry-run --diff

# 自动修复
vendor/bin/php-cs-fixer fix

# 配置文件
.php-cs-fixer.php  # PSR-12 + Laravel 规则
```

#### 规则集

- PSR-12 标准
- Laravel 规则集
- PHP 8+ 特性
- 严格规则

### 前端质量工具

#### ESLint 配置

```bash
# 检查 JavaScript 代码
npm run lint

# 自动修复
npm run lint:fix
```

#### Prettier 配置

```bash
# 格式化代码
npm run format

# 检查格式
npm run format:check
```

## 🚀 部署自动化

### 部署脚本

#### Linux/macOS 部署

```bash
# 部署到 staging 环境
./scripts/deploy.sh staging

# 部署到生产环境
./scripts/deploy.sh production

# 回滚到最新备份
./scripts/deploy.sh production rollback

# 回滚到指定备份
./scripts/deploy.sh production rollback banho-b2b-20231201-143022
```

#### Windows 部署

```powershell
# 部署到 staging 环境
.\scripts\deploy.ps1 -Environment staging

# 部署到生产环境
.\scripts\deploy.ps1 -Environment production

# 回滚操作
.\scripts\deploy.ps1 -Environment production -Rollback
```

### 部署流程

1. **前置检查**
   - Docker 环境
   - 磁盘空间
   - 内存检查

2. **备份当前版本**
   - 数据库备份
   - 应用数据备份
   - 配置文件备份

3. **拉取最新代码**
   - Git 操作
   - 依赖安装

4. **构建和部署**
   - 镜像构建
   - 服务启动
   - 数据库迁移

5. **健康检查**
   - API 健康检查
   - 服务状态验证

6. **清理和通知**
   - 旧备份清理
   - 部署通知

### 环境配置

#### Staging 环境

```bash
# 环境配置
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.manpou.jp

# 数据库
DB_HOST=staging-db.manpou.jp
DB_DATABASE=banho_b2b_staging

# 缓存
REDIS_HOST=staging-redis.manpou.jp
```

#### Production 环境

```bash
# 环境配置
APP_ENV=production
APP_DEBUG=false
APP_URL=https://manpou.jp

# 数据库
DB_HOST=prod-db.manpou.jp
DB_DATABASE=banho_b2b_production

# 缓存
REDIS_HOST=prod-redis.manpou.jp
```

## 🧪 性能测试

### Artillery 负载测试

#### 测试配置

```yaml
# tests/performance/load-test.yml
config:
  target: http://localhost:8000
  phases:
    - duration: 60
      arrivalRate: 5
      name: "Warm up"
    - duration: 300
      arrivalRate: 20
      name: "Normal load"
```

#### 运行测试

```bash
# 安装 Artillery
npm install -g artillery

# 运行负载测试
artillery run tests/performance/load-test.yml

# 生成报告
artillery run tests/performance/load-test.yml --output report.json
artillery report report.json
```

#### 测试场景

1. **健康检查** - 基础 API 可用性
2. **用户认证** - 登录/注册流程
3. **产品浏览** - 搜索和详情页面
4. **订单管理** - 创建和查询订单
5. **询价业务** - 询价创建和管理
6. **批量采购** - 大批量请求处理

## 🔧 故障排除

### 常见问题

#### Docker 问题

```bash
# 查看容器状态
docker-compose ps

# 查看容器日志
docker-compose logs app
docker-compose logs nginx
docker-compose logs mysql

# 重启服务
docker-compose restart app

# 重建容器
docker-compose up -d --force-recreate
```

#### 数据库问题

```bash
# 检查数据库连接
docker-compose exec mysql mysql -u root -p

# 查看慢查询
docker-compose exec mysql mysql -u root -p -e "SHOW FULL PROCESSLIST;"

# 优化表
docker-compose exec mysql mysql -u root -p -e "OPTIMIZE TABLE orders;"
```

#### 缓存问题

```bash
# 检查 Redis 状态
docker-compose exec redis redis-cli ping

# 清理缓存
docker-compose exec redis redis-cli FLUSHALL

# 查看内存使用
docker-compose exec redis redis-cli INFO memory
```

#### 应用问题

```bash
# 查看应用日志
docker-compose exec app tail -f storage/logs/laravel.log

# 清理应用缓存
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear

# 检查队列状态
docker-compose exec app php artisan queue:failed
```

### 监控和日志

#### 查看监控指标

- **Prometheus**: http://localhost:9090
- **Grafana**: http://localhost:3001
- **Kibana**: http://localhost:5601

#### 日志聚合

```bash
# 查看 Nginx 访问日志
docker-compose exec nginx tail -f /var/log/nginx/access.log

# 查看 MySQL 错误日志
docker-compose exec mysql tail -f /var/log/mysql/error.log

# 查看 Redis 日志
docker-compose exec redis tail -f /var/log/redis/redis-server.log
```

## 📚 最佳实践

### 安全实践

1. **定期更新依赖**
   ```bash
   composer update
   npm update
   ```

2. **安全扫描**
   ```bash
   composer audit
   npm audit
   ```

3. **密钥管理**
   - 使用环境变量
   - 定期轮换密钥
   - 使用密钥管理服务

### 性能优化

1. **数据库优化**
   - 索引优化
   - 查询优化
   - 连接池配置

2. **缓存策略**
   - Redis 集群
   - 多级缓存
   - 缓存预热

3. **CDN 配置**
   - 静态资源 CDN
   - 图片优化
   - 压缩配置

### 备份策略

1. **数据库备份**
   ```bash
   # 每日自动备份
   0 2 * * * /scripts/backup-database.sh
   ```

2. **应用备份**
   - 代码版本控制
   - 配置文件备份
   - 用户数据备份

3. **灾难恢复**
   - RTO/RPO 目标
   - 恢复流程文档
   - 定期演练

## 📞 支持联系

如有 DevOps 相关问题，请联系：

- **技术支持**: support@manpou.jp
- **运维团队**: ops@manpou.jp
- **紧急热线**: +81-50-1234-5678

---

**更新时间**: 2024年12月4日  
**版本**: v2.0.0  
**维护者**: 万方商事株式会社 DevOps 团队