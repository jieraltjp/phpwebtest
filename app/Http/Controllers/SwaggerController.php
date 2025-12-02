<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SwaggerController extends Controller
{
    /**
     * 显示 Swagger 文档页面
     */
    public function index()
    {
        return view('swagger.index');
    }

    /**
     * 获取 OpenAPI 规范 JSON
     */
    public function openApi()
    {
        $config = config('swagger.api');
        
        $openApi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $config['title'],
                'description' => $config['description'],
                'version' => $config['version'],
                'contact' => $config['contact'],
                'license' => $config['license'],
            ],
            'servers' => $config['servers'],
            'paths' => $this->getPaths(),
            'components' => [
                'securitySchemes' => $config['security'],
                'schemas' => $this->getSchemas(),
            ],
            'tags' => $config['tags'],
        ];

        return response()->json($openApi);
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