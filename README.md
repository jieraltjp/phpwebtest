# 雅虎 B2B 采购门户 API

基于 Laravel 12 框架开发的 B2B 采购门户 RESTful API，为雅虎客户提供完整的阿里巴巴商品采购功能。

## 功能特性

- ✅ JWT 用户认证系统
- ✅ 产品管理（列表查询、详情查看）
- ✅ 订单管理（创建、查询、状态追踪）
- ✅ 物流追踪集成
- ✅ 多币种支持（CNY/JPY）
- ✅ 分页和筛选功能
- ✅ 跨域请求支持

## 快速开始

### 环境要求

- PHP 8.2+
- Composer
- SQLite（或其他支持的数据库）

### 安装步骤

1. **克隆项目**
   ```bash
   git clone <repository-url>
   cd my-mbxj
   ```

2. **安装依赖**
   ```bash
   composer install
   ```

3. **环境配置**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **配置 JWT 密钥**
   编辑 `.env` 文件，设置 JWT 密钥：
   ```
   JWT_SECRET=your_jwt_secret_key_here_change_this_in_production
   ```

5. **数据库迁移**
   ```bash
   php artisan migrate
   ```

6. **填充测试数据**
   ```bash
   php artisan db:seed --class=ProductSeeder
   php artisan db:seed --class=UserSeeder
   ```

7. **启动服务**
   ```bash
   php artisan serve
   ```

8. **访问 API**
   - API 基础地址：`http://localhost:8000/api/v1`
   - 健康检查：`http://localhost:8000/api/health`

## 测试账户

系统已预置测试账户：

```
用户名: testuser
密码: password123
```

## 项目验证

项目已通过以下测试验证：

### ✅ 基础功能测试
- [x] Laravel 服务器启动成功
- [x] 数据库连接正常
- [x] 测试数据填充完成（1个用户，5个产品）
- [x] API 基础连接正常
- [x] 用户登录功能正常
- [x] 产品查询功能正常

### 🔧 测试命令

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

### ⚠️ 注意事项

1. **JWT 认证**: 由于 composer 包安装限制，当前使用简化认证。生产环境需要：
   ```bash
   composer require tymon/jwt-auth
   php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
   ```

2. **生产环境配置**:
   - 更改 `.env` 中的 JWT_SECRET
   - 配置生产数据库
   - 启用 HTTPS
   - 设置适当的缓存和队列

## API 接口

### 认证接口

- `POST /api/v1/auth/login` - 用户登录
- `POST /api/v1/auth/logout` - 退出登录
- `GET /api/v1/auth/me` - 获取当前用户信息
- `POST /api/v1/auth/refresh` - 刷新访问令牌

### 产品接口

- `GET /api/v1/products` - 获取产品列表
- `GET /api/v1/products/{id}` - 获取产品详情

### 订单接口

- `POST /api/v1/orders` - 创建新订单
- `GET /api/v1/orders` - 获取订单列表
- `GET /api/v1/orders/{id}` - 获取订单详情
- `GET /api/v1/orders/{id}/tracking-link` - 获取物流追踪链接

## 使用示例

### 1. 用户登录

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "password123"
  }'
```

### 2. 获取产品列表

```bash
curl -X GET http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 3. 创建订单

```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "items": [
      {
        "sku": "ALIBABA_SKU_A123",
        "quantity": 2
      }
    ],
    "shipping_address": "日本东京都港区测试地址1-2-3"
  }'
```

## 开发指南

### 项目结构

```
app/
├── Http/Controllers/Api/     # API 控制器
├── Models/                   # 数据模型
├── Http/Middleware/          # 中间件
database/
├── migrations/               # 数据库迁移
├── seeders/                  # 数据填充
routes/
└── api.php                   # API 路由定义
```

### 运行测试

```bash
php artisan test
```

### 代码格式化

```bash
php artisan pint
```

## API 文档

详细的 API 文档请参考：[API_DOCUMENTATION.md](./API_DOCUMENTATION.md)

## 技术栈

- **框架**: Laravel 12
- **认证**: JWT (tymon/jwt-auth)
- **数据库**: SQLite
- **HTTP 客户端**: Guzzle HTTP
- **测试**: PHPUnit

## 贡献指南

1. Fork 本项目
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 支持

如有问题或建议，请创建 [Issue](../../issues) 或联系开发团队。

---

**注意**: 这是 MVP 版本，仅包含核心功能。生产环境使用前请确保：
- 更改 JWT 密钥
- 配置生产数据库
- 启用 HTTPS
- 添加适当的错误监控