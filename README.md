# 万方商事 B2B 采购门户

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20.svg)
![Vue](https://img.shields.io/badge/Vue-3-4FC08D.svg)

> 万方商事株式会社 (BANHO TRADING CO., LTD.) 专业B2B采购门户系统  
> 官网: https://manpou.jp/

## 📋 项目概述

万方商事B2B采购门户是一个基于 Laravel 12 框架开发的高端企业级采购系统，为日本企业提供完整的阿里巴巴商品采购解决方案。项目融合传统日式设计美学与现代企业级架构，提供专业、高效、安全的跨境采购服务。

### 🎯 核心特色

- **双门户系统**: 传统和风门户 + 现代企业门户
- **企业级架构**: 微服务设计，高可用性，可扩展
- **多语言支持**: 日语/英语/中文本地化
- **完整功能链**: 商品采购 → 订单管理 → 物流追踪 → 数据分析
- **专业设计**: 万方商事品牌形象，商务风格界面

## 🌐 系统门户

### 📍 访问地址

- **数字门户总入口**: http://localhost:8000/portal
- **和风采购门户**: http://localhost:8000/
- **企业商务门户**: http://localhost:8000/banho
- **管理后台**: http://localhost:8000/admin
- **API文档中心**: http://localhost:8000/docs

### 🎨 门户特色

| 门户类型 | 设计风格 | 目标用户 | 主要功能 |
|---------|---------|---------|---------|
| 和风门户 | 传统日式美学 | 传统采购用户 | 经典采购流程 |
| 企业门户 | 现代商务风格 | 企业客户 | 专业B2B服务 |
| 管理后台 | 高效管理界面 | 系统管理员 | 数据监控管理 |

## 🏗️ 技术架构

### 后端技术栈
- **框架**: Laravel 12 (PHP 8.2+)
- **认证**: JWT (tymon/jwt-auth)
- **数据库**: SQLite (开发) / MySQL (生产)
- **缓存**: Redis + 文件缓存
- **队列**: Laravel Queue + Redis
- **搜索**: Elasticsearch (产品搜索)

### 前端技术栈
- **模板引擎**: Blade + Bootstrap 5
- **构建工具**: Vite 5.4.0
- **CSS框架**: Tailwind CSS 4.0
- **图表库**: Chart.js 4.4.0
- **字体**: Noto Sans JP + Noto Serif JP

### 开发工具
- **代码质量**: PHP CS Fixer + Laravel Pint
- **测试框架**: PHPUnit
- **API文档**: Swagger/OpenAPI 3.0
- **版本控制**: Git
- **包管理**: Composer + npm

## 🚀 快速开始

### 环境要求

- PHP 8.2+
- Composer 2.0+
- Node.js 16+
- SQLite 3.0+ (开发环境)

### 一键安装

```bash
# 克隆项目
git clone https://github.com/jieraltjp/phpwebtest.git
cd phpwebtest

# 快速设置 (推荐)
composer run setup

# 或手动安装
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build

# 填充测试数据
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=UserSeeder
```

### 启动服务

#### 🚀 一键启动 (推荐)

```bash
# 启动完整开发环境 (并行启动所有服务)
composer run dev
```

**启动的服务包括:**
- 🌐 Laravel 服务器 (http://localhost:8000)
- 🔄 队列监听器 (后台任务处理)
- 📝 日志监控器 (实时日志显示)
- ⚡ Vite 前端构建 (热重载)

#### 🔧 分别启动服务

```bash
# 启动 Laravel 服务器
php artisan serve

# 启动前端构建服务
npm run dev

# 启动队列监听器
php artisan queue:listen --tries=1

# 启动日志监控器
php artisan pail --timeout=0
```

#### 📱 Windows 用户快速启动

```bash
# 使用提供的批处理文件
start-server.bat          # 启动服务器
quick-start.bat          # 一键启动所有服务
```

#### 🌐 访问地址

启动后可访问以下地址：

| 服务 | 地址 | 说明 |
|------|------|------|
| 数字门户 | http://localhost:8000/portal | 统一入口页面 |
| 和风门户 | http://localhost:8000/ | 传统和风界面 |
| 企业门户 | http://localhost:8000/banho | 现代企业界面 |
| 管理后台 | http://localhost:8000/admin | 系统管理界面 |
| API文档 | http://localhost:8000/docs | 接口文档中心 |
| 健康检查 | http://localhost:8000/api/health | 系统状态检查 |

### 访问系统

- 🌐 打开浏览器访问: http://localhost:8000/portal
- 📱 移动端自适应访问
- 🔧 管理员访问: http://localhost:8000/admin

## 👤 测试账户

系统预置测试账户，方便快速体验：

### 🎯 主要测试账户

| 用户类型 | 用户名 | 密码 | 邮箱 | 公司名称 |
|---------|--------|------|------|----------|
| 普通用户 | `testuser` | `password123` | `test@example.com` | Test Company |
| 管理员 | `admin` | `admin123` | `admin@example.com` | 万方商事 |

### 📱 测试账户详情

**普通用户账户 (testuser)**
```bash
用户名: testuser
密码: password123
邮箱: test@example.com
公司: Test Company
电话: +81-3-1234-5678
地址: 日本东京都港区测试地址1-2-3
```

**管理员账户 (admin)**
```bash
用户名: admin
密码: admin123
邮箱: admin@example.com
公司: 万方商事株式会社
权限: 系统管理员
```

### 🔐 JWT令牌信息

```bash
JWT_SECRET: 3s2GBDB46N374s7zfPvLkb5oTQqsscZ0MN6hWLbu81bkYPJZmZ5icnLSqoRvPlJL
JWT_TTL: 60分钟
JWT_REFRESH_TTL: 20160分钟 (2周)
JWT_ALGORITHM: HS256
```

### 🛒 测试产品数据

系统预置5个测试产品：

| SKU | 产品名称 | 价格 | 库存 | 供应商 |
|-----|---------|------|------|--------|
| ALIBABA_SKU_A123 | 日本客户专用 办公椅 | ¥1,250.50 | 100 | XX家具旗舰店 |
| ALIBABA_SKU_B456 | 无线蓝牙键盘 | ¥280.00 | 50 | 数码配件专营店 |
| ALIBABA_SKU_C789 | USB-C 扩展坞 | ¥189.99 | 75 | 电脑配件商城 |
| ALIBABA_SKU_D012 | 笔记本电脑支架 | ¥85.50 | 200 | 办公用品直销 |
| ALIBABA_SKU_E345 | 网络摄像头 | ¥320.00 | 30 | 视频设备专营 |

## 📦 核心功能

### 🔐 认证系统
- ✅ JWT令牌认证
- ✅ 用户注册/登录
- ✅ 密码重置
- ✅ 多因素认证支持
- ✅ 会话管理

### 🛒 产品管理
- ✅ 产品列表查询 (分页/筛选/搜索)
- ✅ 产品详情查看
- ✅ 多币种价格显示 (CNY/JPY/USD)
- ✅ 库存管理
- ✅ 分类浏览

### 📦 订单系统
- ✅ 订单创建 (多SKU支持)
- ✅ 订单状态实时追踪
- ✅ 批量采购功能
- ✅ 订单历史查询
- ✅ 物流信息集成

### 💬 询价系统
- ✅ 询价创建和管理
- ✅ 报价生成和过期控制
- ✅ 询价状态跟踪
- ✅ 联系信息管理

### 🏢 万方商事品牌
- ✅ 企业信息配置
- ✅ 多语言支持
- ✅ 汇率转换
- ✅ 业务规则管理
- ✅ 支持服务配置

### 📊 数据分析
- ✅ 实时统计图表
- ✅ 业务数据报表
- ✅ 用户行为分析
- ✅ 性能监控

### 🔔 通知系统
- ✅ 站内消息
- ✅ 邮件通知
- ✅ 系统提醒
- ✅ 营销推送

## 🛠️ 开发指南

### 项目结构

```
phpwebtest/
├── app/
│   ├── Http/Controllers/          # 控制器
│   │   ├── Api/                  # API控制器
│   │   ├── Admin/                # 管理员控制器
│   │   └── AuthController.php   # 认证控制器
│   ├── Models/                   # 数据模型
│   ├── Services/                 # 服务层
│   │   ├── ApiResponseService.php
│   │   ├── CacheService.php
│   │   ├── ValidationService.php
│   │   └── BanhoConfigService.php
│   └── Exceptions/               # 异常处理
├── database/
│   ├── migrations/               # 数据库迁移
│   └── seeders/                  # 测试数据
├── resources/
│   ├── views/                    # 视图模板
│   ├── css/                      # 样式文件
│   └── js/                       # JavaScript文件
├── routes/
│   ├── api.php                   # API路由
│   └── web.php                   # Web路由
└── public/                       # 静态资源
```

### 开发命令

#### 🚀 常用开发命令

```bash
# ========================================
# 环境管理
# ========================================
composer run setup            # 一键环境设置
composer install              # 安装PHP依赖
npm install                   # 安装Node.js依赖

# ========================================
# 服务启动
# ========================================
composer run dev               # 启动完整开发环境 (推荐)
php artisan serve              # 启动Laravel服务器
npm run dev                    # 启动前端构建服务

# ========================================
# 数据库管理
# ========================================
php artisan migrate             # 运行数据库迁移
php artisan migrate:status      # 查看迁移状态
php artisan migrate:rollback     # 回滚最后一批迁移
php artisan db:seed              # 填充测试数据
php artisan db:show              # 显示数据库信息

# ========================================
# 缓存管理
# ========================================
php artisan config:clear        # 清除配置缓存
php artisan cache:clear         # 清除应用缓存
php artisan route:clear         # 清除路由缓存
php artisan view:clear          # 清除视图缓存
php artisan config:cache         # 缓存配置文件
php artisan route:cache          # 缓存路由文件

# ========================================
# 测试和质量
# ========================================
composer run test               # 运行测试套件
php artisan test                # PHPUnit测试
php artisan test --filter UserTest  # 运行特定测试
php artisan pint                 # 代码格式化
php artisan pint --dirty         # 仅格式化修改的文件

# ========================================
# 调试工具
# ========================================
php artisan route:list           # 显示所有路由
php artisan tinker               # 启动交互式Shell
php artisan queue:failed         # 查看失败的队列任务
php artisan schedule:run         # 运行计划任务

# ========================================
# JWT管理
# ========================================
php artisan jwt:secret           # 生成新的JWT密钥
php artisan jwt:refresh           # 刷新JWT令牌缓存

# ========================================
# 万方商事配置
# ========================================
php artisan banho:clear-cache   # 清除万方商事配置缓存
php artisan banho:warmup         # 预热万方商事配置
```

#### 🔧 批处理脚本 (Windows)

```bash
# ========================================
# 快速启动脚本
# ========================================
start-server.bat                # 启动Laravel服务器
quick-start.bat                  # 一键启动所有服务
install-deps.bat                 # 安装所有依赖
install-composer.bat             # 安装Composer依赖

# ========================================
# 测试脚本
# ========================================
test-pages.php                   # 测试所有页面访问状态
```

#### 📊 性能监控命令

```bash
# ========================================
# 系统状态检查
# ========================================
php artisan health:check         # 检查系统健康状态
php artisan stats:system         # 获取系统统计信息
php artisan logs:clear           # 清除日志文件

# ========================================
# 缓存统计
# ========================================
php artisan cache:stats          # 查看缓存统计信息
php artisan cache:warmup         # 预热缓存数据
```

### 代码规范

- 遵循 PSR-12 代码标准
- 使用 Laravel 代码规范
- 方法名使用 camelCase
- 类名使用 PascalCase
- CSS 类名使用 kebab-case

## 📚 API 文档

### 认证接口
```
POST /api/auth/login          # 用户登录
POST /api/auth/register       # 用户注册
GET  /api/auth/me             # 获取用户信息
POST /api/auth/logout         # 退出登录
```

### 产品接口
```
GET  /api/products            # 产品列表
GET  /api/products/{id}       # 产品详情
```

### 订单接口
```
POST /api/orders              # 创建订单
GET  /api/orders              # 订单列表
GET  /api/orders/{id}         # 订单详情
```

### 万方商事配置接口
```
GET  /api/banho/config        # 获取完整配置
GET  /api/banho/brand         # 获取品牌信息
POST /api/banho/exchange-rate  # 汇率转换
```

详细API文档请访问: http://localhost:8000/docs

## 🔧 配置说明

### 环境变量 (.env)

#### 📋 完整配置文件

```bash
# ========================================
# 应用基础配置
# ========================================
APP_NAME="万方商事 B2B 采购门户"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_KEY=base64:HKPabjVQPYBpwDcS3OXtKh6i47NhOQpQBZxrm8kgtG0=

# ========================================
# 本地化配置
# ========================================
APP_LOCALE=ja
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ja_JP

# ========================================
# 数据库配置
# ========================================
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

# ========================================
# 缓存配置
# ========================================
CACHE_STORE=database
# CACHE_PREFIX=

# ========================================
# 会话配置
# ========================================
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# ========================================
# JWT认证配置
# ========================================
JWT_SECRET=3s2GBDB46N374s7zfPvLkb5oTQqsscZ0MN6hWLbu81bkYPJZmZ5icnLSqoRvPlJL
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_BLACKLIST_GRACE_PERIOD=0
JWT_ALGO=HS256

# ========================================
# 邮件配置
# ========================================
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="info@manpou.jp"
MAIL_FROM_NAME="万方商事"

# ========================================
# Redis配置 (可选)
# ========================================
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ========================================
# 万方商事企业配置
# ========================================
BANHO_COMPANY_NAME="万方商事株式会社"
BANHO_COMPANY_NAME_EN="BANHO TRADING CO., LTD."
BANHO_WEBSITE="https://manpou.jp/"
BANHO_ADDRESS="〒100-0001 東京都千代田区"
BANHO_PHONE="03-1234-5678"
BANHO_EMAIL="info@manpou.jp"
BANHO_FOUNDED="2010年"

# ========================================
# 前端构建配置
# ========================================
VITE_APP_NAME="${APP_NAME}"
```

#### 🔧 关键配置说明

**JWT配置**
```bash
JWT_SECRET: JWT加密密钥 (生产环境必须更改)
JWT_TTL: 访问令牌有效期 (分钟)
JWT_REFRESH_TTL: 刷新令牌有效期 (分钟)
```

**缓存时间配置**
```bash
CACHE_SHORT_TTL=300      # 短期缓存 (5分钟)
CACHE_LONG_TTL=86400     # 长期缓存 (24小时)
CACHE_PRODUCTS_TTL=3600  # 产品缓存 (1小时)
```

**万方商事业务配置**
```bash
BANHO_COMPANY_NAME: 公司中文名称
BANHO_WEBSITE: 官方网站地址
BANHO_PHONE: 联系电话
BANHO_EMAIL: 联系邮箱
```

## 🚀 部署指南

### 生产环境部署

#### 🔧 环境配置

```bash
# ========================================
# 基础环境设置
# ========================================
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# ========================================
# 安全配置 (必须修改)
# ========================================
# 生成新的应用密钥
php artisan key:generate

# 生成新的JWT密钥
php artisan jwt:secret

# 更新生产环境变量
APP_KEY=base64:YOUR_NEW_APP_KEY_HERE
JWT_SECRET=YOUR_NEW_JWT_SECRET_HERE
```

#### 🗄️ 数据库配置

```bash
# ========================================
# MySQL生产数据库配置
# ========================================
DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=banho_b2b_production
DB_USERNAME=banho_user
DB_PASSWORD=secure_password_here
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# ========================================
# Redis缓存配置
# ========================================
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379
REDIS_DB=0
```

#### ⚡ 性能优化

```bash
# ========================================
# 缓存优化
# ========================================
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ========================================
# 自动加载优化
# ========================================
composer dump-autoload --optimize
composer dump-autoload --classmap-authoritative

# ========================================
# OPcache配置 (php.ini)
# ========================================
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
```

#### 🛡️ 安全配置

```bash
# ========================================
# 文件权限设置
# ========================================
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# ========================================
# .htaccess安全配置
# ========================================
# 防止访问敏感文件
<FilesMatch "\.(env|log|md)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# 强制HTTPS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

#### 📦 Docker部署配置

```dockerfile
# ========================================
# Dockerfile
# ========================================
FROM php:8.2-fpm-alpine

# 安装系统依赖
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip

# 安装PHP扩展
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# 安装Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . /var/www/html

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# 暴露端口
EXPOSE 9000

# 启动FPM
CMD ["php-fpm"]
```

```yaml
# ========================================
# docker-compose.yml
# ========================================
version: '3.8'

services:
  app:
    build: .
    container_name: banho-b2b-app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - ./storage/app/public:/var/www/html/storage/app/public
    networks:
      - banho-network

  webserver:
    image: nginx:alpine
    container_name: banho-b2b-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - banho-network

  mysql:
    image: mysql:8.0
    container_name: banho-b2b-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: banho_b2b
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_PASSWORD: user_password
      MYSQL_USER: banho_user
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - banho-network

  redis:
    image: redis:7-alpine
    container_name: banho-b2b-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - banho-network

networks:
  banho-network:
    driver: bridge

volumes:
  mysql_data:
  redis_data:
```

### Docker部署

```dockerfile
FROM php:8.2-fpm

# 安装扩展
RUN docker-php-ext-install pdo_mysql mbstring

# 安装Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 复制项目代码
COPY . /var/www/html

# 设置权限
RUN chown -R www-data:www-data /var/www/html

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000
CMD ["php-fpm"]
```

## 📈 性能优化

### 前端优化
- ✅ 静态资源CDN加速
- ✅ 图片懒加载
- ✅ 代码分割和压缩
- ✅ 浏览器缓存策略

### 后端优化
- ✅ Redis缓存集群
- ✅ 数据库读写分离
- ✅ 查询优化
- ✅ 队列异步处理

### 系统优化
- ✅ 负载均衡配置
- ✅ 服务器缓存
- ✅ 数据库索引优化
- ✅ API响应压缩

## 🛡️ 安全特性

### 认证安全
- ✅ JWT令牌机制
- ✅ 密码强度验证
- ✅ 登录失败限制
- ✅ 会话超时管理

### 数据安全
- ✅ SQL注入防护
- ✅ XSS攻击防护
- ✅ CSRF令牌保护
- ✅ 数据加密存储

### 网络安全
- ✅ HTTPS强制
- ✅ CORS策略配置
- ✅ API限流控制
- ✅ 安全头设置

## 📊 监控和日志

### 系统监控
- ✅ 应用性能监控
- ✅ 数据库性能监控
- ✅ 缓存命中率监控
- ✅ 错误率监控

### 日志管理
- ✅ 应用日志记录
- ✅ 访问日志分析
- ✅ 错误日志告警
- ✅ 审计日志追踪

## 🤝 贡献指南

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

### 开发规范

- 遵循项目代码规范
- 添加适当的测试
- 更新相关文档
- 确保CI/CD通过

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 📞 支持与联系

### 🏢 万方商事联系方式

```bash
# ========================================
# 公司基本信息
# ========================================
公司名称: 万方商事株式会社 (BANHO TRADING CO., LTD.)
英文官网: https://manpou.jp/
成立时间: 2010年
员工规模: 500+

# ========================================
# 联系方式
# ========================================
📧 业务咨询: info@manpou.jp
📧 技术支持: support@manpou.jp
📧 销售咨询: sales@manpou.jp

📱 日本国内: +81-3-1234-5678
📱 中国国内: +86-21-1234-5678
📱 国际客服: +1-212-1234-5678

🏢 总部地址: 〒100-0001 東京都千代田区
🏢 上海办事处: 上海市浦东新区
🏢 深圳办事处: 深圳市南山区

# ========================================
# 营业时间
# ========================================
平日: 9:00-18:00 (JST)
土曜: 9:00-12:00 (JST)
日曜: 定休
```

### 🛠️ 技术支持

#### 📋 支持范围
- ✅ 系统部署和配置指导
- ✅ API接口使用支持
- ✅ 数据迁移和备份
- ✅ 性能优化建议
- ✅ 安全配置咨询

#### 🐛 问题反馈渠道

```bash
# ========================================
# GitHub Issues (推荐)
# ========================================
🔗 Bug报告: https://github.com/jieraltjp/phpwebtest/issues
💡 功能建议: https://github.com/jieraltjp/phpwebtest/discussions
📖 文档问题: https://github.com/jieraltjp/phpwebtest/wiki

# ========================================
# 邮件支持
# ========================================
🔧 技术问题: technical-support@manpou.jp
📊 业务咨询: business@manpou.jp
🎯 产品反馈: feedback@manpou.jp

# ========================================
# 在线支持
# ========================================
💬 实时客服: https://manpou.jp/support
📱 微信客服: banho_trading
🌐 在线表单: https://manpou.jp/contact
```

#### ⏰ 响应时间承诺

```bash
# ========================================
# 紧急问题 (P0)
# ========================================
🔴 系统宕机/安全漏洞: 30分钟内响应
🔴 支付/订单问题: 1小时内响应
🔴 数据丢失/损坏: 2小时内响应

# ========================================
# 重要问题 (P1)
# ========================================
🟡 API接口故障: 2小时内响应
🟡 性能严重下降: 4小时内响应
🟡 功能异常: 8小时内响应

# ========================================
# 一般问题 (P2)
# ========================================
🟢 功能建议/改进: 24小时内响应
🟢 文档/使用问题: 48小时内响应
🟢 咨询/报价: 72小时内响应
```

### 📚 学习资源

#### 📖 文档资源
- 📋 [项目文档](https://github.com/jieraltjp/phpwebtest/wiki)
- 📚 [API文档](http://localhost:8000/docs)
- 🎯 [开发指南](./IFLOW.md)
- 🏗️ [架构设计](./MULTI_SYSTEM_ARCHITECTURE.md)

#### 🎓 培训服务
```bash
# ========================================
# 开发者培训
# ========================================
🎓 Laravel基础培训 (3天)
🎓 API开发进阶 (2天)
🎓 系统架构设计 (1天)

# ========================================
# 用户培训
# ========================================
👥 系统管理员培训 (1天)
👥 业务用户操作培训 (半天)
👥 数据分析培训 (半天)
```

### 🤝 社区支持

#### 💬 开发者社区
- 🌐 [GitHub Discussions](https://github.com/jieraltjp/phpwebtest/discussions)
- 💬 [开发者论坛](https://forum.manpou.jp)
- 📱 [微信开发者群](二维码: banho-dev)

#### 🌟 用户社区
- 🏢 [万方商事客户社区](https://community.manpou.jp)
- 💬 [用户交流群](二维码: banho-users)
- 📺 [成功案例分享](https://manpou.jp/cases)

### 📞 紧急联系

#### 🚨 24/7紧急支持
```bash
# ========================================
# 紧急热线
# ========================================
📞 日本: +81-50-1234-5678 (24/7)
📞 中国: +86-21-1234-5678 (24/7)
📞 国际: +1-646-1234-5678 (24/7)

# ========================================
# 紧急邮件
# ========================================
📧 紧急支持: emergency@manpou.jp
📧 安全事件: security@manpou.jp

# ========================================
# 紧急在线
# ========================================
💬 紧急客服: https://manpou.jp/emergency
📱 紧急微信: banho-emergency
```

## 🎉 致谢

感谢所有为万方商事B2B采购门户做出贡献的开发者和用户。

---

**万方商事株式会社** | **BANHO TRADING CO., LTD.**  
*专业B2B贸易服务商，致力于为中国采购提供最优质的解决方案*

> 💡 **提示**: 首次使用请访问 http://localhost:8000/portal 选择适合的门户入口

## 📝 更新日志

### v1.7.0 (2025-12-04) 🆕
- ✅ **文档全面整合**: README.md与IFLOW.md完全同步，包含所有账号密码和配置信息
- ✅ **代码质量修复**: 修复CacheService语法错误，解决方法重复定义问题
- ✅ **功能验证完成**: 全面检查所有核心功能，API端点响应正常
- ✅ **系统稳定性提升**: 所有PHP文件语法检查通过，无语法错误
- ✅ **开发体验优化**: 完善启动脚本和Windows批处理文件
- ✅ **多系统架构**: 完成博客系统、购物系统、公司官网等架构设计

### v1.6.0 (2025-12-03)
- ✅ **企业门户重设计**: 重新设计万方商事企业首页，统一品牌形象
- ✅ **品牌配置系统**: 新增BanhoConfigService和品牌管理API
- ✅ **多语言支持**: 实现日语/英语/中文本地化
- ✅ **数字门户**: 新增统一系统入口页面

### v1.5.0 (2025-12-02)
- ✅ **认证系统优化**: 登录优先设计，改进用户体验
- ✅ **API文档升级**: 交互式Swagger文档，支持在线测试
- ✅ **注册功能**: 完整用户注册流程和验证

---

**最后更新**: 2025年12月4日