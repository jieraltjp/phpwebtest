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

这是一个基于 Laravel 12 框架开发的 B2B 采购门户系统，为雅虎客户提供完整的阿里巴巴商品采购功能。项目包含 RESTful API、用户仪表板、管理后台、Swagger 文档和日式首页。

## 技术栈

- **后端框架**: Laravel 12 (PHP 8.2+)
- **认证系统**: JWT (tymon/jwt-auth)
- **数据库**: SQLite (开发环境)
- **前端**: Bootstrap 5 + Blade 模板
- **构建工具**: Vite + Tailwind CSS
- **API 文档**: Swagger/OpenAPI 3.0
- **测试框架**: PHPUnit

## 项目结构

```
my-mbxj/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/              # API 控制器 (认证、产品、订单)
│   │   ├── Admin/            # 管理员控制器
│   │   ├── DashboardController.php
│   │   └── SwaggerController.php
│   ├── Models/               # 数据模型 (User, Product, Order, Shipment)
│   └── Providers/
├── config/
│   ├── jwt.php              # JWT 配置
│   └── swagger.php          # Swagger 配置
├── database/
│   ├── migrations/          # 数据库迁移文件
│   └── seeders/            # 测试数据填充
├── resources/views/
│   ├── dashboard.blade.php  # 用户仪表板
│   ├── home.blade.php       # 日式首页
│   ├── admin/               # 管理员界面
│   └── swagger/             # API 文档界面
├── routes/
│   ├── api.php              # API 路由
│   └── web.php              # Web 路由
└── openspec/                # 规格说明文档
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
```

### 开发服务器
```bash
# 启动 Laravel 开发服务器
php artisan serve

# 启动前端构建 (并行开发)
composer run dev
```

### 测试和质量检查
```bash
# 运行测试
php artisan test

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

### 2. 产品管理
- 产品列表查询 (分页、筛选)
- 产品详情查看
- 库存管理
- 多币种支持 (CNY/JPY)

### 3. 订单系统
- 订单创建 (多 SKU 支持)
- 订单状态追踪
- 物流信息集成
- 订单历史查询

### 4. 用户界面
- **用户仪表板**: 12个状态指示器，11个功能模块
- **管理后台**: 实时统计、系统监控、用户管理
- **日式首页**: RAKUMART 风格，SEO 优化

### 5. API 文档
- Swagger UI 界面 (`/docs`)
- OpenAPI 3.0 规范 (`/api/openapi`)
- 交互式 API 测试

## API 接口概览

### 认证接口
- `POST /api/auth/login` - 用户登录
- `POST /api/auth/logout` - 用户登出
- `GET /api/auth/me` - 获取用户信息
- `POST /api/auth/refresh` - 刷新令牌

### 产品接口
- `GET /api/products` - 产品列表
- `GET /api/products/{id}` - 产品详情

### 订单接口
- `POST /api/orders` - 创建订单
- `GET /api/orders` - 订单列表
- `GET /api/orders/{id}` - 订单详情
- `GET /api/orders/{id}/tracking-link` - 物流追踪

### 系统接口
- `GET /api/health` - 健康检查
- `GET /api/test/*` - 测试接口

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
- 统一的响应格式
- 适当的 HTTP 状态码
- JSON 数据交换

### 前端约定
- Bootstrap 5 组件
- 响应式设计
- 日语本地化支持
- 无障碍访问考虑

## 部署注意事项

1. **环境配置**
   - 设置正确的 `APP_ENV=production`
   - 配置生产数据库
   - 更改 JWT 密钥

2. **安全设置**
   - 启用 HTTPS
   - 配置 CORS
   - 设置适当的缓存策略

3. **性能优化**
   - 启用 OPcache
   - 配置 Redis 缓存
   - 优化数据库查询

## 故障排除

### 常见问题
- **JWT 认证失败**: 检查 `.env` 中的 `JWT_SECRET` 配置
- **数据库连接错误**: 确认数据库文件权限和路径
- **CSRF 错误**: API 路由已排除 CSRF 验证
- **静态资源 404**: 运行 `npm run build` 生成资源

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
```

## Git 仓库

项目已上传到: https://github.com/jieraltjp/phpwebtest

## 扩展开发

在添加新功能时，请参考 `openspec/AGENTS.md` 中的规格说明流程：

1. 检查现有规格和变更
2. 创建变更提案
3. 实施并测试
4. 更新文档

## 联系支持

如有问题或需要帮助，请查看项目文档或在仓库中创建 Issue。