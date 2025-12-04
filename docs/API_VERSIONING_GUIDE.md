# API 版本化管理指南

## 概述

万方商事 B2B 采购门户现已实施完整的 API 版本化管理，支持多版本并存、渐进式迁移和向后兼容性。

## 版本策略

### 当前版本状态

| 版本 | 状态 | 发布日期 | 弃用日期 | 停用日期 | 说明 |
|------|------|----------|----------|----------|------|
| v1.0 | Stable (稳定) | 2025-12-01 | - | - | 生产就绪版本，完全支持 |
| v2.0 | Preview (预览) | 2025-12-04 | - | - | 增强功能版本，测试阶段 |

### 版本生命周期

1. **Preview (预览)**: 新功能开发阶段，可能包含 breaking changes
2. **Stable (稳定)**: 生产就绪，向后兼容保证
3. **Deprecated (弃用)**: 不再推荐使用，提供迁移指南
4. **Sunset (停用)**: 不再支持，无法访问

## API 访问方式

### 1. URL 路径版本控制

```bash
# v1 API (稳定版本)
GET /api/v1/products
POST /api/v1/auth/login

# v2 API (预览版本)
GET /api/v2/products
POST /api/v2/auth/login
```

### 2. Accept Header 版本控制

```bash
# 使用 Accept header 指定版本
curl -H "Accept: application/vnd.banho.api+v1" https://api.manpou.jp/api/products
curl -H "Accept: application/vnd.banho.api+v2" https://api.manpou.jp/api/products
```

### 3. 自定义 Header 版本控制

```bash
# 使用自定义 header
curl -H "API-Version: v1" https://api.manpou.jp/api/products
curl -H "API-Version: v2" https://api.manpou.jp/api/products
```

## 版本管理端点

### 获取所有版本信息

```bash
GET /api/versions
```

**响应示例:**
```json
{
    "status": "success",
    "message": "API versions retrieved successfully",
    "data": {
        "versions": {
            "v1": {
                "version": "v1",
                "name": "Version 1.0",
                "status": "stable",
                "release_date": "2025-12-01",
                "deprecated": false,
                "description": "Initial API version with core B2B purchasing functionality",
                "features": [
                    "JWT Authentication",
                    "Product Management",
                    "Order Processing",
                    "Inquiry System",
                    "Bulk Purchase",
                    "Admin Functions"
                ],
                "breaking_changes": [],
                "endpoints_count": 15
            },
            "v2": {
                "version": "v2",
                "name": "Version 2.0",
                "status": "preview",
                "release_date": "2025-12-04",
                "deprecated": false,
                "description": "Enhanced API with improved performance and new features",
                "features": [
                    "Enhanced Authentication",
                    "Advanced Product Search",
                    "Real-time Order Tracking",
                    "Enhanced Inquiry Management",
                    "Advanced Analytics",
                    "Webhook Support",
                    "Rate Limiting",
                    "Response Compression"
                ],
                "breaking_changes": [
                    "Authentication header format changed",
                    "Response structure enhanced",
                    "Error codes standardized"
                ],
                "endpoints_count": 23
            }
        },
        "total": 2,
        "default_version": "v1",
        "latest_version": "v2",
        "supported_versions": ["v1", "v2"]
    }
}
```

### 获取特定版本信息

```bash
GET /api/versions/{version}
```

### 版本比较

```bash
POST /api/versions/compare
Content-Type: application/json

{
    "from_version": "v1",
    "to_version": "v2"
}
```

### 版本统计信息

```bash
GET /api/versions/statistics
```

### 版本健康检查

```bash
GET /api/versions/{version}/health
```

### 迁移指南

```bash
GET /api/versions/{version}/migration-guide
```

## 版本差异对比

### v1 vs v2 主要差异

#### 认证系统

| 功能 | v1 | v2 |
|------|----|----|
| 基础登录 | ✅ | ✅ |
| 用户注册 | ✅ | ✅ (增强) |
| 邮箱验证 | ❌ | ✅ |
| 双因素认证 | ❌ | ✅ |
| 设备管理 | ❌ | ✅ |
| 刷新令牌 | ❌ | ✅ |
| 速率限制 | 基础 | 增强 |

#### 产品管理

| 功能 | v1 | v2 |
|------|----|----|
| 基础搜索 | ✅ | ✅ |
| 高级筛选 | ✅ | ✅ (增强) |
| 模糊搜索 | ❌ | ✅ |
| 搜索建议 | ❌ | ✅ |
| 产品比较 | ❌ | ✅ |
| 实时库存 | 基础 | 增强 |
| 价格历史 | ❌ | ✅ |
| 供应商评分 | ❌ | ✅ |

#### 响应格式

**v1 响应格式:**
```json
{
    "status": "success",
    "message": "操作成功",
    "data": {
        "id": 1,
        "name": "产品名称"
    },
    "timestamp": "2025-12-04T10:30:00.000000Z"
}
```

**v2 增强响应格式:**
```json
{
    "status": "success",
    "message": "操作成功",
    "data": {
        "api_version": "v2",
        "id": 1,
        "name": "产品名称",
        "metadata": {
            "created_at": "2025-12-04T10:30:00.000000Z",
            "updated_at": "2025-12-04T10:30:00.000000Z"
        },
        "links": {
            "self": "/api/v2/products/1",
            "related": "/api/v2/products/1/recommendations"
        }
    },
    "performance": {
        "query_time_ms": 45.2,
        "cache_hit": true
    },
    "timestamp": "2025-12-04T10:30:00.000000Z"
}
```

## 迁移指南

### 从 v1 迁移到 v2

#### 1. 认证迁移

**v1 登录请求:**
```bash
POST /api/v1/auth/login
{
    "username": "testuser",
    "password": "password123"
}
```

**v2 登录请求:**
```bash
POST /api/v2/auth/login
{
    "username": "testuser",
    "password": "password123",
    "device_info": {
        "device_id": "web_123456",
        "device_type": "web",
        "user_agent": "Mozilla/5.0..."
    },
    "remember_me": false
}
```

**主要变化:**
- v2 支持设备信息
- v2 返回刷新令牌
- v2 增强安全验证

#### 2. 产品搜索迁移

**v1 产品搜索:**
```bash
GET /api/v1/products?search=办公椅&min_price=100&max_price=1000
```

**v2 产品搜索:**
```bash
GET /api/v2/products?search=办公椅&price_range=100-1000&categories=办公用品&sort=price_asc&in_stock_only=true
```

**主要变化:**
- v2 支持价格区间格式
- v2 支持多分类筛选
- v2 支持多种排序方式
- v2 支持库存筛选

#### 3. 响应处理迁移

**v1 响应处理:**
```javascript
const response = await fetch('/api/v1/products');
const data = await response.json();
console.log(data.data); // 产品数据
```

**v2 响应处理:**
```javascript
const response = await fetch('/api/v2/products');
const data = await response.json();
console.log(data.data.products); // 产品数据在 products 字段
console.log(data.data.performance); // 性能信息
console.log(data.data.api_version); // API 版本信息
```

### 迁移检查清单

- [ ] 更新 API 端点 URL
- [ ] 处理新的响应格式
- [ ] 实现设备管理（如需要）
- [ ] 更新错误处理逻辑
- [ ] 测试所有功能模块
- [ ] 验证性能改进
- [ ] 更新文档和代码注释

## 最佳实践

### 1. 版本选择策略

- **新项目**: 直接使用 v2 预览版
- **现有项目**: 继续使用 v1，逐步迁移到 v2
- **测试环境**: 同时测试 v1 和 v2

### 2. 错误处理

```javascript
// 检查 API 版本支持
const response = await fetch('/api/v3/products');
if (response.status === 400) {
    const error = await response.json();
    if (error.message.includes('not supported')) {
        console.log('支持的版本:', error.data.supported_versions);
    }
}
```

### 3. 版本弃用处理

```javascript
// 检查弃用警告
const apiVersion = response.headers.get('API-Version');
const deprecated = response.headers.get('API-Deprecated');
const sunsetDate = response.headers.get('API-Sunset-Date');

if (deprecated === 'true') {
    console.warn(`API ${apiVersion} 已弃用，将在 ${sunsetDate} 停用`);
    console.log(`迁移指南: ${response.headers.get('API-Migration-Guide')}`);
}
```

### 4. 缓存策略

```javascript
// 基于版本的缓存
const cacheKey = `products_${apiVersion}_${JSON.stringify(filters)}`;
const cached = localStorage.getItem(cacheKey);
if (cached) {
    return JSON.parse(cached);
}
```

## 性能优化

### 1. 版本缓存

- 版本配置信息自动缓存 60 分钟
- 支持手动清除缓存
- 智能缓存预热机制

### 2. 响应压缩

- v2 API 自动启用 gzip 压缩
- 减少 70% 响应大小
- 提升传输速度

### 3. 查询优化

- v2 使用优化的数据库查询
- 预加载关联数据减少 N+1 查询
- 智能索引使用

## 监控和分析

### 1. 版本使用统计

```bash
GET /api/versions/statistics
```

提供以下信息：
- 各版本请求分布
- 热门端点统计
- 错误率分析
- 平均响应时间

### 2. 性能监控

- 查询执行时间监控
- 缓存命中率统计
- 内存使用情况
- 并发请求处理

### 3. 健康检查

```bash
GET /api/versions/v1/health
GET /api/versions/v2/health
```

监控项目：
- API 可用性
- 数据库连接状态
- 缓存系统状态
- 认证系统状态

## 故障排除

### 常见问题

#### 1. 版本不支持错误

**错误:** `API version v3 is not supported`

**解决:** 使用支持的版本 (v1, v2)

#### 2. 弃用警告

**警告:** `API-Version: deprecated` header

**解决:** 按照迁移指南升级版本

#### 3. 响应格式变化

**问题:** v2 响应结构与 v1 不同

**解决:** 更新响应处理逻辑

### 调试工具

```bash
# 检查版本信息
curl -I https://api.manpou.jp/api/v1/products

# 比较版本差异
curl -X POST https://api.manpou.jp/api/versions/compare \
  -H "Content-Type: application/json" \
  -d '{"from_version":"v1","to_version":"v2"}'

# 健康检查
curl https://api.manpou.jp/api/v1/health
```

## 支持和联系

### 技术支持

- **文档**: [API 版本管理文档](https://docs.manpou.jp/api/versioning)
- **示例代码**: [GitHub 仓库](https://github.com/manpou/api-examples)
- **社区论坛**: [开发者社区](https://community.manpou.jp)

### 联系方式

- **技术支持**: support@manpou.jp
- **API 问题**: api-support@manpou.jp
- **紧急支持**: emergency@manpou.jp

### 更新通知

- **邮件通知**: api-updates@manpou.jp
- **RSS 订阅**: https://blog.manpou.jp/api-updates
- **Twitter**: @banho_api

---

**注意**: 本指南会随着 API 版本更新而持续维护，请定期查看最新版本。