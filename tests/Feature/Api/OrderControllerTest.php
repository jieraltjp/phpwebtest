<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Shipment;
use Illuminate\Support\Facades\Cache;

class OrderControllerTest extends TestCase
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
        // 创建测试产品
        $this->testProduct = Product::factory()->create([
            'sku' => 'ORDER-TEST-001',
            'name' => 'Test Product for Order',
            'price' => 100.00,
            'stock_quantity' => 100,
            'is_active' => true
        ]);

        // 创建测试用户
        $this->testUser = User::factory()->create([
            'username' => 'orderuser',
            'email' => 'order@example.com'
        ]);

        // 创建测试订单
        $this->testOrder = Order::factory()->create([
            'user_id' => $this->testUser->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'total_amount' => 200.00,
            'currency' => 'CNY',
            'status' => 'pending'
        ]);

        // 创建订单项
        OrderItem::factory()->create([
            'order_id' => $this->testOrder->id,
            'product_id' => $this->testProduct->id,
            'sku' => $this->testProduct->sku,
            'quantity' => 2,
            'price' => 100.00,
            'total' => 200.00
        ]);
    }

    /**
     * 测试创建订单 - 成功案例
     */
    public function test_create_order_success(): void
    {
        $orderData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+81-90-1234-5678',
            'shipping_address' => '123 Test St, Tokyo, Japan',
            'billing_address' => '123 Test St, Tokyo, Japan',
            'items' => [
                [
                    'sku' => $this->testProduct->sku,
                    'quantity' => 1,
                    'price' => 100.00
                ]
            ],
            'currency' => 'CNY',
            'notes' => 'Test order notes'
        ];

        $response = $this->withAuth()->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'order' => [
                            'id',
                            'order_number',
                            'customer_name',
                            'customer_email',
                            'total_amount',
                            'currency',
                            'status',
                            'items' => [
                                '*' => [
                                    'id',
                                    'sku',
                                    'quantity',
                                    'price',
                                    'total'
                                ]
                            ],
                            'created_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('John Doe', $response->json('data.order.customer_name'));
        $this->assertEquals('pending', $response->json('data.order.status'));

        // 验证数据库中的订单
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'total_amount' => 100.00
        ]);

        // 验证库存已扣减
        $this->testProduct->refresh();
        $this->assertEquals(99, $this->testProduct->stock_quantity);
    }

    /**
     * 测试创建订单 - 多个商品
     */
    public function test_create_order_multiple_items(): void
    {
        // 创建更多测试产品
        $product2 = Product::factory()->create([
            'sku' => 'ORDER-TEST-002',
            'price' => 50.00,
            'stock_quantity' => 50
        ]);

        $orderData = [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'shipping_address' => '456 Test St, Tokyo, Japan',
            'items' => [
                [
                    'sku' => $this->testProduct->sku,
                    'quantity' => 2,
                    'price' => 100.00
                ],
                [
                    'sku' => $product2->sku,
                    'quantity' => 3,
                    'price' => 50.00
                ]
            ],
            'currency' => 'CNY'
        ];

        $response = $this->withAuth()->postJson('/api/orders', $orderData);

        $response->assertStatus(201);
        
        $order = $response->json('data.order');
        $this->assertEquals(350.00, $order['total_amount']); // 2*100 + 3*50
        $this->assertCount(2, $order['items']);
    }

    /**
     * 测试创建订单 - 库存不足
     */
    public function test_create_order_insufficient_stock(): void
    {
        // 创建库存不足的产品
        $lowStockProduct = Product::factory()->create([
            'sku' => 'LOW-STOCK-001',
            'stock_quantity' => 1
        ]);

        $orderData = [
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'shipping_address' => '123 Test St',
            'items' => [
                [
                    'sku' => $lowStockProduct->sku,
                    'quantity' => 5, // 超过库存
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->withAuth()->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /**
     * 测试创建订单 - 无效商品SKU
     */
    public function test_create_order_invalid_sku(): void
    {
        $orderData = [
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'shipping_address' => '123 Test St',
            'items' => [
                [
                    'sku' => 'INVALID-SKU',
                    'quantity' => 1,
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->withAuth()->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.sku']);
    }

    /**
     * 测试创建订单 - 缺少必填字段
     */
    public function test_create_order_missing_required_fields(): void
    {
        $orderData = [
            'customer_name' => 'Test Customer'
            // 缺少其他必填字段
        ];

        $response = $this->withAuth()->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'customer_email',
                    'shipping_address',
                    'items'
                ]);
    }

    /**
     * 测试获取订单列表 - 成功案例
     */
    public function test_get_orders_success(): void
    {
        $response = $this->withAuth()->getJson('/api/orders');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'orders' => [
                            '*' => [
                                'id',
                                'order_number',
                                'customer_name',
                                'customer_email',
                                'total_amount',
                                'currency',
                                'status',
                                'created_at'
                            ]
                        ],
                        'pagination' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page'
                        ]
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertGreaterThan(0, count($response->json('data.orders')));
    }

    /**
     * 测试获取订单列表 - 带状态筛选
     */
    public function test_get_orders_by_status(): void
    {
        $response = $this->withAuth()->getJson('/api/orders?status=pending');

        $response->assertStatus(200);
        
        $orders = $response->json('data.orders');
        foreach ($orders as $order) {
            $this->assertEquals('pending', $order['status']);
        }
    }

    /**
     * 测试获取订单列表 - 按客户邮箱筛选
     */
    public function test_get_orders_by_customer_email(): void
    {
        $response = $this->withAuth()
                        ->getJson('/api/orders?customer_email=customer@example.com');

        $response->assertStatus(200);
        
        $orders = $response->json('data.orders');
        foreach ($orders as $order) {
            $this->assertEquals('customer@example.com', $order['customer_email']);
        }
    }

    /**
     * 测试获取订单列表 - 日期范围筛选
     */
    public function test_get_orders_by_date_range(): void
    {
        $today = now()->format('Y-m-d');
        $response = $this->withAuth()
                        ->getJson("/api/orders?start_date={$today}&end_date={$today}");

        $response->assertStatus(200);
    }

    /**
     * 测试获取订单详情 - 成功案例
     */
    public function test_get_order_detail_success(): void
    {
        $response = $this->withAuth()->getJson("/api/orders/{$this->testOrder->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'order' => [
                            'id',
                            'order_number',
                            'customer_name',
                            'customer_email',
                            'customer_phone',
                            'shipping_address',
                            'billing_address',
                            'total_amount',
                            'currency',
                            'status',
                            'payment_status',
                            'items' => [
                                '*' => [
                                    'id',
                                    'sku',
                                    'product_name',
                                    'quantity',
                                    'price',
                                    'total'
                                ]
                            ],
                            'shipments' => [
                                '*' => [
                                    'id',
                                    'tracking_number',
                                    'status',
                                    'shipped_at'
                                ]
                            ],
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $order = $response->json('data.order');
        $this->assertEquals($this->testOrder->id, $order['id']);
        $this->assertEquals('Test Customer', $order['customer_name']);
    }

    /**
     * 测试获取订单详情 - 订单不存在
     */
    public function test_get_order_detail_not_found(): void
    {
        $response = $this->withAuth()->getJson('/api/orders/99999');

        $response->assertStatus(404);
    }

    /**
     * 测试获取订单详情 - 无权限访问
     */
    public function test_get_order_detail_unauthorized(): void
    {
        // 创建其他用户的订单
        $otherUser = User::factory()->create();
        $otherOrder = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withAuth()->getJson("/api/orders/{$otherOrder->id}");

        $response->assertStatus(403);
    }

    /**
     * 测试更新订单状态 - 成功案例
     */
    public function test_update_order_status_success(): void
    {
        $response = $this->withAdminAuth()
                        ->patchJson("/api/orders/{$this->testOrder->id}/status", [
                            'status' => 'confirmed',
                            'notes' => 'Order confirmed by admin'
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'order' => [
                            'id',
                            'status',
                            'updated_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('confirmed', $response->json('data.order.status'));

        // 验证数据库中的状态更新
        $this->assertDatabaseHas('orders', [
            'id' => $this->testOrder->id,
            'status' => 'confirmed'
        ]);
    }

    /**
     * 测试更新订单状态 - 无效状态
     */
    public function test_update_order_status_invalid_status(): void
    {
        $response = $this->withAdminAuth()
                        ->patchJson("/api/orders/{$this->testOrder->id}/status", [
                            'status' => 'invalid_status'
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    /**
     * 测试更新订单状态 - 普通用户无权限
     */
    public function test_update_order_status_unauthorized(): void
    {
        $response = $this->withAuth()
                        ->patchJson("/api/orders/{$this->testOrder->id}/status", [
                            'status' => 'confirmed'
                        ]);

        $response->assertStatus(403);
    }

    /**
     * 测试取消订单 - 成功案例
     */
    public function test_cancel_order_success(): void
    {
        $response = $this->withAuth()
                        ->patchJson("/api/orders/{$this->testOrder->id}/cancel", [
                            'reason' => 'Customer request'
                        ]);

        $response->assertStatus(200);

        // 验证订单状态已更新
        $this->testOrder->refresh();
        $this->assertEquals('cancelled', $this->testOrder->status);

        // 验证库存已恢复
        $this->testProduct->refresh();
        $this->assertEquals(102, $this->testProduct->stock_quantity); // 恢复2个库存
    }

    /**
     * 测试取消订单 - 已确认订单无法取消
     */
    public function test_cancel_confirmed_order(): void
    {
        // 先确认订单
        $this->testOrder->update(['status' => 'confirmed']);

        $response = $this->withAuth()
                        ->patchJson("/api/orders/{$this->testOrder->id}/cancel");

        $response->assertStatus(422);
    }

    /**
     * 测试获取物流追踪链接
     */
    public function test_get_tracking_link(): void
    {
        // 创建物流记录
        $shipment = Shipment::factory()->create([
            'order_id' => $this->testOrder->id,
            'tracking_number' => 'TRACK123456',
            'carrier' => 'TestCarrier'
        ]);

        $response = $this->withAuth()
                        ->getJson("/api/orders/{$this->testOrder->id}/tracking-link");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'tracking_number',
                        'carrier',
                        'tracking_url',
                        'status',
                        'estimated_delivery'
                    ],
                    'timestamp'
                ]);

        $data = $response->json('data');
        $this->assertEquals('TRACK123456', $data['tracking_number']);
    }

    /**
     * 测试获取订单统计信息
     */
    public function test_order_statistics(): void
    {
        $response = $this->withAdminAuth()
                        ->getJson('/api/orders/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'total_orders',
                        'total_revenue',
                        'average_order_value',
                        'orders_by_status' => [
                            '*' => [
                                'status',
                                'count'
                            ]
                        ],
                        'recent_orders' => [
                            '*' => [
                                'id',
                                'order_number',
                                'total_amount',
                                'created_at'
                            ]
                        ]
                    ],
                    'timestamp'
                ]);

        $data = $response->json('data');
        $this->assertGreaterThan(0, $data['total_orders']);
        $this->assertIsArray($data['orders_by_status']);
    }

    /**
     * 测试订单导出功能
     */
    public function test_export_orders(): void
    {
        $response = $this->withAdminAuth()
                        ->getJson('/api/orders/export?format=csv');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /**
     * 测试订单导出 - 无权限
     */
    public function test_export_orders_unauthorized(): void
    {
        $response = $this->withAuth()->getJson('/api/orders/export');

        $response->assertStatus(403);
    }

    /**
     * 测试订单搜索功能
     */
    public function test_order_search(): void
    {
        $response = $this->withAuth()
                        ->getJson('/api/orders?search=Test');

        $response->assertStatus(200);
        
        $orders = $response->json('data.orders');
        foreach ($orders as $order) {
            $searchFields = $order['customer_name'] . ' ' . $order['customer_email'] . ' ' . $order['order_number'];
            $this->assertStringContainsStringIgnoringCase('Test', $searchFields);
        }
    }

    /**
     * 测试订单缓存功能
     */
    public function test_order_caching(): void
    {
        // 第一次请求
        $response1 = $this->withAuth()->getJson("/api/orders/{$this->testOrder->id}");
        $response1->assertStatus(200);

        // 检查缓存
        $cacheKey = 'order_' . $this->testOrder->id;
        $this->assertTrue(Cache::has($cacheKey));

        // 第二次请求应该从缓存获取
        $response2 = $this->withAuth()->getJson("/api/orders/{$this->testOrder->id}");
        $response2->assertStatus(200);
        
        // 验证两个响应相同
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * 测试订单状态变更通知
     */
    public function test_order_status_notification(): void
    {
        // 更新订单状态应该触发通知
        $response = $this->withAdminAuth()
                        ->patchJson("/api/orders/{$this->testOrder->id}/status", [
                            'status' => 'shipped'
                        ]);

        $response->assertStatus(200);

        // 这里可以检查通知是否被发送（如果实现了通知系统）
        // $this->assertDatabaseHas('notifications', [
        //     'notifiable_type' => 'App\Models\User',
        //     'notifiable_id' => $this->testUser->id
        // ]);
    }

    /**
     * 测试订单计算逻辑
     */
    public function test_order_calculation(): void
    {
        $orderData = [
            'customer_name' => 'Calculation Test',
            'customer_email' => 'calc@example.com',
            'shipping_address' => '123 Test St',
            'items' => [
                [
                    'sku' => $this->testProduct->sku,
                    'quantity' => 3,
                    'price' => 100.00
                ]
            ],
            'currency' => 'CNY',
            'shipping_cost' => 10.00,
            'tax_rate' => 0.10,
            'discount' => 20.00
        ];

        $response = $this->withAuth()->postJson('/api/orders', $orderData);

        $response->assertStatus(201);
        
        $order = $response->json('data.order');
        $expectedTotal = (3 * 100.00) + 10.00 + (30.00 * 0.10) - 20.00; // 320.00
        $this->assertEquals($expectedTotal, $order['total_amount']);
    }

    /**
     * 测试批量操作订单
     */
    public function test_batch_update_orders(): void
    {
        // 创建更多测试订单
        $orders = Order::factory()->count(3)->create([
            'status' => 'pending'
        ]);

        $orderIds = $orders->pluck('id')->toArray();

        $response = $this->withAdminAuth()
                        ->patchJson('/api/orders/batch', [
                            'order_ids' => $orderIds,
                            'action' => 'confirm',
                            'notes' => 'Batch confirmed'
                        ]);

        $response->assertStatus(200);

        // 验证所有订单状态已更新
        foreach ($orderIds as $orderId) {
            $this->assertDatabaseHas('orders', [
                'id' => $orderId,
                'status' => 'confirmed'
            ]);
        }
    }

    /**
     * 测试API错误处理
     */
    public function test_api_error_handling(): void
    {
        // 无效的订单ID
        $response = $this->withAuth()->getJson('/api/orders/invalid');
        $response->assertStatus(404);

        // 无效的状态值
        $response = $this->withAdminAuth()
                        ->patchJson("/api/orders/{$this->testOrder->id}/status", [
                            'status' => ''
                        ]);
        $response->assertStatus(422);
    }
}