<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected $adminUser;
    protected $regularUser;
    protected $jwtToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 运行数据库迁移
        $this->runDatabaseMigrations();
        
        // 设置测试用户
        $this->createTestUsers();
        
        // 清除缓存
        $this->artisan('cache:clear');
    }

    protected function tearDown(): void
    {
        // 清理测试数据
        $this->artisan('cache:clear');
        
        parent::tearDown();
    }

    /**
     * 创建测试用户
     */
    protected function createTestUsers(): void
    {
        $this->adminUser = User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@banho.com',
            'role' => 'admin'
        ]);

        $this->regularUser = User::factory()->create([
            'username' => 'testuser',
            'email' => 'testuser@banho.com',
            'role' => 'user'
        ]);
    }

    /**
     * 获取管理员JWT令牌
     */
    protected function getAdminJwtToken(): string
    {
        if (!$this->jwtToken) {
            $response = $this->postJson('/api/auth/login', [
                'username' => 'admin',
                'password' => 'admin123'
            ]);
            
            $this->jwtToken = $response->json('data.access_token');
        }
        
        return $this->jwtToken;
    }

    /**
     * 获取普通用户JWT令牌
     */
    protected function getUserJwtToken(): string
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);
        
        return $response->json('data.access_token');
    }

    /**
     * 带认证的API请求
     */
    protected function withAuth($token = null): self
    {
        $token = $token ?: $this->getUserJwtToken();
        
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ]);
    }

    /**
     * 带管理员认证的API请求
     */
    protected function withAdminAuth(): self
    {
        return $this->withAuth($this->getAdminJwtToken());
    }

    /**
     * 断言API响应格式
     */
    protected function assertApiResponseFormat($response): void
    {
        $response->assertJsonStructure([
            'status',
            'message',
            'data',
            'timestamp'
        ]);
    }

    /**
     * 断言API错误响应格式
     */
    protected function assertApiErrorResponse($response, $status = 400): void
    {
        $response->assertStatus($status);
        $response->assertJsonStructure([
            'status',
            'message',
            'errors',
            'timestamp'
        ]);
    }

    /**
     * 创建测试产品数据
     */
    protected function createTestProduct($overrides = [])
    {
        return array_merge([
            'sku' => 'TEST_SKU_' . uniqid(),
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 100.00,
            'currency' => 'CNY',
            'stock_quantity' => 100,
            'category' => 'electronics',
            'brand' => 'Test Brand',
            'weight' => 1.0,
            'dimensions' => json_encode(['length' => 10, 'width' => 10, 'height' => 10]),
            'is_active' => true
        ], $overrides);
    }

    /**
     * 创建测试订单数据
     */
    protected function createTestOrderData($overrides = [])
    {
        return array_merge([
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'shipping_address' => 'Test Address',
            'items' => [
                [
                    'sku' => 'ALIBABA_SKU_A123',
                    'quantity' => 2,
                    'price' => 1250.50
                ]
            ],
            'total_amount' => 2501.00,
            'currency' => 'CNY'
        ], $overrides);
    }
}
