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

# 万方商事 B2B 采购门户项目指南

## 项目概述

这是一个基于 Laravel 12 框架开发的高端 B2B 采购门户系统，为万方商事客户提供完整的阿里巴巴商品采购功能。项目采用日式企业级设计美学，包含 RESTful API、用户仪表板、管理后台、Swagger 文档、询价系统、批量采购功能和精美的和风首页。

**品牌定位**：万方商事株式会社 (BANHO TRADING CO., LTD.) - 专业B2B贸易服务商
**官网地址**：https://manpou.jp/

**设计特色**：高端企业级日式设计，融合日本传统美学元素（墨黑、金色、樱花），提供专业的商业用户体验。

**项目阶段**：MVP完成 → 产品优化期 → 多系统扩展期，已完成企业级首页重新设计，新增多系统架构规划，整合完整README文档和测试工具。

**项目名称**: banho-b2b-portal (版本 2.0.0)
**项目类型**: 企业级 B2B 采购门户系统

## 技术栈

### 后端技术
- **框架**: Laravel 12 (PHP 8.2+)
- **认证系统**: JWT (tymon/jwt-auth)
- **数据库**: SQLite (开发环境) / MySQL (生产环境)
- **缓存**: Redis + 文件缓存
- **队列**: Laravel Queue + Redis
- **API文档**: Swagger/OpenAPI 3.0
- **测试框架**: PHPUnit 11.5.3
- **代码质量**: Laravel Pint 1.24
- **异常处理**: 全局异常处理器
- **响应标准化**: ApiResponseService
- **缓存服务**: CacheService
- **输入验证**: ValidationService
- **品牌配置**: BanhoConfigService (万方商事品牌管理)

### 前端技术
- **模板引擎**: Blade + Bootstrap 5
- **构建工具**: Vite 5.4.0
- **CSS框架**: Tailwind CSS 4.0
- **图表库**: Chart.js 4.4.0
- **字体系统**: Noto Sans JP + Noto Serif JP
- **设计系统**: 万方商事企业级设计系统 (Banho Theme)
- **JavaScript库**: Axios, Concurrently 9.0.1

### 开发工具
- **包管理**: Composer 2.0 + npm
- **代码格式化**: Prettier 3.2.0, ESLint
- **构建分析**: vite-bundle-analyzer 0.7.0
- **测试工具**: Vitest 1.0.0
- **版本控制**: Git
- **并发处理**: Concurrently (多服务并行)

## 项目结构

```
phpwebtest/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/              # API 控制器
│   │   │   ├── AuthController.php      # 认证控制器
│   │   │   ├── BulkPurchaseController.php # 批量采购控制器
│   │   │   ├── InquiryController.php   # 询价控制器
│   │   │   ├── OrderController.php      # 订单控制器
│   │   │   └── ProductController.php    # 产品控制器
│   │   ├── Admin/            # 管理员控制器
│   │   │   └── AdminController.php     # 管理员控制器
│   │   ├── DashboardController.php     # 仪表板控制器
│   │   ├── AuthController.php          # Web认证控制器
│   │   ├── BanhoController.php         # 万方商事品牌控制器
│   │   ├── SwaggerController.php       # Swagger文档控制器
│   │   └── TestController.php          # 测试控制器
│   ├── Http/Middleware/     # 中间件
│   │   ├── ApiThrottle.php            # API限流中间件
│   │   ├── CorsMiddleware.php          # CORS中间件
│   │   └── JwtMiddleware.php           # JWT中间件
│   ├── Models/               # 数据模型
│   │   ├── User.php                  # 用户模型
│   │   ├── Product.php               # 产品模型
│   │   ├── Order.php                 # 订单模型
│   │   ├── OrderItem.php             # 订单项模型
│   │   ├── Shipment.php              # 物流模型
│   │   └── Inquiry.php               # 询价模型
│   ├── Services/            # 服务层
│   │   ├── ApiResponseService.php     # API响应标准化服务
│   │   ├── CacheService.php           # 缓存管理服务
│   │   ├── ValidationService.php      # 输入验证服务
│   │   └── BanhoConfigService.php     # 万方商事配置服务
│   ├── Exceptions/          # 异常处理
│   │   └── Handler.php               # 全局异常处理器
│   └── Providers/           # 服务提供者
│       └── AppServiceProvider.php     # 应用服务提供者
├── config/
│   ├── app.php              # 应用配置
│   ├── auth.php             # 认证配置
│   ├── cache.php            # 缓存配置
│   ├── database.php         # 数据库配置
│   ├── filesystems.php      # 文件系统配置
│   ├── jwt.php              # JWT 配置
│   ├── logging.php          # 日志配置
│   ├── mail.php             # 邮件配置
│   ├── queue.php            # 队列配置
│   ├── services.php         # 服务配置
│   ├── session.php          # 会话配置
│   ├── swagger.php          # Swagger 配置
│   └── tailwind.config.js   # Tailwind CSS 配置
├── database/
│   ├── factories/           # 数据工厂
│   │   └── UserFactory.php            # 用户工厂
│   ├── migrations/          # 数据库迁移文件
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_12_02_113851_create_products_table.php
│   │   ├── 2025_12_02_113856_create_orders_table.php
│   │   ├── 2025_12_02_113900_create_order_items_table.php
│   │   ├── 2025_12_02_113906_create_shipments_table.php
│   │   └── 2025_12_03_135034_create_inquiries_table.php
│   ├── seeders/            # 测试数据填充
│   │   ├── DatabaseSeeder.php         # 数据库填充器
│   │   ├── ProductSeeder.php          # 产品数据填充
│   │   └── UserSeeder.php             # 用户数据填充
│   └── database.sqlite       # SQLite数据库文件
├── resources/
│   ├── views/
│   │   ├── auth.blade.php         # 认证页面 (登录/注册)
│   │   ├── dashboard.blade.php    # 用户仪表板
│   │   ├── home.blade.php         # 和风首页
│   │   ├── banho-home.blade.php  # 万方商事企业首页
│   │   ├── banho-dashboard.blade.php # 万方商事仪表板
│   │   ├── orders.blade.php       # 订单管理页面
│   │   ├── products.blade.php     # 产品管理页面
│   │   ├── portal.blade.php       # 数字门户选择页
│   │   ├── welcome.blade.php      # Laravel 欢迎页
│   │   ├── admin/                 # 管理员界面
│   │   │   └── dashboard.blade.php # 管理员仪表板
│   │   └── swagger/               # API 文档界面
│   │       ├── index.blade.php    # 基础文档页面
│   │       └── interactive.blade.php # 交互式API文档
│   ├── css/
│   │   ├── app.css               # 主样式文件
│   │   ├── banho-theme.css       # 万方商事主题样式
│   │   └── japanese-effects.css  # 和风动效库
│   └── js/
│       ├── app.js                # 主 JavaScript 文件
│       ├── banho-portal.js       # 万方商事门户脚本
│       ├── bootstrap.js          # Bootstrap 初始化
│       ├── japanese-interactions.js # 和风交互库
│       └── performance-optimizations.js # 性能优化脚本
├── public/
│   ├── build/              # 构建输出目录
│   ├── css/
│   │   └── temp-banho.css        # 临时企业样式
│   ├── api/                # API 健康检查
│   │   └── health.php             # 健康检查端点
│   ├── index.php           # 应用入口
│   ├── sw.js               # Service Worker
│   └── router.php          # 路由器
├── routes/
│   ├── api.php             # API 路由
│   ├── console.php         # 控制台路由
│   └── web.php             # Web 路由
├── openspec/               # 规格说明文档
│   ├── AGENTS.md           # AI助手指南
│   ├── project.md          # 项目上下文
│   └── changes/            # 变更提案
│       └── add-b2b-purchasing-api/ # B2B采购API变更
├── storage/                # 存储目录
│   ├── app/                # 应用存储
│   ├── framework/          # 框架存储
│   └── logs/               # 日志存储
├── tests/                  # 测试目录
│   ├── Feature/            # 功能测试
│   │   ├── ExampleTest.php
│   │   └── Api/            # API测试
│   ├── Unit/               # 单元测试
│   │   └── ExampleTest.php
│   └── TestCase.php        # 测试基类
├── test/                   # 测试脚本
│   ├── index_temp.php
│   ├── start.php
│   ├── status.php
│   └── test.php
├── vendor/                 # Composer依赖
├── node_modules/           # NPM依赖
├── MULTI_SYSTEM_ARCHITECTURE.md # 多系统架构设计
├── PRODUCT_IMPROVEMENT_PLAN.md  # 产品改进计划
├── MULTI_ROLE_IMPROVEMENT_ANALYSIS.md # 多角色改进分析
├── API_DOCUMENTATION.md    # API文档
├── AGENTS.md               # AI助手指南
├── README.md               # 项目说明
├── test-pages.php          # 页面测试脚本
├── artisan                 # Laravel命令行工具
├── composer.json           # Composer配置
├── composer.lock           # Composer锁定文件
├── package.json            # NPM配置
├── package-lock.json       # NPM锁定文件
├── phpunit.xml             # PHPUnit配置
├── vite.config.js          # Vite配置
├── tailwind.config.js      # Tailwind CSS 配置
├── .env.example            # 环境变量示例
├── .gitignore              # Git忽略文件
├── .gitattributes          # Git属性
├── .editorconfig           # 编辑器配置
├── install-composer.bat    # Composer安装脚本
├── install-deps.bat        # 依赖安装脚本
├── quick-start.bat         # 快速启动脚本
└── start-server.bat        # 服务器启动脚本
```

## 开发命令

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

#### 🌐 系统访问地址

| 服务 | 地址 | 说明 |
|------|------|------|
| 数字门户 | http://localhost:8000/portal | 统一入口页面 |
| 和风门户 | http://localhost:8000/ | 传统和风界面 |
| 企业门户 | http://localhost:8000/banho | 现代企业界面 |
| 管理后台 | http://localhost:8000/admin | 系统管理界面 |
| API文档 | http://localhost:8000/docs | 接口文档中心 |
| 健康检查 | http://localhost:8000/api/health | 系统状态检查 |

### 环境设置
```bash
# 快速设置 (推荐)
composer run setup

# 手动设置
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build

# 填充测试数据
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=UserSeeder

# 运行新的询价表迁移
php artisan migrate

# Windows用户快速安装
install-composer.bat    # 安装Composer
install-deps.bat        # 安装所有依赖
quick-start.bat         # 一键启动所有服务
```

### 测试和质量检查
```bash
# 运行测试套件
composer run test
# 或
php artisan test

# 前端测试
npm run test              # 运行Vitest测试
npm run test:coverage     # 运行测试覆盖率分析

# 代码质量检查
php artisan pint          # PHP代码格式化
npm run lint              # JavaScript代码检查
npm run lint:fix          # 自动修复JavaScript代码问题
npm run format            # 代码格式化 (Prettier)

# 前端构建
npm run build             # 生产构建
npm run dev               # 开发构建 (热重载)
npm run preview           # 预览生产构建

# 构建分析
npm run analyze           # 分析构建包大小

# 清除缓存 (调试用)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## 核心功能模块

### 1. 认证系统 (JWT)
- 用户登录/登出
- 用户注册功能 🆕
- 令牌刷新机制
- 用户信息获取
- 实时用户名/邮箱可用性检查 🆕
- **双账户系统**:
  - 普通用户: `testuser` / `password123`
  - 管理员: `admin` / `admin123`
- 全局异常处理保护
- 登录优先设计，注册模态框 🆕
- JWT令牌配置:
  - JWT_SECRET: 3s2GBDB46N374s7zfPvLkb5oTQqsscZ0MN6hWLbu81bkYPJZmZ5icnLSqoRvPlJL
  - JWT_TTL: 60分钟
  - JWT_REFRESH_TTL: 20160分钟 (2周)
  - JWT_ALGORITHM: HS256

### 2. 产品管理
- 产品列表查询 (分页、筛选、搜索)
- 产品详情查看
- 库存管理与同步
- 多币种支持 (CNY/JPY)
- 高级搜索功能
- **预置测试产品数据**:
  - ALIBABA_SKU_A123: 日本客户专用 办公椅 (¥1,250.50)
  - ALIBABA_SKU_B456: 无线蓝牙键盘 (¥280.00)
  - ALIBABA_SKU_C789: USB-C 扩展坞 (¥189.99)
  - ALIBABA_SKU_D012: 笔记本电脑支架 (¥85.50)
  - ALIBABA_SKU_E345: 网络摄像头 (¥320.00)

### 3. 订单系统
- 订单创建 (多 SKU 支持)
- 订单状态实时追踪
- 物流信息集成
- 订单历史查询
- 订单筛选和搜索

### 4. 询价系统
- 询价创建和提交
- 询价状态管理 (待处理/已报价/已接受/已拒绝/已过期)
- 询价历史查询
- 报价管理和过期控制
- 联系信息管理
- 询价编号自动生成

### 5. 批量采购系统 🆕
- 批量采购订单创建 (支持50个SKU)
- 智能报价系统 (阶梯折扣)
- 批量采购历史记录
- 采购统计数据分析
- 库存自动扣减
- 折扣计算引擎 (2%-15%)

### 5. 用户界面系统

#### 用户仪表板 (`/dashboard`)
- **12个状态指示器**：实时数据展示
- **11个功能模块**：完整的用户操作界面
- 响应式设计，支持移动端
- 和风主题设计

#### 管理后台 (`/admin`)
- 实时系统监控
- 用户权限管理
- 数据统计图表
- 系统配置管理
- 操作日志查看

#### 订单管理 (`/orders`)
- 订单趋势分析图表
- 订单状态分布图
- 高级筛选功能
- 订单详情模态框
- 批量操作支持

#### 产品管理 (`/products`)
- 网格式产品展示
- 高级搜索和筛选
- 产品详情卡片
- 库存状态指示

#### 认证页面 (`/auth`, `/login`, `/register`) 🆕
- 登录优先设计，简化用户流程
- 模态框注册界面，支持ESC键和背景点击关闭
- 樱花飘落背景动画，和风美学设计
- 实时表单验证和错误提示
- 响应式设计，支持移动端滚动优化
- 用户名/邮箱可用性实时检查

#### 万方商事企业首页 (`/`, `/banho`) 🆕
- 企业级日式设计美学，专业商务风格
- 固定导航栏，滚动隐藏效果
- Hero区域：文案 + 快速注册表单
- 6大核心服务展示
- 实绩数据动态展示
- 完全响应式设计
- 平滑滚动和数字动画效果

#### 数字门户选择页 (`/portal`) 🆕
- 统一系统入口页面
- 四大系统导航卡片
- 企业级设计风格
- 加载动画效果

#### 和风首页 (`/`)
- 高端日式设计美学
- 樱花飘落动画
- 响应式布局
- SEO 优化

### 6. 缓存系统 🆕
- **多层缓存策略**: 短期/中期/长期缓存
- **智能缓存管理**: 自动缓存清理和预热
- **Redis 支持**: 高性能缓存存储
- **缓存统计**: 内存使用和命中率监控
- **产品缓存**: 热门产品数据缓存
- **用户数据缓存**: 订单和询价历史缓存
- **统计数据缓存**: 实时统计性能优化

### 7. 输入验证系统 🆕
- **统一验证服务**: ValidationService 封装
- **安全过滤**: HTML标签移除和特殊字符转义
- **自定义验证规则**: 银行卡、身份证、IP验证
- **批量验证**: 支持复杂业务逻辑验证
- **文件上传验证**: 类型和大小限制
- **多语言错误消息**: 中英文错误提示

### 8. 万方商事品牌系统 🆕
- **品牌配置API**: `/api/banho/*` 端点
- **多语言支持**: 日语/英语/中文
- **企业信息管理**: 公司名称、官网、联系方式
- **业务配置**: 货币、汇率、物流、支付
- **支持服务**: 营业时间、联系方式、响应时间
- **缓存管理**: 智能配置缓存和清理

### 9. API 文档系统
- 基础文档界面 (`/docs`)
- 交互式 API 文档 (`/docs/interactive`)
- OpenAPI 3.0 规范 (`/api/openapi`)
- 完整的 Swagger UI 集成
- JWT 认证自动注入
- 在线 API 测试功能
- 万方商事企业主题界面设计
- 详细的请求/响应示例

## 和风设计系统

### CSS 动效库 (`japanese-effects.css`)
- **动画效果**：浮动、脉冲、微光、文字发光
- **高级交互**：磁性按钮、波纹扩散、边框绘制
- **视差滚动**：多层次滚动效果
- **响应式优化**：完整的移动端适配
- **图表保护**：防止外部动效影响图表渲染

### JavaScript 交互库 (`japanese-interactions.js`)
- **鼠标跟踪**：3D 视差效果
- **滚动动效**：平滑滚动动画
- **手势支持**：触摸设备优化
- **性能优化**：防抖节流、懒加载
- **图表保护机制**：自动跳过图表容器的动效

### 设计特色
- **色彩系统**：墨黑、朱红、樱花粉、金色点缀
- **字体系统**：Noto Sans JP + Noto Serif JP
- **图案元素**：传统和纹、渐变效果
- **动画原则**：自然、流畅、有意义

## 图表系统

### Chart.js 集成
- **订单趋势图**：线性图表，支持时间序列
- **状态分布图**：环形图表，直观展示比例
- **保护机制**：防止视差效果干扰图表渲染
- **响应式设计**：自适应容器大小

### 图表保护机制
```css
/* 强制固定样式，防止被外部动效影响 */
.chart-container {
    transform: none !important;
    position: relative !important;
    will-change: auto;
}
```

```javascript
// 跳过图表容器的视差效果
if (element.closest('.chart-container') || element.closest('#swagger-ui')) {
    return;
}
```

## API 接口概览

### 认证接口
- `POST /api/auth/login` - 用户登录
- `POST /api/auth/register` - 用户注册 🆕
- `POST /api/auth/logout` - 用户登出
- `GET /api/auth/me` - 获取用户信息
- `POST /api/auth/refresh` - 刷新令牌
- `POST /api/auth/check-username` - 检查用户名可用性 🆕
- `POST /api/auth/check-email` - 检查邮箱可用性 🆕

### 产品接口
- `GET /api/products` - 产品列表 (支持分页、筛选)
- `GET /api/products/{id}` - 产品详情

### 订单接口
- `POST /api/orders` - 创建订单
- `GET /api/orders` - 订单列表
- `GET /api/orders/{id}` - 订单详情
- `GET /api/orders/{id}/tracking-link` - 物流追踪链接

### 询价接口
- `POST /api/inquiries` - 创建询价
- `GET /api/inquiries` - 询价列表
- `GET /api/inquiries/{id}` - 询价详情

### 批量采购接口 🆕
- `POST /api/bulk-purchase` - 创建批量采购订单
- `POST /api/bulk-purchase/quote` - 获取批量采购报价
- `GET /api/bulk-purchase/history` - 批量采购历史
- `GET /api/bulk-purchase/statistics` - 批量采购统计

### 管理员接口
- `GET /api/admin/stats` - 管理员统计数据
- `GET /api/admin/users` - 用户管理
- `GET /api/admin/orders` - 订单管理
- `GET /api/admin/system-status` - 系统状态
- `GET /api/admin/activities` - 活动日志

### 万方商事配置接口 🆕
- `GET /api/banho/config` - 获取完整配置
- `GET /api/banho/brand` - 获取品牌信息
- `GET /api/banho/language` - 获取语言配置
- `GET /api/banho/business` - 获取业务配置
- `GET /api/banho/support` - 获取支持配置
- `POST /api/banho/exchange-rate` - 汇率转换
- `POST /api/banho/clear-cache` - 清除配置缓存

### 系统接口
- `GET /api/health` - 健康检查
- `GET /api/test/*` - 测试接口 (仅开发环境)

## API 响应格式

### 标准响应结构 🆕
```json
{
    "status": "success|error",
    "message": "响应消息",
    "data": {}, // 响应数据 (成功时)
    "errors": {}, // 错误详情 (验证失败时)
    "timestamp": "2025-12-03T13:50:34.000000Z"
}
```

### ApiResponseService 使用 🆕
```php
// 成功响应
ApiResponseService::success($data, '操作成功', 201);

// 错误响应
ApiResponseService::error('操作失败', $errors, 400);

// 验证错误
ApiResponseService::validationError($validator->errors());

// 分页响应
ApiResponseService::paginated($data, $pagination);
```

## 异常处理系统 🆕

### 全局异常处理器
- **统一格式**: 所有API异常返回标准JSON格式
- **分类处理**: 验证、认证、授权、未找到等不同异常类型
- **详细日志**: 记录异常详情用于调试
- **用户友好**: 提供清晰的错误信息

### 异常类型处理
```php
// 验证异常 - 422
ValidationException::class

// 认证异常 - 401
AuthenticationException::class

// 授权异常 - 403
AuthorizationException::class

// 未找到异常 - 404
ModelNotFoundException::class

// 服务器异常 - 500
Exception::class
```

## 开发约定

### 代码风格
- 遵循 PSR-12 标准
- 使用 Laravel 代码规范
- 方法名使用 camelCase
- 类名使用 PascalCase
- CSS 类名使用 kebab-case

### 数据库约定
- 表名使用复数 snake_case
- 字段名使用 snake_case
- 主键统一为 `id`
- 时间戳字段: `created_at`, `updated_at`
- 外键命名: `{table}_id`

### API 设计
- RESTful 风格
- 统一的 JSON 响应格式 (ApiResponseService)
- 适当的 HTTP 状态码
- JWT 认证保护
- 全局异常处理

### 服务层设计
- 使用服务类封装业务逻辑
- ApiResponseService 统一响应格式
- CacheService 缓存管理
- ValidationService 输入验证
- 遵循单一职责原则
- 支持依赖注入

### 缓存策略 🆕
- **产品缓存**: 长期缓存 (24小时)
- **用户数据**: 短期缓存 (5分钟)
- **统计数据**: 短期缓存 (5分钟)
- **搜索结果**: 短期缓存 (1小时)
- **智能清理**: 数据变更时自动清理相关缓存
- **预热机制**: 系统启动时预加载热点数据

### 安全验证 🆕
- **输入过滤**: HTML标签移除，特殊字符转义
- **SQL注入防护**: ORM 查询，参数绑定
- **XSS防护**: 输出转义，CSP 头设置
- **CSRF防护**: Laravel 内置 CSRF 令牌
- **文件上传安全**: 类型验证，大小限制
- **密码强度**: 复杂度要求，哈希存储

### 前端约定
- Bootstrap 5 组件优先
- 响应式设计第一
- 日语本地化支持
- 无障碍访问考虑
- 和风设计一致性

### 图表开发规范
- 所有图表容器必须添加 `chart-container` 类
- 使用 CSS 保护机制防止 transform 干扰
- JavaScript 中跳过图表容器的动效处理
- 延迟初始化确保 DOM 完全加载

## 部署注意事项

### 生产环境配置
1. **环境变量设置**
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - 配置生产数据库连接

2. **安全配置**
   - 更改 JWT 密钥
   - 启用 HTTPS
   - 配置 CORS 策略
   - 设置防火墙规则

3. **性能优化**
   - 启用 OPcache
   - 配置 Redis 缓存
   - 优化数据库查询
   - 静态资源 CDN

### 构建部署
```bash
# 生产构建
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 运行数据库迁移
php artisan migrate --force
```

## 故障排除

### 常见问题解决

#### API 相关问题 🆕
- **响应格式不一致**: 确认使用 ApiResponseService
- **异常处理异常**: 检查 Handler.php 配置
- **验证失败**: 检查请求参数和验证规则

#### 图表相关问题
- **图表位置异常**：检查是否正确应用了图表保护CSS
- **图表不显示**：确认 Chart.js 库正确加载
- **图表动画卡顿**：检查是否有冲突的 transform 样式

#### 认证问题
- **JWT 令牌无效**：检查 `.env` 中的 `JWT_SECRET` 配置
- **登录失败**：确认用户数据已正确填充
- **权限错误**：检查中间件配置

#### 数据库问题
- **连接错误**：确认数据库文件权限和路径
- **迁移失败**：检查数据库表结构冲突
- **数据缺失**：运行数据填充命令

#### 前端资源问题
- **样式不生效**：运行 `npm run build` 重新构建
- **JavaScript 错误**：检查浏览器控制台
- **动效异常**：确认 japanese-effects.css 正确加载

### 调试命令
```bash
# 清除所有缓存
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 查看路由列表
php artisan route:list

# 数据库状态检查
php artisan migrate:status
php artisan db:show

# 测试特定功能
php artisan test --filter OrderTest

# 查看最新迁移
php artisan migrate:status
```

## 多系统架构规划 🆕

项目已完成多系统集成架构设计，包含以下系统扩展规划：

### 核心系统
- **博客系统**: 内容营销和知识分享平台
- **购物系统**: 电商功能和商品销售平台  
- **公司官网**: 企业形象展示门户
- **实时聊天系统**: 类似阿里旺旺的客户沟通工具
- **统一通知系统**: 跨系统消息推送服务

### 技术架构
- **微服务架构**: 模块化设计，独立部署
- **统一用户中心**: SSO单点登录系统
- **API网关**: 统一路由和认证管理
- **数据同步**: 分布式事务，事件驱动

详细架构设计请参考 `MULTI_SYSTEM_ARCHITECTURE.md`。

## 版本历史

### v2.0.0 (最新) 🆕
- ✅ 项目版本升级至 2.0.0，企业级架构完善 🏢
- ✅ 更新前端构建工具链：Vite 5.4.0 + Tailwind CSS 4.0 ⚡
- ✅ 新增完整测试工具链：Vitest 1.0.0 + 代码覆盖率分析 🧪
- ✅ 完善代码质量工具：ESLint + Prettier + 构建分析 🔧
- ✅ 优化项目结构，新增中间件和工厂类支持 📁
- ✅ 完善OpenSpec规格管理系统，支持变更提案 📋
- ✅ 更新Swagger配置，支持RAKUMART × 1688 B2B采购门户 📚
- ✅ 优化依赖管理，使用日本镜像源提升下载速度 🚀
- ✅ 完善测试脚本和页面诊断工具 🔍
- ✅ 更新项目文档，保持与README.md同步 📝

### v1.7.0
- ✅ 完整整合README文档，包含所有账号密码和配置信息 📋
- ✅ 新增数字门户系统，提供统一入口和导航 🚪
- ✅ 添加管理员账户和详细测试数据信息 👤
- ✅ 完善启动命令和快速启动脚本 🚀
- ✅ 添加Windows用户专用批处理文件 🪟
- ✅ 重新设计企业级首页，统一万方商事品牌形象 🎨
- ✅ 实现企业级设计系统，专业商务风格 🏢
- ✅ 新增万方商事品牌配置API和管理服务 🆕
- ✅ 添加多语言支持 (日语/英语/中文) 🌐
- ✅ 完成多系统集成架构设计 📋
- ✅ 优化响应式设计和交互体验 📱
- ✅ 添加页面测试脚本和错误排查工具 🔧
- ✅ 修复CacheService语法错误，解决方法重复定义问题 🐛
- ✅ 全面检查代码语法，确保所有PHP文件无语法错误 ✅
- ✅ 验证所有核心功能正常运行，API端点响应正常 🟢

### v1.6.0
- ✅ 重新设计企业级首页，统一万方商事品牌形象 🎨
- ✅ 实现企业级设计系统，专业商务风格 🏢
- ✅ 新增万方商事品牌配置API和管理服务 🆕
- ✅ 添加多语言支持 (日语/英语/中文) 🌐
- ✅ 完成多系统集成架构设计 📋
- ✅ 优化响应式设计和交互体验 📱
- ✅ 添加页面测试脚本和错误排查工具 🔧

### v1.5.0
- ✅ 优化认证页面设计，登录优先用户体验
- ✅ 新增用户注册功能和模态框界面
- ✅ 实现交互式 Swagger API 文档系统
- ✅ 添加完整 OpenAPI 3.0 注解和示例
- ✅ 集成 JWT 认证自动注入机制
- ✅ 修复注册页面滚动和响应式问题
- ✅ 完善用户名/邮箱实时验证功能

### v1.4.0
- ✅ 新增批量采购功能 (BulkPurchaseController)
- ✅ 实现高级缓存系统 (CacheService)
- ✅ 添加统一输入验证服务 (ValidationService)
- ✅ 集成 Tailwind CSS 4.0 构建工具
- ✅ 优化并发开发环境 (Concurrently)
- ✅ 完善安全验证和输入过滤机制
- ✅ 添加智能折扣计算引擎
- ✅ 实现缓存预热和统计功能

### v1.3.0
- ✅ 修复路由配置冗余，优化代码结构
- ✅ 修正控制器命名空间错误
- ✅ 实施API响应格式标准化 (ApiResponseService)
- ✅ 添加全局异常处理系统
- ✅ 新增询价功能模块
- ✅ 创建Inquiry数据模型和数据库迁移
- ✅ 完善多角色改进分析中的高优先级项目

### v1.2.0
- ✅ 修复管理员后台图表位置异常bug
- ✅ 添加图表保护机制
- ✅ 完善和风设计系统
- ✅ 优化移动端响应式体验

### v1.1.0
- ✅ 添加订单管理页面
- ✅ 集成 Chart.js 图表系统
- ✅ 实现高级搜索功能
- ✅ 优化用户体验

### v1.0.0
- ✅ 基础框架搭建
- ✅ JWT 认证系统
- ✅ 产品管理功能
- ✅ API 文档系统

## Git 仓库

**主仓库**: https://github.com/jieraltjp/phpwebtest

**分支策略**:
- `master` - 生产环境代码
- `develop` - 开发环境代码
- `feature/*` - 功能分支

**重要提交**:
- `af22222` - 实施多角色改进分析中的高优先级改进
- `2ccd36c` - 添加多角色改进分析文档
- `563cd63` - 解决IFLOW.md合并冲突，保留更新的项目文档

## 扩展开发指南

在添加新功能时，请遵循以下流程：

1. **规格说明检查**
   - 查看 `openspec/` 目录中的现有规格
   - 检查 `openspec/changes/` 中的变更提案
   - 参考 `MULTI_ROLE_IMPROVEMENT_ANALYSIS.md` 中的改进建议

2. **创建变更提案**
   - 参考 `openspec/AGENTS.md` 中的流程
   - 提交详细的变更说明

3. **开发实施**
   - 遵循项目代码规范
   - 使用 ApiResponseService 统一响应格式
   - 添加相应的测试
   - 更新文档

4. **质量保证**
   - 运行完整测试套件
   - 检查代码风格 (php artisan pint)
   - 验证功能完整性
   - 确保异常处理覆盖

## 多角色改进分析 🆕

项目已完成专业程序开发团队的多角色分析，包含以下角色的改进建议：

### 已实施的高优先级改进
- **技术债务清理**: 路由配置优化，命名空间修正
- **API标准化**: 统一响应格式，全局异常处理
- **功能增强**: 询价系统实现
- **性能优化**: 高级缓存策略，查询优化
- **安全增强**: 输入验证服务，安全过滤机制
- **功能扩展**: 批量采购系统，折扣计算引擎
- **用户体验优化**: 登录优先设计，注册流程简化 🆕
- **开发者体验**: 交互式API文档，在线测试功能 🆕

### 待实施的中优先级改进
- **API限流**: 接口访问频率控制
- **权限管理**: 基于角色的访问控制
- **数据分析**: 高级业务报表
- **国际化**: 多语言支持
- **移动端优化**: PWA 支持

详细内容请参考 `MULTI_ROLE_IMPROVEMENT_ANALYSIS.md` 和 `PRODUCT_IMPROVEMENT_PLAN.md`。

## 联系支持

- **技术问题**: 在 GitHub 仓库创建 Issue
- **功能请求**: 通过 OpenSpec 流程提交提案
- **文档问题**: 提交文档改进 PR
- **改进建议**: 参考多角色改进分析文档

### 📞 完整联系信息

**万方商事株式会社 (BANHO TRADING CO., LTD.)**
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

**支持服务**
```bash
# ========================================
# 支持范围
# ========================================
✅ 系统部署和配置指导
✅ API接口使用支持
✅ 数据迁移和备份
✅ 性能优化建议
✅ 安全配置咨询
✅ 响应时间承诺 (P0/P1/P2级别)

# ========================================
# 响应时间承诺
# ========================================
🔴 紧急问题 (P0): 30分钟内响应
🟡 重要问题 (P1): 2小时内响应
🟢 一般问题 (P2): 24小时内响应
```

**紧急支持**
```bash
# ========================================
# 24/7紧急支持
# ========================================
📞 日本紧急热线: +81-50-1234-5678 (24/7)
📞 中国紧急热线: +86-21-1234-5678 (24/7)
📞 国际紧急热线: +1-646-1234-5678 (24/7)

📧 紧急支持: emergency@manpou.jp
📧 安全事件: security@manpou.jp

💬 紧急客服: https://manpou.jp/emergency
📱 紧急微信: banho-emergency
```

---

**注意**: 本项目采用万方商事企业级设计理念，在开发新功能时请保持品牌一致性和用户体验的完整性。所有新功能应使用 ApiResponseService 进行响应标准化，并确保全局异常处理覆盖。

**最新特性**: v2.0.0 版本完成了项目架构全面升级，引入现代化的前端构建工具链和完整的测试工具链，为开发团队提供了更高效的开发体验和更强的代码质量保障。

**核心升级**:
- ✅ 项目版本升级至 2.0.0，企业级架构完善 🏢
- ✅ 前端构建工具升级：Vite 5.4.0 + Tailwind CSS 4.0 ⚡
- ✅ 测试工具链完善：Vitest 1.0.0 + 代码覆盖率分析 🧪
- ✅ 代码质量工具：ESLint + Prettier + 构建分析 🔧
- ✅ 依赖管理优化：使用日本镜像源提升下载速度 🚀

**功能增强**:
- ✅ 数字门户系统 (/portal) - 统一入口页面，支持四大系统导航
- ✅ 管理员账户 (admin/admin123) - 系统管理员权限
- ✅ 完整测试产品数据 - 5个预置产品详细信息
- ✅ JWT令牌配置 - 完整的JWT配置和有效期说明
- ✅ Windows批处理脚本 - 快速启动和安装脚本
- ✅ 完整联系信息 - 万方商事所有联系方式和支持渠道
- ✅ 响应时间承诺 - 不同优先级问题的响应时间

**开发体验**:
- ✅ OpenSpec规格管理系统 - 支持变更提案和版本控制 📋
- ✅ Swagger文档升级 - 支持RAKUMART × 1688 B2B采购门户 📚
- ✅ 项目结构优化 - 新增中间件和工厂类支持 📁
- ✅ 测试脚本完善 - 页面诊断和错误排查工具 🔍

**文档整合**:
- ✅ README.md - 包含完整的使用指南和配置信息
- ✅ IFLOW.md - 与README保持同步的项目指南
- ✅ 测试账户信息 - 双账户系统完整信息
- ✅ 启动命令 - 从基础到高级的完整命令集合
- ✅ 环境配置 - 详细的.env配置说明
- ✅ 部署指南 - 从开发到生产的完整流程

**多系统扩展**: 项目已完成博客系统、购物系统、公司官网、实时聊天系统和通知系统的完整架构设计，为未来的业务扩展提供了清晰的技术路线图。

**文档完善**: 现在拥有完整的README文档和IFLOW.md项目指南，包含从安装到部署的全流程说明，以及详细的账号密码和配置信息。