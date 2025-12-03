<!-- OPENSPEC:START -->
# OpenSpec Instructions

These instructions are for AI assistants working in this project.

Always open `@/openspec/AGENTS.md` when the request:
- Mentions planning or proposals (words like proposal, spec, change, plan)
- Introduces new capabilities, breaking changes, architecture shifts, or big performance/security work
- Sounds ambiguous and you need the authoritative spec before coding

Use `@/openspec/AGENTS.md` to learn:
- How to create and apply change proposals
- Spec format and conventions
- Project structure and guidelines

Keep this managed block so 'openspec update' can refresh the instructions.

<!-- OPENSPEC:END -->

# 雅虎 B2B 采购门户项目指南

## 项目概述

这是一个基于 Laravel 12 框架开发的 B2B 采购门户系统，为雅虎客户提供完整的阿里巴巴商品采购功能。项目包含 RESTful API、用户仪表板、管理后台、Swagger 文档和精美的日式首页设计。项目已完成 MVP 阶段，具备完整的业务流程和良好的用户体验。

## 技术栈

- **后端框架**: Laravel 12 (PHP 8.2+)
- **认证系统**: JWT (tymon/jwt-auth)
- **数据库**: SQLite (开发环境)
- **前端**: Bootstrap 5 + Blade 模板
- **构建工具**: Vite + Tailwind CSS 4.0
- **API 文档**: Swagger/OpenAPI 3.0
- **测试框架**: PHPUnit
- **样式系统**: 和风设计系统 (樱花动画、磁性按钮、视差滚动)

## 项目结构

```
phpwebtest/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/              # API 控制器 (认证、产品、订单)
│   │   ├── Admin/            # 管理员控制器
│   │   ├── DashboardController.php
│   │   ├── SwaggerController.php
│   │   └── TestController.php
│   ├── Models/               # 数据模型 (User, Product, Order, Shipment, OrderItem)
│   ├── Http/Middleware/      # 中间件 (CORS, JWT)
│   └── Providers/
├── config/
│   ├── jwt.php              # JWT 配置
│   └── swagger.php          # Swagger 配置
├── database/
│   ├── migrations/          # 数据库迁移文件
│   └── seeders/            # 测试数据填充
├── resources/
│   ├── views/
│   │   ├── dashboard.blade.php  # 用户仪表板
│   │   ├── home.blade.php       # 日式首页 (948行)
│   │   ├── products.blade.php   # 产品管理页面
│   │   ├── orders.blade.php     # 订单管理页面
│   │   ├── admin/               # 管理员界面
│   │   └── swagger/             # API 文档界面
│   ├── css/
│   │   ├── app.css              # 主样式文件
│   │   └── japanese-effects.css # 和风效果样式
│   └── js/
│       ├── app.js               # 主 JavaScript
│       └── japanese-interactions.js # 和风交互效果
├── routes/
│   ├── api.php              # API 路由 (带版本控制)
│   └── web.php              # Web 路由
├── openspec/                # 规格说明文档
│   ├── project.md
│   ├── AGENTS.md
│   └── changes/
│       └── add-b2b-purchasing-api/
└── tests/
    ├── Feature/             # 功能测试
    │   └── Api/
    │       └── AuthTest.php
    └── Unit/                # 单元测试
```

## 开发命令

### 环境设置
```bash
# 安装 PHP 依赖
composer install

# 安装前端依赖
npm install

# 环境配置
cp .env.example .env
php artisan key:generate

# 数据库迁移
php artisan migrate

# 填充测试数据
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=UserSeeder

# 一键设置 (推荐)
composer run setup
```

### 开发服务器
```bash
# 启动 Laravel 开发服务器
php artisan serve

# 启动前端构建 (并行开发)
composer run dev

# Windows 快速启动
start-server.bat
```

### 测试和质量检查
```bash
# 运行测试
php artisan test
composer run test

# 代码格式化
php artisan pint

# 前端构建
npm run build
```

## 核心功能模块

### 1. 认证系统 (JWT)
- 用户登录/登出
- 令牌刷新
- 用户信息获取
- 测试账户: `testuser` / `password123`
- JWT 中间件保护

### 2. 产品管理
- 产品列表查询 (分页、筛选)
- 产品详情查看
- 库存管理
- 多币种支持 (CNY/JPY)
- 高级搜索功能

### 3. 订单系统
- 订单创建 (多 SKU 支持)
- 订单状态追踪
- 物流信息集成
- 订单历史查询
- 批量操作支持

### 4. 用户界面
- **用户仪表板**: 12个状态指示器，11个功能模块
- **管理后台**: 实时统计、系统监控、用户管理
- **日式首页**: RAKUMART 风格，樱花动画，SEO 优化
- **产品管理**: 网格式展示，高级筛选
- **订单管理**: 图表可视化，状态追踪

### 5. API 文档
- Swagger UI 界面 (`/docs`)
- OpenAPI 3.0 规范 (`/api/openapi`)
- 交互式 API 测试
- 完整的 API 契约文档

### 6. 和风设计系统
- 樱花飘落动画效果
- 磁性按钮交互
- 视差滚动效果
- 和风配色方案
- 响应式设计

## API 接口概览

### 认证接口
- `POST /api/v1/auth/login` - 用户登录
- `POST /api/v1/auth/logout` - 用户登出
- `GET /api/v1/auth/me` - 获取用户信息
- `POST /api/v1/auth/refresh` - 刷新令牌

### 产品接口
- `GET /api/v1/products` - 产品列表
- `GET /api/v1/products/{id}` - 产品详情

### 订单接口
- `POST /api/v1/orders` - 创建订单
- `GET /api/v1/orders` - 订单列表
- `GET /api/v1/orders/{id}` - 订单详情
- `GET /api/v1/orders/{id}/tracking-link` - 物流追踪

### 管理员接口
- `GET /api/admin/stats` - 管理统计数据
- `GET /api/admin/users` - 用户管理
- `GET /api/admin/orders` - 订单管理
- `GET /api/admin/system-status` - 系统状态

### 系统接口
- `GET /api/health` - 健康检查
- `GET /api/test/*` - 测试接口

## 项目状态与评估

### ✅ 已完成的核心功能
- 技术架构: Laravel 12 + JWT认证系统 (评分: 8.5/10)
- 用户界面: 和风首页 + 仪表板 + 管理后台 (评分: 9.0/10)
- API功能: 认证 + 产品 + 订单系统 (评分: 7.5/10)
- 用户体验: 响应式设计 + 交互效果 (评分: 8.0/10)

### ⚠️ 需要调整的问题
- 路由配置冗余 (web.php 中存在重复路由定义)
- 管理员控制器命名空间错误 (部分路由引用了错误的命名空间)
- API响应格式不统一
- 安全性配置需要加强

### 📋 详细改进计划
完整的改进计划请参考: [PRODUCT_IMPROVEMENT_PLAN.md](./PRODUCT_IMPROVEMENT_PLAN.md)

## 开发约定

### 代码风格
- 遵循 PSR-12 标准
- 使用 Laravel 代码规范
- 方法名使用 camelCase
- 类名使用 PascalCase

### 数据库约定
- 表名使用复数 snake_case
- 字段名使用 snake_case
- 主键统一为 `id`
- 时间戳字段: `created_at`, `updated_at`

### API 设计
- RESTful 风格
- 版本控制 (/api/v1/)
- 统一的响应格式
- 适当的 HTTP 状态码
- JSON 数据交换

### 前端约定
- Bootstrap 5 组件
- 响应式设计
- 日语本地化支持
- 无障碍访问考虑
- 和风设计系统

## 测试与验证

### 测试账户
```
用户名: testuser
密码: password123
```

### 测试命令
```bash
# 启动服务器
php artisan serve

# 测试基础连接
curl http://localhost:8000/api/test

# 测试数据库连接
curl http://localhost:8000/api/test/database

# 测试产品查询
curl http://localhost:8000/api/test/products

# 测试登录
curl -X POST http://localhost:8000/api/test/login \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"password123"}'
```

### 已验证功能
- [x] Laravel 服务器启动成功
- [x] 数据库连接正常
- [x] 测试数据填充完成（1个用户，5个产品）
- [x] API 基础连接正常
- [x] 用户登录功能正常
- [x] 产品查询功能正常

## 部署注意事项

1. **环境配置**
   - 设置正确的 `APP_ENV=production`
   - 配置生产数据库 (MySQL/PostgreSQL)
   - 更改 JWT 密钥
   - 配置 HTTPS

2. **安全设置**
   - 启用 HTTPS
   - 配置 CORS 策略
   - 实施 API 限流
   - 加强输入验证

3. **性能优化**
   - 启用 OPcache
   - 配置 Redis 缓存
   - 优化数据库查询
   - 实施响应缓存

## 故障排除

### 常见问题
- **JWT 认证失败**: 检查 `.env` 中的 `JWT_SECRET` 配置
- **数据库连接错误**: 确认数据库文件权限和路径
- **CSRF 错误**: API 路由已排除 CSRF 验证
- **静态资源 404**: 运行 `npm run build` 生成资源
- **路由冲突**: 检查 web.php 中的重复路由定义

### 调试命令
```bash
# 清除缓存
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 查看路由
php artisan route:list

# 数据库状态
php artisan migrate:status

# 检查路由问题
php artisan route:list | findstr admin
```

## Git 仓库

项目已上传到: https://github.com/jieraltjp/phpwebtest

## OpenSpec 规格管理

项目使用 OpenSpec 进行规格驱动的开发流程：

```bash
# 查看现有规格
openspec list
openspec list --specs

# 创建变更提案
openspec proposal create [change-id]

# 验证规格
openspec validate [change-id] --strict

# 归档已完成变更
openspec archive <change-id> --yes
```

当前活跃变更:
- `add-b2b-purchasing-api`: 添加雅虎 B2B 采购门户 API

## 扩展开发

在添加新功能时，请参考 `openspec/AGENTS.md` 中的规格说明流程：

1. 检查现有规格和变更
2. 创建变更提案
3. 实施并测试
4. 更新文档

## 联系支持

如有问题或需要帮助，请查看项目文档或在仓库中创建 Issue。