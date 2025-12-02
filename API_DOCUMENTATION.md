# 雅虎 B2B 采购门户 API 文档

## 概述

这是雅虎 B2B 采购门户的 RESTful API 文档，提供用户认证、产品管理和订单处理功能。

**基础 URL**: `http://localhost:8000/api/v1`

## 认证

所有 API 请求（除了登录）都需要在请求头中包含 JWT 令牌：

```
Authorization: Bearer <your_jwt_token>
```

## API 接口

### 1. 用户认证

#### 登录
```http
POST /auth/login
```

**请求体**:
```json
{
    "username": "yahoo_client_001",
    "password": "strongpassword123"
}
```

**响应**:
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

#### 获取当前用户信息
```http
GET /auth/me
Authorization: Bearer <token>
```

#### 退出登录
```http
POST /auth/logout
Authorization: Bearer <token>
```

#### 刷新令牌
```http
POST /auth/refresh
Authorization: Bearer <token>
```

### 2. 产品管理

#### 获取产品列表
```http
GET /products?page=1&limit=20&search=keyword&min_price=100&max_price=500
Authorization: Bearer <token>
```

**查询参数**:
- `page`: 页码（默认: 1）
- `limit`: 每页数量（默认: 20）
- `search`: 搜索关键词（搜索名称、SKU、供应商）
- `min_price`: 最低价格
- `max_price`: 最高价格
- `supplier`: 供应商筛选

**响应**:
```json
{
    "data": [
        {
            "id": 1,
            "sku": "ALIBABA_SKU_A123",
            "name": "日本客户专用 办公椅",
            "price": 1250.50,
            "currency": "CNY",
            "image_url": "https://cdn.alibaba.com/img/A123.jpg",
            "supplier_shop": "XX家具旗舰店",
            "stock": 100,
            "created_at": "2025-12-02T11:38:51.000000Z",
            "updated_at": "2025-12-02T11:38:51.000000Z"
        }
    ],
    "total": 5,
    "page": 1,
    "per_page": 20,
    "last_page": 1
}
```

#### 获取产品详情
```http
GET /products/{id}
Authorization: Bearer <token>
```

**响应**:
```json
{
    "sku": "ALIBABA_SKU_A123",
    "name": "日本客户专用 办公椅",
    "description": "人体工学设计，适合长时间办公使用，支持多角度调节",
    "price": 1250.50,
    "currency": "CNY",
    "image_url": "https://cdn.alibaba.com/img/A123.jpg",
    "supplier_shop": "XX家具旗舰店",
    "specs": {
        "Color": "Black",
        "Size": "Large",
        "Material": "Mesh + Aluminum",
        "Weight": "15kg"
    },
    "stock": 100
}
```

### 3. 订单管理

#### 创建新订单
```http
POST /orders
Authorization: Bearer <token>
```

**请求体**:
```json
{
    "items": [
        {
            "sku": "ALIBABA_SKU_A123",
            "quantity": 2
        },
        {
            "sku": "ALIBABA_SKU_B456",
            "quantity": 1
        }
    ],
    "shipping_address": "日本东京都港区..."
}
```

**响应**:
```json
{
    "order_id": "YO-20251202-00001",
    "message": "订单已成功提交到阿里巴巴平台。",
    "total_amount_cny": 2781.00,
    "total_amount_jpy": 56900.50
}
```

#### 获取订单列表
```http
GET /orders?status=PENDING
Authorization: Bearer <token>
```

**查询参数**:
- `status`: 订单状态筛选 (PENDING, PROCESSING, SHIPPED, DELIVERED, RETURNED, CANCELLED)

**响应**:
```json
{
    "data": [
        {
            "order_id": "YO-20251202-00001",
            "created_at": "2025-12-02T12:00:00.000000Z",
            "total_amount": 2781.00,
            "currency": "CNY",
            "status": "PENDING",
            "status_message": "订单已成功提交到阿里巴巴平台。"
        }
    ],
    "total": 1
}
```

#### 获取订单详情
```http
GET /orders/{order_id}
Authorization: Bearer <token>
```

**响应**:
```json
{
    "order_id": "YO-20251202-00001",
    "created_at": "2025-12-02T12:00:00.000000Z",
    "total_amount": 2781.00,
    "currency": "CNY",
    "status": "PENDING",
    "status_message": "订单已成功提交到阿里巴巴平台。",
    "items": [
        {
            "sku": "ALIBABA_SKU_A123",
            "name": "日本客户专用 办公椅",
            "quantity": 2,
            "unit_price": 1250.50
        },
        {
            "sku": "ALIBABA_SKU_B456",
            "name": "无线蓝牙键盘",
            "quantity": 1,
            "unit_price": 280.00
        }
    ],
    "shipping_address": "日本东京都港区...",
    "domestic_tracking_number": null,
    "international_tracking_number": null,
    "total_fee_cny": 2781.00,
    "total_fee_jpy": 56900.50
}
```

#### 获取物流追踪链接
```http
GET /orders/{order_id}/tracking-link
Authorization: Bearer <token>
```

**响应**:
```json
{
    "tracking_url": "https://www.fedex.com/fedextrack/?trknbr=ABC123456",
    "logistics_company": "Fedex",
    "domestic_tracking_number": "SF1234567890",
    "international_tracking_number": "ABC123456"
}
```

## 错误响应

所有错误响应都遵循以下格式：

```json
{
    "error": "错误类型",
    "message": "错误描述"
}
```

常见错误码：
- `400`: 请求参数错误
- `401`: 未授权访问
- `404`: 资源不存在
- `422`: 验证失败
- `500`: 服务器内部错误

## 状态常量

### 订单状态
- `PENDING`: 待处理
- `PROCESSING`: 处理中
- `SHIPPED`: 已发货
- `DELIVERED`: 已送达
- `RETURNED`: 已退回
- `CANCELLED`: 已取消

### 物流状态
- `pending`: 待发货
- `shipped`: 已发货
- `in_transit`: 运输中
- `delivered`: 已送达
- `returned`: 已退回

## 测试账户

为了方便测试，可以使用以下账户：

```json
{
    "username": "testuser",
    "password": "password123"
}
```

## 开发环境设置

1. 克隆项目并安装依赖
2. 配置 `.env` 文件
3. 运行数据库迁移：`php artisan migrate`
4. 填充测试数据：`php artisan db:seed --class=ProductSeeder`
5. 启动开发服务器：`php artisan serve`