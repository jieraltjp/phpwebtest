<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ProductControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建测试产品数据
        $this->createTestProducts();
    }

    /**
     * 创建测试产品
     */
    protected function createTestProducts(): void
    {
        Product::factory()->createMany([
            [
                'sku' => 'TEST-SKU-001',
                'name' => 'Test Laptop',
                'description' => 'High performance laptop',
                'price' => 999.99,
                'currency' => 'CNY',
                'stock_quantity' => 50,
                'category' => 'electronics',
                'brand' => 'TestBrand',
                'is_active' => true
            ],
            [
                'sku' => 'TEST-SKU-002',
                'name' => 'Test Mouse',
                'description' => 'Wireless optical mouse',
                'price' => 29.99,
                'currency' => 'CNY',
                'stock_quantity' => 100,
                'category' => 'electronics',
                'brand' => 'TestBrand',
                'is_active' => true
            ],
            [
                'sku' => 'TEST-SKU-003',
                'name' => 'Test Keyboard',
                'description' => 'Mechanical keyboard',
                'price' => 79.99,
                'currency' => 'CNY',
                'stock_quantity' => 0, // 缺货
                'category' => 'electronics',
                'brand' => 'TestBrand',
                'is_active' => false // 非活跃
            ]
        ]);
    }

    /**
     * 测试获取产品列表 - 成功案例
     */
    public function test_get_products_success(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'products' => [
                            '*' => [
                                'id',
                                'sku',
                                'name',
                                'description',
                                'price',
                                'currency',
                                'stock_quantity',
                                'category',
                                'brand',
                                'is_active',
                                'created_at',
                                'updated_at'
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
        $this->assertGreaterThan(0, count($response->json('data.products')));
    }

    /**
     * 测试获取产品列表 - 带分页
     */
    public function test_get_products_with_pagination(): void
    {
        $response = $this->getJson('/api/products?page=1&per_page=2');

        $response->assertStatus(200);
        
        $products = $response->json('data.products');
        $pagination = $response->json('data.pagination');
        
        $this->assertCount(2, $products);
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(2, $pagination['per_page']);
    }

    /**
     * 测试获取产品列表 - 带搜索
     */
    public function test_get_products_with_search(): void
    {
        $response = $this->getJson('/api/products?search=Laptop');

        $response->assertStatus(200);
        
        $products = $response->json('data.products');
        $this->assertGreaterThan(0, count($products));
        
        // 验证搜索结果包含关键词
        foreach ($products as $product) {
            $this->assertStringContainsStringIgnoringCase('Laptop', 
                $product['name'] . ' ' . $product['description']);
        }
    }

    /**
     * 测试获取产品列表 - 按分类筛选
     */
    public function test_get_products_by_category(): void
    {
        $response = $this->getJson('/api/products?category=electronics');

        $response->assertStatus(200);
        
        $products = $response->json('data.products');
        foreach ($products as $product) {
            $this->assertEquals('electronics', $product['category']);
        }
    }

    /**
     * 测试获取产品列表 - 价格范围筛选
     */
    public function test_get_products_by_price_range(): void
    {
        $response = $this->getJson('/api/products?min_price=50&max_price=800');

        $response->assertStatus(200);
        
        $products = $response->json('data.products');
        foreach ($products as $product) {
            $this->assertGreaterThanOrEqual(50, $product['price']);
            $this->assertLessThanOrEqual(800, $product['price']);
        }
    }

    /**
     * 测试获取产品列表 - 仅显示有库存
     */
    public function test_get_products_in_stock_only(): void
    {
        $response = $this->getJson('/api/products?in_stock=true');

        $response->assertStatus(200);
        
        $products = $response->json('data.products');
        foreach ($products as $product) {
            $this->assertGreaterThan(0, $product['stock_quantity']);
        }
    }

    /**
     * 测试获取产品列表 - 仅显示活跃产品
     */
    public function test_get_products_active_only(): void
    {
        $response = $this->getJson('/api/products?active=true');

        $response->assertStatus(200);
        
        $products = $response->json('data.products');
        foreach ($products as $product) {
            $this->assertTrue($product['is_active']);
        }
    }

    /**
     * 测试获取产品列表 - 排序
     */
    public function test_get_products_sorted(): void
    {
        // 按价格升序
        $response = $this->getJson('/api/products?sort=price&order=asc');
        
        $products = $response->json('data.products');
        for ($i = 1; $i < count($products); $i++) {
            $this->assertLessThanOrEqual(
                $products[$i]['price'], 
                $products[$i-1]['price']
            );
        }

        // 按价格降序
        $response = $this->getJson('/api/products?sort=price&order=desc');
        
        $products = $response->json('data.products');
        for ($i = 1; $i < count($products); $i++) {
            $this->assertGreaterThanOrEqual(
                $products[$i]['price'], 
                $products[$i-1]['price']
            );
        }
    }

    /**
     * 测试获取产品详情 - 成功案例
     */
    public function test_get_product_detail_success(): void
    {
        $product = Product::where('sku', 'TEST-SKU-001')->first();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'sku',
                        'name',
                        'description',
                        'price',
                        'currency',
                        'stock_quantity',
                        'category',
                        'brand',
                        'weight',
                        'dimensions',
                        'is_active',
                        'created_at',
                        'updated_at'
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('TEST-SKU-001', $response->json('data.sku'));
    }

    /**
     * 测试获取产品详情 - 产品不存在
     */
    public function test_get_product_detail_not_found(): void
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors',
                    'timestamp'
                ]);

        $this->assertEquals('error', $response->json('status'));
    }

    /**
     * 测试按SKU获取产品 - 成功案例
     */
    public function test_get_product_by_sku_success(): void
    {
        $response = $this->getJson('/api/products/sku/TEST-SKU-001');

        $response->assertStatus(200);
        $this->assertEquals('TEST-SKU-001', $response->json('data.sku'));
    }

    /**
     * 测试按SKU获取产品 - SKU不存在
     */
    public function test_get_product_by_sku_not_found(): void
    {
        $response = $this->getJson('/api/products/sku/NONEXISTENT-SKU');

        $response->assertStatus(404);
    }

    /**
     * 测试产品缓存功能
     */
    public function test_product_caching(): void
    {
        $product = Product::where('sku', 'TEST-SKU-001')->first();

        // 第一次请求
        $response1 = $this->getJson("/api/products/{$product->id}");
        $response1->assertStatus(200);

        // 检查缓存
        $cacheKey = 'product_' . $product->id;
        $this->assertTrue(Cache::has($cacheKey));

        // 第二次请求应该从缓存获取
        $response2 = $this->getJson("/api/products/{$product->id}");
        $response2->assertStatus(200);
        
        // 验证两个响应相同
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * 测试搜索结果缓存
     */
    public function test_search_results_caching(): void
    {
        $searchQuery = 'Laptop';
        
        // 第一次搜索
        $response1 = $this->getJson("/api/products?search={$searchQuery}");
        $response1->assertStatus(200);

        // 检查搜索缓存
        $cacheKey = 'search_' . md5($searchQuery);
        $this->assertTrue(Cache::has($cacheKey));

        // 第二次搜索应该从缓存获取
        $response2 = $this->getJson("/api/products?search={$searchQuery}");
        $response2->assertStatus(200);
        
        // 验证两个响应相同
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * 测试产品统计信息
     */
    public function test_product_statistics(): void
    {
        $response = $this->getJson('/api/products/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'total_products',
                        'active_products',
                        'in_stock_products',
                        'out_of_stock_products',
                        'categories' => [
                            '*' => [
                                'name',
                                'count'
                            ]
                        ],
                        'price_ranges' => [
                            'min_price',
                            'max_price',
                            'avg_price'
                        ]
                    ],
                    'timestamp'
                ]);

        $data = $response->json('data');
        $this->assertGreaterThan(0, $data['total_products']);
        $this->assertIsArray($data['categories']);
    }

    /**
     * 测试产品推荐
     */
    public function test_product_recommendations(): void
    {
        $product = Product::where('sku', 'TEST-SKU-001')->first();

        $response = $this->getJson("/api/products/{$product->id}/recommendations");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'recommendations' => [
                            '*' => [
                                'id',
                                'sku',
                                'name',
                                'price',
                                'reason'
                            ]
                        ]
                    ],
                    'timestamp'
                ]);

        $recommendations = $response->json('data.recommendations');
        $this->assertIsArray($recommendations);
    }

    /**
     * 测试产品库存检查
     */
    public function test_product_stock_check(): void
    {
        $product = Product::where('sku', 'TEST-SKU-001')->first();

        $response = $this->getJson("/api/products/{$product->id}/stock");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'sku',
                        'stock_quantity',
                        'is_available',
                        'estimated_delivery'
                    ],
                    'timestamp'
                ]);

        $data = $response->json('data');
        $this->assertEquals($product->sku, $data['sku']);
        $this->assertEquals($product->stock_quantity, $data['stock_quantity']);
    }

    /**
     * 测试产品价格历史
     */
    public function test_product_price_history(): void
    {
        $product = Product::where('sku', 'TEST-SKU-001')->first();

        $response = $this->getJson("/api/products/{$product->id}/price-history");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'current_price',
                        'currency',
                        'history' => [
                            '*' => [
                                'price',
                                'date',
                                'change_type'
                            ]
                        ]
                    ],
                    'timestamp'
                ]);
    }

    /**
     * 测试批量获取产品信息
     */
    public function test_batch_get_products(): void
    {
        $productIds = Product::limit(2)->pluck('id')->toArray();

        $response = $this->postJson('/api/products/batch', [
            'product_ids' => $productIds
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'products' => [
                            '*' => [
                                'id',
                                'sku',
                                'name',
                                'price',
                                'stock_quantity'
                            ]
                        ]
                    ],
                    'timestamp'
                ]);

        $products = $response->json('data.products');
        $this->assertCount(2, $products);
    }

    /**
     * 测试批量获取产品 - 无效ID
     */
    public function test_batch_get_products_with_invalid_ids(): void
    {
        $response = $this->postJson('/api/products/batch', [
            'product_ids' => [99999, 99998]
        ]);

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data.products'));
    }

    /**
     * 测试产品比较功能
     */
    public function test_product_comparison(): void
    {
        $productIds = Product::limit(3)->pluck('id')->toArray();

        $response = $this->postJson('/api/products/compare', [
            'product_ids' => $productIds
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'comparison' => [
                            'specifications' => [
                                '*' => [
                                    'name',
                                    'values' => [
                                        '*' => [
                                            'product_id',
                                            'value'
                                        ]
                                    ]
                                ]
                            ],
                            'products' => [
                                '*' => [
                                    'id',
                                    'sku',
                                    'name',
                                    'price'
                                ]
                            ]
                        ]
                    ],
                    'timestamp'
                ]);
    }

    /**
     * 测试API错误处理 - 无效参数
     */
    public function test_api_error_handling_invalid_params(): void
    {
        // 无效的分页参数
        $response = $this->getJson('/api/products?page=-1');
        $response->assertStatus(422);

        // 无效的价格范围
        $response = $this->getJson('/api/products?min_price=abc');
        $response->assertStatus(422);

        // 无效的排序字段
        $response = $this->getJson('/api/products?sort=invalid_field');
        $response->assertStatus(422);
    }

    /**
     * 测试API响应时间
     */
    public function test_api_response_time(): void
    {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/products');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // 转换为毫秒

        $response->assertStatus(200);
        $this->assertLessThan(1000, $responseTime, 'API response should be under 1000ms');
    }

    /**
     * 测试并发请求处理
     */
    public function test_concurrent_requests(): void
    {
        $responses = [];
        
        // 模拟并发请求
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/products');
        }

        // 验证所有请求都成功
        foreach ($responses as $response) {
            $response->assertStatus(200);
            $this->assertApiResponseFormat($response);
        }
    }
}