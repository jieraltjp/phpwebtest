<?php

namespace App\Http\Controllers;

class SwaggerController extends Controller
{
    public function index()
    {
        return view('swagger.index');
    }

    public function interactive()
    {
        return view('swagger.interactive');
    }

    public function openApi()
    {
        $openApi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => '雅虎 B2B 采购门户 API',
                'version' => '1.4.0',
                'description' => '供雅虎客户后台使用的核心采购流程 API 契约。包含用户认证、产品管理、订单处理、询价管理、批量采购和物流追踪等功能。',
                'contact' => [
                    'name' => 'RAKUMART 技术支持',
                    'email' => 'support@rakumart.com',
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://opensource.org/licenses/MIT',
                ],
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'http://localhost:8000') . '/api',
                    'description' => '开发环境 API',
                ],
                [
                    'url' => 'https://api.rakumart.com/api',
                    'description' => '生产环境 API',
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description' => 'JWT 认证令牌',
                    ],
                ],
                'schemas' => [
                    'User' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'name' => ['type' => 'string', 'example' => '测试用户'],
                            'username' => ['type' => 'string', 'example' => 'testuser'],
                            'email' => ['type' => 'string', 'example' => 'test@example.com'],
                            'company' => ['type' => 'string', 'example' => '测试公司'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                            'last_login_at' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                    'Product' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'name' => ['type' => 'string', 'example' => 'iPhone 15 Pro'],
                            'sku' => ['type' => 'string', 'example' => 'IP15P001'],
                            'description' => ['type' => 'string', 'example' => '最新款 iPhone'],
                            'price' => ['type' => 'number', 'format' => 'float', 'example' => 8999.00],
                            'currency' => ['type' => 'string', 'example' => 'CNY'],
                            'stock' => ['type' => 'integer', 'example' => 100],
                            'category' => ['type' => 'string', 'example' => '电子产品'],
                        ],
                    ],
                    'ApiResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'example' => 'success'],
                            'message' => ['type' => 'string', 'example' => '操作成功'],
                            'data' => ['type' => 'object'],
                            'timestamp' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                    'ErrorResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'example' => 'error'],
                            'message' => ['type' => 'string', 'example' => '操作失败'],
                            'errors' => ['type' => 'object'],
                            'timestamp' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                    ],
                ],
            ],
            'paths' => [
                '/auth/login' => [
                    'post' => [
                        'summary' => '用户登录',
                        'description' => '使用用户名和密码进行登录认证',
                        'tags' => ['Authentication'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['username', 'password'],
                                        'properties' => [
                                            'username' => [
                                                'type' => 'string',
                                                'description' => '用户名',
                                                'example' => 'testuser',
                                            ],
                                            'password' => [
                                                'type' => 'string',
                                                'description' => '密码',
                                                'example' => 'password123',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => '登录成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                        'example' => [
                                            'status' => 'success',
                                            'message' => '登录成功',
                                            'data' => [
                                                'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                                                'token_type' => 'bearer',
                                                'expires_in' => 3600,
                                                'user' => [
                                                    'id' => 1,
                                                    'name' => '测试用户',
                                                    'username' => 'testuser',
                                                    'email' => 'test@example.com',
                                                    'company' => '测试公司',
                                                ],
                                            ],
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                'description' => '认证失败',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ErrorResponse',
                                        ],
                                        'example' => [
                                            'status' => 'error',
                                            'message' => '用户名或密码错误',
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/auth/register' => [
                    'post' => [
                        'summary' => '用户注册',
                        'description' => '创建新用户账户',
                        'tags' => ['Authentication'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['name', 'username', 'email', 'password', 'password_confirmation'],
                                        'properties' => [
                                            'name' => [
                                                'type' => 'string',
                                                'description' => '用户姓名',
                                                'example' => '张三',
                                            ],
                                            'username' => [
                                                'type' => 'string',
                                                'description' => '用户名',
                                                'example' => 'zhangsan',
                                            ],
                                            'email' => [
                                                'type' => 'string',
                                                'format' => 'email',
                                                'description' => '邮箱地址',
                                                'example' => 'zhangsan@example.com',
                                            ],
                                            'password' => [
                                                'type' => 'string',
                                                'description' => '密码（至少8位，包含大小写字母、数字和特殊字符）',
                                                'example' => 'Password123!',
                                            ],
                                            'password_confirmation' => [
                                                'type' => 'string',
                                                'description' => '确认密码',
                                                'example' => 'Password123!',
                                            ],
                                            'company' => [
                                                'type' => 'string',
                                                'description' => '公司名称（可选）',
                                                'example' => '测试公司',
                                            ],
                                            'phone' => [
                                                'type' => 'string',
                                                'description' => '电话号码（可选）',
                                                'example' => '+8613800138000',
                                            ],
                                            'address' => [
                                                'type' => 'string',
                                                'description' => '地址（可选）',
                                                'example' => '北京市朝阳区',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => '注册成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                        'example' => [
                                            'status' => 'success',
                                            'message' => '注册成功',
                                            'data' => [
                                                'user' => [
                                                    'id' => 2,
                                                    'name' => '张三',
                                                    'username' => 'zhangsan',
                                                    'email' => 'zhangsan@example.com',
                                                    'company' => '测试公司',
                                                ],
                                                'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                                                'token_type' => 'bearer',
                                                'expires_in' => 3600,
                                            ],
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                            '422' => [
                                'description' => '验证失败',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ErrorResponse',
                                        ],
                                        'example' => [
                                            'status' => 'error',
                                            'message' => 'Validation failed',
                                            'errors' => [
                                                'email' => ['邮箱已被使用'],
                                            ],
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/auth/me' => [
                    'get' => [
                        'summary' => '获取当前用户信息',
                        'description' => '获取当前登录用户的详细信息',
                        'tags' => ['Authentication'],
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => [
                                'description' => '获取成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                        'example' => [
                                            'status' => 'success',
                                            'message' => '获取用户信息成功',
                                            'data' => [
                                                'id' => 1,
                                                'name' => '测试用户',
                                                'username' => 'testuser',
                                                'email' => 'test@example.com',
                                                'company' => '测试公司',
                                                'created_at' => '2025-12-03T13:50:34.000000Z',
                                                'last_login_at' => '2025-12-04T10:30:00.000000Z',
                                            ],
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                'description' => '未认证',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ErrorResponse',
                                        ],
                                        'example' => [
                                            'status' => 'error',
                                            'message' => '认证失败，请重新登录',
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/auth/logout' => [
                    'post' => [
                        'summary' => '退出登录',
                        'description' => '退出当前用户的登录状态',
                        'tags' => ['Authentication'],
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => [
                                'description' => '退出成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                        'example' => [
                                            'status' => 'success',
                                            'message' => '退出登录成功',
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/auth/check-username' => [
                    'post' => [
                        'summary' => '检查用户名可用性',
                        'description' => '检查用户名是否已被注册',
                        'tags' => ['Authentication'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['username'],
                                        'properties' => [
                                            'username' => [
                                                'type' => 'string',
                                                'description' => '要检查的用户名',
                                                'example' => 'testuser',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => '检查成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                        'example' => [
                                            'status' => 'success',
                                            'data' => [
                                                'available' => true,
                                                'message' => '用户名可用',
                                            ],
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/auth/check-email' => [
                    'post' => [
                        'summary' => '检查邮箱可用性',
                        'description' => '检查邮箱是否已被注册',
                        'tags' => ['Authentication'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['email'],
                                        'properties' => [
                                            'email' => [
                                                'type' => 'string',
                                                'format' => 'email',
                                                'description' => '要检查的邮箱地址',
                                                'example' => 'test@example.com',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => '检查成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                        'example' => [
                                            'status' => 'success',
                                            'data' => [
                                                'available' => false,
                                                'message' => '邮箱已被注册',
                                            ],
                                            'timestamp' => '2025-12-04T10:30:00.000000Z',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/products' => [
                    'get' => [
                        'summary' => '获取产品列表',
                        'description' => '分页获取产品列表，支持搜索和筛选',
                        'tags' => ['Products'],
                        'parameters' => [
                            [
                                'name' => 'page',
                                'in' => 'query',
                                'description' => '页码',
                                'required' => false,
                                'schema' => ['type' => 'integer', 'default' => 1],
                            ],
                            [
                                'name' => 'per_page',
                                'in' => 'query',
                                'description' => '每页数量',
                                'required' => false,
                                'schema' => ['type' => 'integer', 'default' => 20],
                            ],
                            [
                                'name' => 'search',
                                'in' => 'query',
                                'description' => '搜索关键词',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'category',
                                'in' => 'query',
                                'description' => '产品分类',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => '获取成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/products/{id}' => [
                    'get' => [
                        'summary' => '获取产品详情',
                        'description' => '根据ID获取单个产品的详细信息',
                        'tags' => ['Products'],
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'description' => '产品ID',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => '获取成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => [
                                'description' => '产品不存在',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ErrorResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/orders' => [
                    'get' => [
                        'summary' => '获取订单列表',
                        'description' => '获取当前用户的订单列表',
                        'tags' => ['Orders'],
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [
                            [
                                'name' => 'page',
                                'in' => 'query',
                                'description' => '页码',
                                'required' => false,
                                'schema' => ['type' => 'integer', 'default' => 1],
                            ],
                            [
                                'name' => 'per_page',
                                'in' => 'query',
                                'description' => '每页数量',
                                'required' => false,
                                'schema' => ['type' => 'integer', 'default' => 20],
                            ],
                            [
                                'name' => 'status',
                                'in' => 'query',
                                'description' => '订单状态筛选',
                                'required' => false,
                                'schema' => [
                                    'type' => 'string',
                                    'enum' => ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => '获取成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'post' => [
                        'summary' => '创建订单',
                        'description' => '创建新的采购订单',
                        'tags' => ['Orders'],
                        'security' => [['bearerAuth' => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['items', 'shipping_address', 'contact_info'],
                                        'properties' => [
                                            'items' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'required' => ['sku', 'quantity'],
                                                    'properties' => [
                                                        'sku' => ['type' => 'string'],
                                                        'quantity' => ['type' => 'integer'],
                                                    ],
                                                ],
                                            ],
                                            'shipping_address' => ['type' => 'string'],
                                            'contact_info' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'email' => ['type' => 'string'],
                                                    'phone' => ['type' => 'string'],
                                                    'contact_person' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => '创建成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/inquiries' => [
                    'get' => [
                        'summary' => '获取询价列表',
                        'description' => '获取当前用户的询价列表',
                        'tags' => ['Inquiries'],
                        'security' => [['bearerAuth' => []]],
                        'responses' => [
                            '200' => [
                                'description' => '获取成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'post' => [
                        'summary' => '创建询价',
                        'description' => '创建新的产品询价',
                        'tags' => ['Inquiries'],
                        'security' => [['bearerAuth' => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['product_sku', 'quantity', 'contact_info'],
                                        'properties' => [
                                            'product_sku' => ['type' => 'string'],
                                            'quantity' => ['type' => 'integer'],
                                            'message' => ['type' => 'string'],
                                            'contact_info' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'email' => ['type' => 'string'],
                                                    'phone' => ['type' => 'string'],
                                                    'contact_person' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => '创建成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/bulk-purchase/orders' => [
                    'post' => [
                        'summary' => '创建批量采购订单',
                        'description' => '创建批量采购订单，支持多SKU和折扣计算',
                        'tags' => ['Bulk Purchase'],
                        'security' => [['bearerAuth' => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['items', 'shipping_address', 'contact_info'],
                                        'properties' => [
                                            'items' => [
                                                'type' => 'array',
                                                'maxItems' => 50,
                                                'items' => [
                                                    'type' => 'object',
                                                    'required' => ['sku', 'quantity'],
                                                    'properties' => [
                                                        'sku' => ['type' => 'string'],
                                                        'quantity' => ['type' => 'integer', 'maximum' => 100000],
                                                    ],
                                                ],
                                            ],
                                            'shipping_address' => ['type' => 'string'],
                                            'contact_info' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'email' => ['type' => 'string'],
                                                    'phone' => ['type' => 'string'],
                                                    'contact_person' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => '创建成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/bulk-purchase/quote' => [
                    'post' => [
                        'summary' => '获取批量采购报价',
                        'description' => '获取批量采购的折扣报价，不创建订单',
                        'tags' => ['Bulk Purchase'],
                        'security' => [['bearerAuth' => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['items'],
                                        'properties' => [
                                            'items' => [
                                                'type' => 'array',
                                                'maxItems' => 50,
                                                'items' => [
                                                    'type' => 'object',
                                                    'required' => ['sku', 'quantity'],
                                                    'properties' => [
                                                        'sku' => ['type' => 'string'],
                                                        'quantity' => ['type' => 'integer'],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => '获取成功',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ApiResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/health' => [
                    'get' => [
                        'summary' => '健康检查',
                        'description' => '检查API服务状态',
                        'tags' => ['System'],
                        'responses' => [
                            '200' => [
                                'description' => '服务正常',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'status' => ['type' => 'string', 'example' => 'ok'],
                                                'timestamp' => ['type' => 'string', 'format' => 'date-time'],
                                                'version' => ['type' => 'string', 'example' => '1.4.0'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'tags' => [
                [
                    'name' => 'Authentication',
                    'description' => '用户认证相关接口',
                ],
                [
                    'name' => 'Products',
                    'description' => '产品管理相关接口',
                ],
                [
                    'name' => 'Orders',
                    'description' => '订单管理相关接口',
                ],
                [
                    'name' => 'Inquiries',
                    'description' => '询价管理相关接口',
                ],
                [
                    'name' => 'Bulk Purchase',
                    'description' => '批量采购相关接口',
                ],
                [
                    'name' => 'System',
                    'description' => '系统相关接口',
                ],
            ],
        ];

        return response()->json($openApi);
    }
}

    /**
     * 获取 API 路径定义
     */
    private function getPaths()
    {
        return [
            '/auth/login' => [
                'post' => [
                    'tags' => ['认证'],
                    'summary' => '用户登录并获取认证 Token',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'username' => [
                                            'type' => 'string',
                                            'example' => 'yahoo_client_001',
                                        ],
                                        'password' => [
                                            'type' => 'string',
                                            'example' => 'strongpassword123',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => '登录成功',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'access_token' => [
                                                'type' => 'string',
                                                'description' => '用于后续请求的认证 Token',
                                            ],
                                            'token_type' => [
                                                'type' => 'string',
                                                'example' => 'Bearer',
                                            ],
                                            'expires_in' => [
                                                'type' => 'integer',
                                                'description' => 'Token 有效期（秒）',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => [
                            'description' => '认证失败',
                        ],
                    ],
                ],
            ],
            '/products' => [
                'get' => [
                    'tags' => ['产品'],
                    'summary' => '获取产品列表（支持分页和筛选）',
                    'security' => [
                        ['BearerAuth' => []],
                    ],
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'integer', 'default' => 1],
                        ],
                        [
                            'name' => 'limit',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'integer', 'default' => 20],
                        ],
                        [
                            'name' => 'search',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => '产品列表数据',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/ProductListResponse',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '/products/{id}' => [
                'get' => [
                    'tags' => ['产品'],
                    'summary' => '获取单个产品详情',
                    'security' => [
                        ['BearerAuth' => []],
                    ],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                            'description' => '产品 SKU 或 ID',
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => '单个产品详情数据',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/ProductDetail',
                                    ],
                                ],
                            ],
                        ],
                        '404' => [
                            'description' => '产品不存在',
                        ],
                    ],
                ],
            ],
            '/orders' => [
                'post' => [
                    'tags' => ['订单'],
                    'summary' => '创建新的采购订单',
                    'security' => [
                        ['BearerAuth' => []],
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'items' => [
                                            'type' => 'array',
                                            'description' => '订单包含的产品列表',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'sku' => [
                                                        'type' => 'string',
                                                        'description' => '产品 SKU',
                                                        'example' => 'ALIBABA_SKU_A123',
                                                    ],
                                                    'quantity' => [
                                                        'type' => 'integer',
                                                        'description' => '采购数量',
                                                        'example' => 100,
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'shipping_address' => [
                                            'type' => 'string',
                                            'description' => '配送地址',
                                            'example' => '日本东京都港区...',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => '订单创建成功',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'order_id' => [
                                                'type' => 'string',
                                                'example' => 'YO-12345678',
                                            ],
                                            'message' => [
                                                'type' => 'string',
                                                'example' => '订单已成功提交到阿里巴巴平台。',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => [
                            'description' => '请求参数错误或库存不足',
                        ],
                    ],
                ],
                'get' => [
                    'tags' => ['订单'],
                    'summary' => '获取客户的所有订单列表',
                    'security' => [
                        ['BearerAuth' => []],
                    ],
                    'parameters' => [
                        [
                            'name' => 'status',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'string'],
                            'description' => '按状态筛选',
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => '订单列表数据',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/OrderListResponse',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '/orders/{id}' => [
                'get' => [
                    'tags' => ['订单'],
                    'summary' => '获取单个订单详情和最新状态',
                    'security' => [
                        ['BearerAuth' => []],
                    ],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                            'description' => '订单 ID',
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => '订单详情数据',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/OrderDetail',
                                    ],
                                ],
                            ],
                        ],
                        '404' => [
                            'description' => '订单不存在',
                        ],
                    ],
                ],
            ],
            '/orders/{id}/tracking-link' => [
                'get' => [
                    'tags' => ['订单'],
                    'summary' => '获取订单物流追踪的外部链接',
                    'security' => [
                        ['BearerAuth' => []],
                    ],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                            'description' => '订单 ID',
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => '外部追踪链接',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'tracking_url' => [
                                                'type' => 'string',
                                                'format' => 'url',
                                                'example' => 'https://www.logistics-company.com/track?id=ABC123456',
                                            ],
                                            'logistics_company' => [
                                                'type' => 'string',
                                                'example' => 'Fedex',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * 获取数据模型定义
     */
    private function getSchemas()
    {
        return [
            'ProductListResponse' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/ProductSummary',
                        ],
                    ],
                    'total' => [
                        'type' => 'integer',
                        'example' => 150,
                    ],
                    'page' => [
                        'type' => 'integer',
                        'example' => 1,
                    ],
                ],
            ],
            'ProductSummary' => [
                'type' => 'object',
                'properties' => [
                    'sku' => [
                        'type' => 'string',
                        'example' => 'ALIBABA_SKU_A123',
                    ],
                    'name' => [
                        'type' => 'string',
                        'example' => '日本客户专用 办公椅',
                    ],
                    'price' => [
                        'type' => 'number',
                        'format' => 'float',
                        'example' => 1250.50,
                    ],
                    'currency' => [
                        'type' => 'string',
                        'example' => 'CNY',
                    ],
                    'image_url' => [
                        'type' => 'string',
                        'format' => 'url',
                        'example' => 'https://cdn.alibaba.com/img/A123.jpg',
                    ],
                    'supplier_shop' => [
                        'type' => 'string',
                        'example' => 'XX家具旗舰店',
                    ],
                ],
            ],
            'ProductDetail' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/ProductSummary'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'description' => [
                                'type' => 'string',
                                'description' => '详细描述',
                            ],
                            'specs' => [
                                'type' => 'object',
                                'description' => '型号规格',
                                'example' => ['Color' => 'Black', 'Size' => 'Large'],
                            ],
                        ],
                    ],
                ],
            ],
            'OrderListResponse' => [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/components/schemas/OrderSummary',
                        ],
                    ],
                    'total' => [
                        'type' => 'integer',
                        'example' => 45,
                    ],
                ],
            ],
            'OrderSummary' => [
                'type' => 'object',
                'properties' => [
                    'order_id' => [
                        'type' => 'string',
                        'example' => 'YO-12345678',
                    ],
                    'created_at' => [
                        'type' => 'string',
                        'format' => 'date-time',
                    ],
                    'total_amount' => [
                        'type' => 'number',
                        'format' => 'float',
                        'example' => 12500.00,
                    ],
                    'currency' => [
                        'type' => 'string',
                        'example' => 'CNY',
                    ],
                    'status' => [
                        'type' => 'string',
                        'description' => '订单当前状态',
                        'enum' => ['PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'RETURNED', 'CANCELLED'],
                        'example' => 'SHIPPED',
                    ],
                ],
            ],
            'OrderDetail' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/OrderSummary'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'items' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'sku' => ['type' => 'string'],
                                        'name' => ['type' => 'string'],
                                        'quantity' => ['type' => 'integer'],
                                        'unit_price' => ['type' => 'number', 'format' => 'float'],
                                    ],
                                ],
                            ],
                            'shipping_address' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ];
    }
}