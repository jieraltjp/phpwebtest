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

这是一个基于 Laravel 12 框架开发的高端 B2B 采购门户系统，为雅虎客户提供完整的阿里巴巴商品采购功能。项目采用日式设计美学，包含 RESTful API、用户仪表板、管理后台、Swagger 文档、询价系统和精美的和风首页。

**设计特色**：高端大气上档次，融合日本传统美学元素（樱花、和纸、墨黑、金色点缀），提供沉浸式的用户体验。

**项目阶段**：MVP完成 → 产品优化期，已完成多角色改进分析中的高优先级技术改进。

## 技术栈

- **后端框架**: Laravel 12 (PHP 8.2+)
- **认证系统**: JWT (tymon/jwt-auth)
- **数据库**: SQLite (开发环境)
- **前端框架**: Bootstrap 5 + Blade 模板
- **构建工具**: Vite
- **设计系统**: 自定义和风设计系统 (Japanese Effects)
- **图表库**: Chart.js
- **API 文档**: Swagger/OpenAPI 3.0
- **测试框架**: PHPUnit
- **异常处理**: 全局异常处理器
- **响应标准化**: ApiResponseService

## 项目结构

```
my-mbxj/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/              # API 控制器
│   │   │   ├── AuthController.php      # 认证控制器
│   │   │   ├── InquiryController.php   # 询价控制器 (新增)
│   │   │   ├── OrderController.php      # 订单控制器
│   │   │   └── ProductController.php    # 产品控制器
│   │   ├── Admin/            # 管理员控制器
│   │   │   └── AdminController.php     # 管理员控制器
│   │   ├── DashboardController.php     # 仪表板控制器
│   │   └── SwaggerController.php       # Swagger文档控制器
│   ├── Models/               # 数据模型
│   │   ├── User.php                  # 用户模型
│   │   ├── Product.php               # 产品模型
│   │   ├── Order.php                 # 订单模型
│   │   ├── OrderItem.php             # 订单项模型
│   │   ├── Shipment.php              # 物流模型
│   │   └── Inquiry.php               # 询价模型 (新增)
│   ├── Services/            # 服务层 (新增)
│   │   └── ApiResponseService.php     # API响应标准化服务
│   ├── Exceptions/          # 异常处理 (新增)
│   │   └── Handler.php               # 全局异常处理器
│   └── Providers/           # 服务提供者
├── config/
│   ├── jwt.php              # JWT 配置
│   └── swagger.php          # Swagger 配置
├── database/
│   ├── migrations/          # 数据库迁移文件
│   │   ├── 2025_12_03_135034_create_inquiries_table.php  # 询价表迁移 (新增)
│   │   └── ...               # 其他迁移文件
│   └── seeders/            # 测试数据填充
├── resources/
│   ├── views/
│   │   ├── dashboard.blade.php    # 用户仪表板 (12个状态指示器)
│   │   ├── home.blade.php         # 和风首页
│   │   ├── orders.blade.php       # 订单管理页面
│   │   ├── products.blade.php     # 产品管理页面
│   │   ├── welcome.blade.php      # Laravel 欢迎页
│   │   ├── admin/                 # 管理员界面
│   │   │   └── dashboard.blade.php # 管理员仪表板
│   │   └── swagger/               # API 文档界面
│   ├── css/
│   │   ├── app.css               # 主样式文件
│   │   └── japanese-effects.css  # 和风动效库
│   └── js/
│       ├── app.js                # 主 JavaScript 文件
│       ├── bootstrap.js          # Bootstrap 初始化
│       └── japanese-interactions.js # 和风交互库
├── routes/
│   ├── api.php              # API 路由
│   └── web.php              # Web 路由 (已优化)
└── openspec/                # 规格说明文档
```

## 开发命令

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
```

### 开发服务器
```bash
# 启动完整开发环境 (并行服务)
composer run dev

# 单独启动 Laravel 服务器
php artisan serve

# 单独启动前端构建
npm run dev
```

### 测试和质量检查
```bash
# 运行测试套件
composer run test
# 或
php artisan test

# 代码格式化
php artisan pint

# 前端生产构建
npm run build

# 清除缓存 (调试用)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## 核心功能模块

### 1. 认证系统 (JWT)
- 用户登录/登出
- 令牌刷新机制
- 用户信息获取
- 测试账户: `testuser` / `password123`
- 全局异常处理保护

### 2. 产品管理
- 产品列表查询 (分页、筛选、搜索)
- 产品详情查看
- 库存管理与同步
- 多币种支持 (CNY/JPY)
- 高级搜索功能

### 3. 订单系统
- 订单创建 (多 SKU 支持)
- 订单状态实时追踪
- 物流信息集成
- 订单历史查询
- 订单筛选和搜索

### 4. 询价系统 🆕
- 询价创建和提交
- 询价状态管理 (待处理/已报价/已接受/已拒绝/已过期)
- 询价历史查询
- 报价管理和过期控制
- 联系信息管理

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

#### 和风首页 (`/`)
- 高端日式设计美学
- 樱花飘落动画
- 响应式布局
- SEO 优化

### 6. API 文档系统
- Swagger UI 界面 (`/docs`)
- OpenAPI 3.0 规范 (`/api/openapi`)
- 交互式 API 测试
- 和风主题界面设计

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
- `POST /api/auth/logout` - 用户登出
- `GET /api/auth/me` - 获取用户信息
- `POST /api/auth/refresh` - 刷新令牌

### 产品接口
- `GET /api/products` - 产品列表 (支持分页、筛选)
- `GET /api/products/{id}` - 产品详情

### 订单接口
- `POST /api/orders` - 创建订单
- `GET /api/orders` - 订单列表
- `GET /api/orders/{id}` - 订单详情
- `GET /api/orders/{id}/tracking-link` - 物流追踪链接

### 询价接口 🆕
- `POST /api/inquiries` - 创建询价
- `GET /api/inquiries` - 询价列表
- `GET /api/inquiries/{id}` - 询价详情

### 管理员接口
- `GET /api/admin/stats` - 管理员统计数据
- `GET /api/admin/users` - 用户管理
- `GET /api/admin/orders` - 订单管理
- `GET /api/admin/system-status` - 系统状态
- `GET /api/admin/activities` - 活动日志

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

### 服务层设计 🆕
- 使用服务类封装业务逻辑
- ApiResponseService 统一响应格式
- 遵循单一职责原则
- 支持依赖注入

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

## 版本历史

### v1.3.0 (最新) 🆕
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

### 待实施的中优先级改进
- **性能优化**: 缓存策略，查询优化
- **安全增强**: API限流，输入验证
- **功能扩展**: 批量采购，权限管理

详细内容请参考 `MULTI_ROLE_IMPROVEMENT_ANALYSIS.md` 和 `PRODUCT_IMPROVEMENT_PLAN.md`。

## 联系支持

- **技术问题**: 在 GitHub 仓库创建 Issue
- **功能请求**: 通过 OpenSpec 流程提交提案
- **文档问题**: 提交文档改进 PR
- **改进建议**: 参考多角色改进分析文档

---

**注意**: 本项目采用高端和风设计理念，在开发新功能时请保持设计一致性和用户体验的完整性。所有新功能应使用 ApiResponseService 进行响应标准化，并确保全局异常处理覆盖。