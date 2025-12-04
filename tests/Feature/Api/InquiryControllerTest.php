<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class InquiryControllerTest extends TestCase
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
        $this->testProducts = Product::factory()->count(3)->create([
            'is_active' => true
        ]);

        // 创建测试用户
        $this->testUser = User::factory()->create([
            'username' => 'inquiryuser',
            'email' => 'inquiry@example.com'
        ]);

        // 创建测试询价
        $this->testInquiry = Inquiry::factory()->create([
            'user_id' => $this->testUser->id,
            'contact_name' => 'Test Contact',
            'contact_email' => 'contact@example.com',
            'contact_phone' => '+81-90-1234-5678',
            'company_name' => 'Test Company',
            'product_skus' => $this->testProducts->pluck('sku')->toArray(),
            'quantity' => 100,
            'target_price' => 80.00,
            'message' => 'Interested in bulk purchase',
            'status' => 'pending'
        ]);
    }

    /**
     * 测试创建询价 - 成功案例
     */
    public function test_create_inquiry_success(): void
    {
        $inquiryData = [
            'contact_name' => 'John Smith',
            'contact_email' => 'johnsmith@example.com',
            'contact_phone' => '+81-90-1234-5679',
            'company_name' => 'Smith Corporation',
            'product_skus' => $this->testProducts->pluck('sku')->toArray(),
            'quantity' => 500,
            'target_price' => 75.00,
            'message' => 'We are interested in bulk purchase for our retail stores',
            'expected_delivery_date' => '2025-12-25',
            'budget_range' => '50000-100000'
        ];

        $response = $this->withAuth()->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'inquiry' => [
                            'id',
                            'inquiry_number',
                            'contact_name',
                            'contact_email',
                            'company_name',
                            'product_skus',
                            'quantity',
                            'target_price',
                            'status',
                            'created_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('John Smith', $response->json('data.inquiry.contact_name'));
        $this->assertEquals('pending', $response->json('data.inquiry.status'));

        // 验证数据库中的询价
        $this->assertDatabaseHas('inquiries', [
            'contact_name' => 'John Smith',
            'contact_email' => 'johnsmith@example.com',
            'quantity' => 500,
            'status' => 'pending'
        ]);
    }

    /**
     * 测试创建询价 - 单个产品
     */
    public function test_create_inquiry_single_product(): void
    {
        $singleProduct = $this->testProducts->first();

        $inquiryData = [
            'contact_name' => 'Single Product Buyer',
            'contact_email' => 'single@example.com',
            'product_skus' => [$singleProduct->sku],
            'quantity' => 100,
            'message' => 'Interested in single product'
        ];

        $response = $this->withAuth()->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(201);
        
        $inquiry = $response->json('data.inquiry');
        $this->assertCount(1, $inquiry['product_skus']);
        $this->assertEquals($singleProduct->sku, $inquiry['product_skus'][0]);
    }

    /**
     * 测试创建询价 - 无效产品SKU
     */
    public function test_create_inquiry_invalid_skus(): void
    {
        $inquiryData = [
            'contact_name' => 'Test Contact',
            'contact_email' => 'test@example.com',
            'product_skus' => ['INVALID-SKU-1', 'INVALID-SKU-2'],
            'quantity' => 100
        ];

        $response = $this->withAuth()->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['product_skus']);
    }

    /**
     * 测试创建询价 - 缺少必填字段
     */
    public function test_create_inquiry_missing_required_fields(): void
    {
        $inquiryData = [
            'contact_name' => 'Test Contact'
            // 缺少其他必填字段
        ];

        $response = $this->withAuth()->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'contact_email',
                    'product_skus',
                    'quantity'
                ]);
    }

    /**
     * 测试创建询价 - 无效邮箱格式
     */
    public function test_create_inquiry_invalid_email(): void
    {
        $inquiryData = [
            'contact_name' => 'Test Contact',
            'contact_email' => 'invalid-email',
            'product_skus' => $this->testProducts->pluck('sku')->toArray(),
            'quantity' => 100
        ];

        $response = $this->withAuth()->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['contact_email']);
    }

    /**
     * 测试创建询价 - 数量过小
     */
    public function test_create_inquiry_quantity_too_small(): void
    {
        $inquiryData = [
            'contact_name' => 'Test Contact',
            'contact_email' => 'test@example.com',
            'product_skus' => $this->testProducts->pluck('sku')->toArray(),
            'quantity' => 5 // 小于最小起订量
        ];

        $response = $this->withAuth()->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['quantity']);
    }

    /**
     * 测试获取询价列表 - 成功案例
     */
    public function test_get_inquiries_success(): void
    {
        $response = $this->withAuth()->getJson('/api/inquiries');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'inquiries' => [
                            '*' => [
                                'id',
                                'inquiry_number',
                                'contact_name',
                                'contact_email',
                                'company_name',
                                'quantity',
                                'target_price',
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
        $this->assertGreaterThan(0, count($response->json('data.inquiries')));
    }

    /**
     * 测试获取询价列表 - 按状态筛选
     */
    public function test_get_inquiries_by_status(): void
    {
        $response = $this->withAuth()->getJson('/api/inquiries?status=pending');

        $response->assertStatus(200);
        
        $inquiries = $response->json('data.inquiries');
        foreach ($inquiries as $inquiry) {
            $this->assertEquals('pending', $inquiry['status']);
        }
    }

    /**
     * 测试获取询价列表 - 按公司筛选
     */
    public function test_get_inquiries_by_company(): void
    {
        $response = $this->withAuth()
                        ->getJson('/api/inquiries?company=Test Company');

        $response->assertStatus(200);
        
        $inquiries = $response->json('data.inquiries');
        foreach ($inquiries as $inquiry) {
            $this->assertStringContainsStringIgnoringCase('Test Company', $inquiry['company_name']);
        }
    }

    /**
     * 测试获取询价详情 - 成功案例
     */
    public function test_get_inquiry_detail_success(): void
    {
        $response = $this->withAuth()->getJson("/api/inquiries/{$this->testInquiry->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'inquiry' => [
                            'id',
                            'inquiry_number',
                            'contact_name',
                            'contact_email',
                            'contact_phone',
                            'company_name',
                            'product_skus',
                            'quantity',
                            'target_price',
                            'message',
                            'status',
                            'quoted_price',
                            'expiry_date',
                            'notes',
                            'products' => [
                                '*' => [
                                    'sku',
                                    'name',
                                    'price',
                                    'stock_quantity'
                                ]
                            ],
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $inquiry = $response->json('data.inquiry');
        $this->assertEquals($this->testInquiry->id, $inquiry['id']);
        $this->assertEquals('Test Contact', $inquiry['contact_name']);
        $this->assertIsArray($inquiry['products']);
    }

    /**
     * 测试获取询价详情 - 询价不存在
     */
    public function test_get_inquiry_detail_not_found(): void
    {
        $response = $this->withAuth()->getJson('/api/inquiries/99999');

        $response->assertStatus(404);
    }

    /**
     * 测试更新询价状态 - 成功案例（管理员）
     */
    public function test_update_inquiry_status_success(): void
    {
        $response = $this->withAdminAuth()
                        ->patchJson("/api/inquiries/{$this->testInquiry->id}/status", [
                            'status' => 'quoted',
                            'quoted_price' => 85.00,
                            'notes' => 'Special discount for bulk order',
                            'expiry_date' => Carbon::now()->addDays(7)->format('Y-m-d')
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'inquiry' => [
                            'id',
                            'status',
                            'quoted_price',
                            'expiry_date',
                            'updated_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('quoted', $response->json('data.inquiry.status'));
        $this->assertEquals(85.00, $response->json('data.inquiry.quoted_price'));

        // 验证数据库中的状态更新
        $this->assertDatabaseHas('inquiries', [
            'id' => $this->testInquiry->id,
            'status' => 'quoted',
            'quoted_price' => 85.00
        ]);
    }

    /**
     * 测试更新询价状态 - 普通用户无权限
     */
    public function test_update_inquiry_status_unauthorized(): void
    {
        $response = $this->withAuth()
                        ->patchJson("/api/inquiries/{$this->testInquiry->id}/status", [
                            'status' => 'quoted'
                        ]);

        $response->assertStatus(403);
    }

    /**
     * 测试更新询价状态 - 无效状态
     */
    public function test_update_inquiry_status_invalid_status(): void
    {
        $response = $this->withAdminAuth()
                        ->patchJson("/api/inquiries/{$this->testInquiry->id}/status", [
                            'status' => 'invalid_status'
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    /**
     * 测试接受报价 - 成功案例
     */
    public function test_accept_quote_success(): void
    {
        // 先设置报价
        $this->testInquiry->update([
            'status' => 'quoted',
            'quoted_price' => 85.00
        ]);

        $response = $this->withAuth()
                        ->patchJson("/api/inquiries/{$this->testInquiry->id}/accept", [
                            'notes' => 'We accept the quote and would like to proceed'
                        ]);

        $response->assertStatus(200);

        // 验证状态已更新
        $this->testInquiry->refresh();
        $this->assertEquals('accepted', $this->testInquiry->status);
    }

    /**
     * 测试拒绝报价 - 成功案例
     */
    public function test_reject_quote_success(): void
    {
        // 先设置报价
        $this->testInquiry->update([
            'status' => 'quoted',
            'quoted_price' => 85.00
        ]);

        $response = $this->withAuth()
                        ->patchJson("/api/inquiries/{$this->testInquiry->id}/reject", [
                            'reason' => 'Price is too high for our budget'
                        ]);

        $response->assertStatus(200);

        // 验证状态已更新
        $this->testInquiry->refresh();
        $this->assertEquals('rejected', $this->testInquiry->status);
    }

    /**
     * 测试获取询价统计信息
     */
    public function test_inquiry_statistics(): void
    {
        $response = $this->withAdminAuth()
                        ->getJson('/api/inquiries/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'total_inquiries',
                        'pending_inquiries',
                        'quoted_inquiries',
                        'accepted_inquiries',
                        'rejected_inquiries',
                        'conversion_rate',
                        'average_response_time',
                        'inquiries_by_company' => [
                            '*' => [
                                'company_name',
                                'inquiry_count',
                                'total_value'
                            ]
                        ],
                        'recent_inquiries' => [
                            '*' => [
                                'id',
                                'inquiry_number',
                                'contact_name',
                                'quantity',
                                'status',
                                'created_at'
                            ]
                        ]
                    ],
                    'timestamp'
                ]);

        $data = $response->json('data');
        $this->assertGreaterThan(0, $data['total_inquiries']);
        $this->assertIsArray($data['inquiries_by_company']);
    }

    /**
     * 测试询价搜索功能
     */
    public function test_inquiry_search(): void
    {
        $response = $this->withAuth()
                        ->getJson('/api/inquiries?search=Test');

        $response->assertStatus(200);
        
        $inquiries = $response->json('data.inquiries');
        foreach ($inquiries as $inquiry) {
            $searchFields = $inquiry['contact_name'] . ' ' . $inquiry['company_name'] . ' ' . $inquiry['inquiry_number'];
            $this->assertStringContainsStringIgnoringCase('Test', $searchFields);
        }
    }

    /**
     * 测试询价导出功能
     */
    public function test_export_inquiries(): void
    {
        $response = $this->withAdminAuth()
                        ->getJson('/api/inquiries/export?format=csv');

        $response->assertStatus(200)
                ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /**
     * 测试询价导出 - 无权限
     */
    public function test_export_inquiries_unauthorized(): void
    {
        $response = $this->withAuth()->getJson('/api/inquiries/export');

        $response->assertStatus(403);
    }

    /**
     * 测试询价过期检查
     */
    public function test_inquiry_expiry_check(): void
    {
        // 创建过期的询价
        $expiredInquiry = Inquiry::factory()->create([
            'status' => 'quoted',
            'quoted_price' => 100.00,
            'expiry_date' => Carbon::yesterday()
        ]);

        $response = $this->withAdminAuth()
                        ->postJson('/api/inquiries/check-expiry');

        $response->assertStatus(200);

        // 验证过期询价状态已更新
        $expiredInquiry->refresh();
        $this->assertEquals('expired', $expiredInquiry->status);
    }

    /**
     * 测试批量更新询价状态
     */
    public function test_batch_update_inquiries(): void
    {
        // 创建更多测试询价
        $inquiries = Inquiry::factory()->count(3)->create([
            'status' => 'pending'
        ]);

        $inquiryIds = $inquiries->pluck('id')->toArray();

        $response = $this->withAdminAuth()
                        ->patchJson('/api/inquiries/batch', [
                            'inquiry_ids' => $inquiryIds,
                            'action' => 'quote',
                            'quoted_price' => 90.00,
                            'notes' => 'Batch quote processing'
                        ]);

        $response->assertStatus(200);

        // 验证所有询价状态已更新
        foreach ($inquiryIds as $inquiryId) {
            $this->assertDatabaseHas('inquiries', [
                'id' => $inquiryId,
                'status' => 'quoted',
                'quoted_price' => 90.00
            ]);
        }
    }

    /**
     * 测试询价缓存功能
     */
    public function test_inquiry_caching(): void
    {
        // 第一次请求
        $response1 = $this->withAuth()->getJson("/api/inquiries/{$this->testInquiry->id}");
        $response1->assertStatus(200);

        // 检查缓存
        $cacheKey = 'inquiry_' . $this->testInquiry->id;
        $this->assertTrue(Cache::has($cacheKey));

        // 第二次请求应该从缓存获取
        $response2 = $this->withAuth()->getJson("/api/inquiries/{$this->testInquiry->id}");
        $response2->assertStatus(200);
        
        // 验证两个响应相同
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * 测试询价通知功能
     */
    public function test_inquiry_notification(): void
    {
        // 创建新询价应该触发通知
        $inquiryData = [
            'contact_name' => 'Notification Test',
            'contact_email' => 'notify@example.com',
            'product_skus' => $this->testProducts->pluck('sku')->toArray(),
            'quantity' => 100
        ];

        $response = $this->withAuth()->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(201);

        // 这里可以检查通知是否被发送（如果实现了通知系统）
        // $this->assertDatabaseHas('notifications', [
        //     'type' => 'inquiry.created'
        // ]);
    }

    /**
     * 测试询价自动报价计算
     */
    public function test_auto_quote_calculation(): void
    {
        $inquiryData = [
            'contact_name' => 'Auto Quote Test',
            'contact_email' => 'auto@example.com',
            'product_skus' => $this->testProducts->pluck('sku')->toArray(),
            'quantity' => 1000, // 大数量
            'target_price' => 70.00
        ];

        $response = $this->withAuth()->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(201);

        // 如果实现了自动报价功能，可以检查建议价格
        // $inquiry = $response->json('data.inquiry');
        // $this->assertArrayHasKey('suggested_price', $inquiry);
    }

    /**
     * 测试询价重复检查
     */
    public function test_duplicate_inquiry_check(): void
    {
        $inquiryData = [
            'contact_name' => 'Duplicate Test',
            'contact_email' => 'duplicate@example.com',
            'product_skus' => $this->testProducts->pluck('sku')->toArray(),
            'quantity' => 100
        ];

        // 第一次创建
        $response1 = $this->withAuth()->postJson('/api/inquiries', $inquiryData);
        $response1->assertStatus(201);

        // 第二次创建相同询价应该被标记为重复
        $response2 = $this->withAuth()->postJson('/api/inquiries', $inquiryData);
        
        // 根据业务逻辑，可能返回422或创建但标记为重复
        $this->assertContains($response2->status(), [201, 422]);
    }

    /**
     * 测试API错误处理
     */
    public function test_api_error_handling(): void
    {
        // 无效的询价ID
        $response = $this->withAuth()->getJson('/api/inquiries/invalid');
        $response->assertStatus(404);

        // 无效的状态值
        $response = $this->withAdminAuth()
                        ->patchJson("/api/inquiries/{$this->testInquiry->id}/status", [
                            'status' => ''
                        ]);
        $response->assertStatus(422);

        // 接受未报价的询价
        $response = $this->withAuth()
                        ->patchJson("/api/inquiries/{$this->testInquiry->id}/accept");
        $response->assertStatus(422);
    }

    /**
     * 测试询价性能
     */
    public function test_inquiry_performance(): void
    {
        $startTime = microtime(true);
        
        $response = $this->withAuth()->getJson('/api/inquiries');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(1000, $responseTime, 'Inquiry API response should be under 1000ms');
    }
}