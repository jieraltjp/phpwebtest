<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Swagger Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for your Swagger documentation.
    |
    */

    'api' => [
        /*
        |--------------------------------------------------------------------------
        | API Title
        |--------------------------------------------------------------------------
        |
        | The title of your API.
        |
        */
        'title' => 'RAKUMART × 1688 B2B 采购门户 API',

        /*
        |--------------------------------------------------------------------------
        | API Description
        |--------------------------------------------------------------------------
        |
        | The description of your API.
        |
        */
        'description' => '供雅虎客户后台使用的核心采购流程 API 契约。包含用户认证、产品管理、订单处理和物流追踪等功能。',

        /*
        |--------------------------------------------------------------------------
        | API Version
        |--------------------------------------------------------------------------
        |
        | The version of your API.
        |
        */
        'version' => '1.0.0',

        /*
        |--------------------------------------------------------------------------
        | Contact Information
        |--------------------------------------------------------------------------
        |
        | The contact information for your API.
        |
        */
        'contact' => [
            'name' => 'RAKUMART 技术支持',
            'email' => 'support@rakumart.com',
        ],

        /*
        |--------------------------------------------------------------------------
        | License Information
        |--------------------------------------------------------------------------
        |
        | The license information for your API.
        |
        */
        'license' => [
            'name' => 'MIT',
            'url' => 'https://opensource.org/licenses/MIT',
        ],

        /*
        |--------------------------------------------------------------------------
        | Server Information
        |--------------------------------------------------------------------------
        |
        | The server information for your API.
        |
        */
        'servers' => [
            [
                'url' => env('APP_URL', 'http://localhost:8000') . '/api/v1',
                'description' => '开发环境 API',
            ],
            [
                'url' => 'https://api.rakumart.com/v1',
                'description' => '生产环境 API',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Security Schemes
        |--------------------------------------------------------------------------
        |
        | The security schemes for your API.
        |
        */
        'security' => [
            'BearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
                'description' => 'JWT 认证令牌',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Tags
        |--------------------------------------------------------------------------
        |
        | The tags for your API.
        |
        */
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
                'name' => 'Admin',
                'description' => '管理员相关接口',
            ],
        ],
    ],
];