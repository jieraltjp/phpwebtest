<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class BulkPurchaseControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建测试数据
        $this->createTestData();
    }

    /**
     * 创建测试数据
     */
    protected function createTestData(): void
    {
        // 创建多个测试产品
        $this->testProducts = Product::factory()->count(10)->create([
            'is_active' => true,
            'stock_quantity' => 1000
        ]);

        // 创建测试用户
        $this->testUser = User::factory()->create([
            'username' => 'bulkbuyer',
            'email' => 'bulk@example.com'
        ]);
    }

    /**
     * 测试批量采购报价 - 成功案例
     */
    public function test_bulk_purchase_quote_success(): void
    {
        $items = [];
        foreach ($this->testProducts->take(5) as $product) {
            $items[] = [
                'sku' => $product->sku,
                'quantity' => 100
            ];
        }

        $quoteData = [
            'customer_name' => 'Bulk Buyer Corp',
            'customer_email' => 'buyer@bulkcorp.com',
            'customer_phone' => '+81-90-1234-5678',
            'company_name' => 'Bulk Buyer Corporation',
            'items' => $items,
            'shipping_address' => '123 Bulk Street, Tokyo, Japan',
            'expected_delivery_date' => '2025-12-25'
        ];

        $response = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'quote' => [
                            'quote_id',
                            'valid_until',
                            'items' => [
                                '*' => [
                                    'sku',
                                    'product_name',
                                    'unit_price',
                                    'quantity',
                                    'subtotal',
                                    'discount_percentage',
                                    'discount_amount'
                                ]
                            ],
                            'total_amount',
                            'total_discount',
                            'final_amount',
                            'estimated_shipping',
                            'tax_amount',
                            'currency',
                            'volume_discount_tier'
                        ]
                    ],
                    'timestamp'
                ]);

        $quote = $response->json('data.quote');
        $this->assertEquals('success', $response->json('status'));
        $this->assertNotEmpty($quote['quote_id']);
        $this->assertCount(5, $quote['items']);
        $this->assertGreaterThan(0, $quote['total_discount']);
        $this->assertLessThan($quote['total_amount'], $quote['final_amount']);
    }

    /**
     * 测试批量采购报价 - 大额订单折扣
     */
    public function test_bulk_purchase_large_order_discount(): void
    {
        $items = [];
        foreach ($this->testProducts->take(3) as $product) {
            $items[] = [
                'sku' => $product->sku,
                'quantity' => 1000 // 大数量
            ];
        }

        $quoteData = [
            'customer_name' => 'Large Order Buyer',
            'customer_email' => 'large@example.com',
            'items' => $items
        ];

        $response = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);

        $response->assertStatus(200);
        
        $quote = $response->json('data.quote');
        $this->assertGreaterThan(10, $quote['total_discount']); // 大订单应该有更高折扣
        $this->assertEquals('high_volume', $quote['volume_discount_tier']);
    }

    /**
     * 测试批量采购报价 - 超过50个SKU限制
     */
    public function test_bulk_purchase_quote_exceeds_sku_limit(): void
    {
        $items = [];
        for ($i = 0; $i < 51; $i++) {
            $items[] = [
                'sku' => $this->testProducts->first()->sku,
                'quantity' => 10
            ];
        }

        $quoteData = [
            'customer_name' => 'Excessive SKU Buyer',
            'customer_email' => 'excessive@example.com',
            'items' => $items
        ];

        $response = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items']);
    }

    /**
     * 测试批量采购报价 - 无效SKU
     */
    public function test_bulk_purchase_quote_invalid_skus(): void
    {
        $items = [
            ['sku' => 'INVALID-SKU-1', 'quantity' => 100],
            ['sku' => 'INVALID-SKU-2', 'quantity' => 50]
        ];

        $quoteData = [
            'customer_name' => 'Invalid SKU Buyer',
            'customer_email' => 'invalid@example.com',
            'items' => $items
        ];

        $response = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items']);
    }

    /**
     * 测试批量采购报价 - 库存不足
     */
    public function test_bulk_purchase_quote_insufficient_stock(): void
    {
        // 创建库存不足的产品
        $lowStockProduct = Product::factory()->create([
            'stock_quantity' => 50
        ]);

        $items = [
            ['sku' => $lowStockProduct->sku, 'quantity' => 100] // 超过库存
        ];

        $quoteData = [
            'customer_name' => 'Stock Issue Buyer',
            'customer_email' => 'stock@example.com',
            'items' => $items
        ];

        $response = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /**
     * 测试创建批量采购订单 - 成功案例
     */
    public function test_create_bulk_purchase_order_success(): void
    {
        // 先获取报价
        $items = [];
        foreach ($this->testProducts->take(3) as $product) {
            $items[] = [
                'sku' => $product->sku,
                'quantity' => 100
            ];
        }

        $quoteData = [
            'customer_name' => 'Order Creator',
            'customer_email' => 'creator@example.com',
            'items' => $items
        ];

        $quoteResponse = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);
        $quoteId = $quoteResponse->json('data.quote.quote_id');

        // 使用报价创建订单
        $orderData = [
            'quote_id' => $quoteId,
            'shipping_address' => '456 Order Street, Tokyo, Japan',
            'billing_address' => '456 Order Street, Tokyo, Japan',
            'payment_method' => 'bank_transfer',
            'notes' => 'Please process order ASAP'
        ];

        $response = $this->withAuth()->postJson('/api/bulk-purchase', $orderData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'order' => [
                            'id',
                            'order_number',
                            'quote_id',
                            'customer_name',
                            'total_amount',
                            'final_amount',
                            'currency',
                            'status',
                            'items' => [
                                '*' => [
                                    'sku',
                                    'product_name',
                                    'quantity',
                                    'unit_price',
                                    'subtotal'
                                ]
                            ],
                            'payment_info' => [
                                'payment_method',
                                'payment_status',
                                'due_amount'
                            ],
                            'shipping_info' => [
                                'shipping_address',
                                'estimated_delivery'
                            ],
                            'created_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $order = $response->json('data.order');
        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('pending', $order['status']);
        $this->assertEquals($quoteId, $order['quote_id']);
        $this->assertCount(3, $order['items']);

        // 验证库存已扣减
        foreach ($this->testProducts->take(3) as $product) {
            $product->refresh();
            $this->assertEquals(900, $product->stock_quantity); // 1000 - 100
        }

        // 验证数据库中的订单
        $this->assertDatabaseHas('orders', [
            'order_number' => $order['order_number'],
            'status' => 'pending'
        ]);
    }

    /**
     * 测试创建批量采购订单 - 无效报价ID
     */
    public function test_create_bulk_purchase_order_invalid_quote(): void
    {
        $orderData = [
            'quote_id' => 'invalid-quote-id',
            'shipping_address' => '123 Test Street'
        ];

        $response = $this->withAuth()->postJson('/api/bulk-purchase', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['quote_id']);
    }

    /**
     * 测试创建批量采购订单 - 过期报价
     */
    public function test_create_bulk_purchase_order_expired_quote(): void
    {
        // 这里需要创建一个过期的报价，具体实现取决于报价存储机制
        // 暂时跳过这个测试，因为需要模拟时间
        $this->markTestSkipped('需要模拟时间来测试过期报价');
    }

    /**
     * 测试获取批量采购历史 - 成功案例
     */
    public function test_get_bulk_purchase_history_success(): void
    {
        // 创建一些批量采购订单
        $this->createBulkPurchaseOrders(3);

        $response = $this->withAuth()->getJson('/api/bulk-purchase/history');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'orders' => [
                            '*' => [
                                'id',
                                'order_number',
                                'quote_id',
                                'customer_name',
                                'total_amount',
                                'final_amount',
                                'currency',
                                'status',
                                'item_count',
                                'created_at'
                            ]
                        ],
                        'pagination' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page'
                        ],
                        'summary' => [
                            'total_orders',
                            'total_amount',
                            'average_order_value'
                        ]
                    ],
                    'timestamp'
                ]);

        $data = $response->json('data');
        $this->assertEquals('success', $response->json('status'));
        $this->assertGreaterThan(0, count($data['orders']));
        $this->assertGreaterThan(0, $data['summary']['total_orders']);
    }

    /**
     * 测试获取批量采购历史 - 按状态筛选
     */
    public function test_get_bulk_purchase_history_by_status(): void
    {
        $this->createBulkPurchaseOrders(2);

        $response = $this->withAuth()
                        ->getJson('/api/bulk-purchase/history?status=pending');

        $response->assertStatus(200);
        
        $orders = $response->json('data.orders');
        foreach ($orders as $order) {
            $this->assertEquals('pending', $order['status']);
        }
    }

    /**
     * 测试获取批量采购历史 - 日期范围筛选
     */
    public function test_get_bulk_purchase_history_by_date_range(): void
    {
        $this->createBulkPurchaseOrders(1);

        $today = now()->format('Y-m-d');
        $response = $this->withAuth()
                        ->getJson("/api/bulk-purchase/history?start_date={$today}&end_date={$today}");

        $response->assertStatus(200);
    }

    /**
     * 测试获取批量采购统计 - 成功案例
     */
    public function test_get_bulk_purchase_statistics_success(): void
    {
        $this->createBulkPurchaseOrders(5);

        $response = $this->withAuth()->getJson('/api/bulk-purchase/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'overview' => [
                            'total_orders',
                            'total_amount',
                            'total_items',
                            'average_order_value',
                            'total_discount_given'
                        ],
                        'status_breakdown' => [
                            '*' => [
                                'status',
                                'count',
                                'percentage',
                                'total_amount'
                            ]
                        ],
                        'monthly_trends' => [
                            '*' => [
                                'month',
                                'order_count',
                                'total_amount',
                                'average_value'
                            ]
                        ],
                        'top_products' => [
                            '*' => [
                                'sku',
                                'product_name',
                                'total_quantity',
                                'total_amount',
                                'order_count'
                            ]
                        ],
                        'discount_analysis' => [
                            'average_discount_percentage',
                            'total_discount_amount',
                            'discount_tiers' => [
                                '*' => [
                                    'tier',
                                    'order_count',
                                    'total_discount'
                                ]
                            ]
                        ]
                    ],
                    'timestamp'
                ]);

        $data = $response->json('data');
        $this->assertEquals('success', $response->json('status'));
        $this->assertGreaterThan(0, $data['overview']['total_orders']);
        $this->assertIsArray($data['status_breakdown']);
        $this->assertIsArray($data['monthly_trends']);
    }

    /**
     * 测试批量采购订单详情 - 成功案例
     */
    public function test_get_bulk_purchase_order_detail_success(): void
    {
        $order = $this->createBulkPurchaseOrders(1)->first();

        $response = $this->withAuth()->getJson("/api/bulk-purchase/{$order->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'order' => [
                            'id',
                            'order_number',
                            'quote_id',
                            'customer_name',
                            'customer_email',
                            'total_amount',
                            'final_amount',
                            'currency',
                            'status',
                            'payment_status',
                            'shipping_status',
                            'items' => [
                                '*' => [
                                    'sku',
                                    'product_name',
                                    'quantity',
                                    'unit_price',
                                    'discount_percentage',
                                    'subtotal'
                                ]
                            ],
                            'financial_summary' => [
                                'subtotal',
                                'discount_amount',
                                'tax_amount',
                                'shipping_cost',
                                'total_amount'
                            ],
                            'timeline' => [
                                '*' => [
                                    'status',
                                    'timestamp',
                                    'notes'
                                ]
                            ],
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $orderData = $response->json('data.order');
        $this->assertEquals($order->id, $orderData['id']);
        $this->assertIsArray($orderData['items']);
        $this->assertIsArray($orderData['timeline']);
    }

    /**
     * 测试批量采购订单状态更新 - 管理员权限
     */
    public function test_update_bulk_purchase_order_status_admin(): void
    {
        $order = $this->createBulkPurchaseOrders(1)->first();

        $response = $this->withAdminAuth()
                        ->patchJson("/api/bulk-purchase/{$order->id}/status", [
                            'status' => 'confirmed',
                            'notes' => 'Order confirmed, preparing for shipment'
                        ]);

        $response->assertStatus(200);

        // 验证状态已更新
        $order->refresh();
        $this->assertEquals('confirmed', $order->status);
    }

    /**
     * 测试批量采购订单状态更新 - 普通用户无权限
     */
    public function test_update_bulk_purchase_order_status_unauthorized(): void
    {
        $order = $this->createBulkPurchaseOrders(1)->first();

        $response = $this->withAuth()
                        ->patchJson("/api/bulk-purchase/{$order->id}/status", [
                            'status' => 'confirmed'
                        ]);

        $response->assertStatus(403);
    }

    /**
     * 测试批量采购订单取消
     */
    public function test_cancel_bulk_purchase_order(): void
    {
        $order = $this->createBulkPurchaseOrders(1)->first();

        $response = $this->withAuth()
                        ->patchJson("/api/bulk-purchase/{$order->id}/cancel", [
                            'reason' => 'Business requirements changed'
                        ]);

        $response->assertStatus(200);

        // 验证订单状态已更新
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // 验证库存已恢复
        foreach ($order->items as $item) {
            $product = Product::where('sku', $item->sku)->first();
            $this->assertGreaterThan($product->stock_quantity, $item->quantity);
        }
    }

    /**
     * 测试批量采购订单导出
     */
    public function test_export_bulk_purchase_orders(): void
    {
        $this->createBulkPurchaseOrders(3);

        $response = $this->withAdminAuth()
                        ->getJson('/api/bulk-purchase/export?format=csv');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /**
     * 测试批量采购缓存功能
     */
    public function test_bulk_purchase_caching(): void
    {
        $items = [];
        foreach ($this->testProducts->take(2) as $product) {
            $items[] = [
                'sku' => $product->sku,
                'quantity' => 50
            ];
        }

        $quoteData = [
            'customer_name' => 'Cache Test',
            'customer_email' => 'cache@example.com',
            'items' => $items
        ];

        // 第一次请求
        $response1 = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);
        $response1->assertStatus(200);

        // 检查缓存
        $quoteId = $response1->json('data.quote.quote_id');
        $cacheKey = 'bulk_quote_' . $quoteId;
        $this->assertTrue(Cache::has($cacheKey));

        // 第二次请求相同报价应该从缓存获取
        $response2 = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);
        $response2->assertStatus(200);
        
        // 验证两个响应的报价ID相同（表示使用了缓存）
        $this->assertEquals(
            $response1->json('data.quote.quote_id'),
            $response2->json('data.quote.quote_id')
        );
    }

    /**
     * 测试折扣计算引擎
     */
    public function test_discount_calculation_engine(): void
    {
        // 测试不同数量级别的折扣
        $testCases = [
            ['quantity' => 10, 'expected_discount' => 2],   // 2% 折扣
            ['quantity' => 100, 'expected_discount' => 5],  // 5% 折扣
            ['quantity' => 500, 'expected_discount' => 10], // 10% 折扣
            ['quantity' => 1000, 'expected_discount' => 15] // 15% 折扣
        ];

        foreach ($testCases as $testCase) {
            $items = [
                ['sku' => $this->testProducts->first()->sku, 'quantity' => $testCase['quantity']]
            ];

            $quoteData = [
                'customer_name' => 'Discount Test',
                'customer_email' => 'discount@example.com',
                'items' => $items
            ];

            $response = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);

            $response->assertStatus(200);
            
            $quote = $response->json('data.quote');
            $actualDiscount = $quote['total_discount'] / $quote['total_amount'] * 100;
            
            $this->assertEqualsWithDelta(
                $testCase['expected_discount'], 
                $actualDiscount, 
                0.5, 
                "Discount for quantity {$testCase['quantity']} should be approximately {$testCase['expected_discount']}%"
            );
        }
    }

    /**
     * 测试批量采购性能
     */
    public function test_bulk_purchase_performance(): void
    {
        $items = [];
        foreach ($this->testProducts->take(20) as $product) {
            $items[] = [
                'sku' => $product->sku,
                'quantity' => 50
            ];
        }

        $quoteData = [
            'customer_name' => 'Performance Test',
            'customer_email' => 'perf@example.com',
            'items' => $items
        ];

        $startTime = microtime(true);
        
        $response = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(2000, $responseTime, 'Bulk purchase quote should be under 2000ms even for 20 items');
    }

    /**
     * 测试并发批量采购报价请求
     */
    public function test_concurrent_bulk_purchase_quotes(): void
    {
        $items = [
            ['sku' => $this->testProducts->first()->sku, 'quantity' => 100]
        ];

        $quoteData = [
            'customer_name' => 'Concurrent Test',
            'customer_email' => 'concurrent@example.com',
            'items' => $items
        ];

        $responses = [];
        
        // 模拟并发请求
        for ($i = 0; $i < 5; $i++) {
            $quoteData['customer_email'] = "concurrent{$i}@example.com";
            $responses[] = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);
        }

        // 验证所有请求都成功
        foreach ($responses as $response) {
            $response->assertStatus(200);
            $this->assertApiResponseFormat($response);
        }

        // 验证每个报价都有唯一的ID
        $quoteIds = array_map(function($response) {
            return $response->json('data.quote.quote_id');
        }, $responses);

        $uniqueQuoteIds = array_unique($quoteIds);
        $this->assertEquals(5, count($uniqueQuoteIds));
    }

    /**
     * 辅助方法：创建批量采购订单
     */
    protected function createBulkPurchaseOrders($count = 1)
    {
        $orders = collect();
        
        for ($i = 0; $i < $count; $i++) {
            $items = [];
            foreach ($this->testProducts->take(3) as $product) {
                $items[] = [
                    'sku' => $product->sku,
                    'quantity' => 50 + ($i * 10)
                ];
            }

            $quoteData = [
                'customer_name' => "Bulk Buyer {$i}",
                'customer_email' => "bulk{$i}@example.com",
                'items' => $items
            ];

            $quoteResponse = $this->withAuth()->postJson('/api/bulk-purchase/quote', $quoteData);
            $quoteId = $quoteResponse->json('data.quote.quote_id');

            $orderData = [
                'quote_id' => $quoteId,
                'shipping_address' => "Address {$i}, Tokyo, Japan"
            ];

            $orderResponse = $this->withAuth()->postJson('/api/bulk-purchase', $orderData);
            $orders->push(Order::find($orderResponse->json('data.order.id')));
        }

        return $orders;
    }

    /**
     * 测试API错误处理
     */
    public function test_api_error_handling(): void
    {
        // 无效的订单ID
        $response = $this->withAuth()->getJson('/api/bulk-purchase/99999');
        $response->assertStatus(404);

        // 无效的状态值
        $order = $this->createBulkPurchaseOrders(1)->first();
        $response = $this->withAdminAuth()
                        ->patchJson("/api/bulk-purchase/{$order->id}/status", [
                            'status' => 'invalid_status'
                        ]);
        $response->assertStatus(422);

        // 空的商品列表
        $response = $this->withAuth()->postJson('/api/bulk-purchase/quote', [
            'customer_name' => 'Empty Items Test',
            'customer_email' => 'empty@example.com',
            'items' => []
        ]);
        $response->assertStatus(422);
    }
}