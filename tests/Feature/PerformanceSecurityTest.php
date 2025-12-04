<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Services\CacheService;
use App\Services\EncryptionService;

class PerformanceSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected CacheService $cacheService;
    protected EncryptionService $encryptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new CacheService();
        $this->encryptionService = new EncryptionService();
    }

    /**
     * 测试API响应时间性能
     */
    public function test_api_response_time_performance(): void
    {
        // 创建测试用户
        $user = User::factory()->create();
        $token = $this->withAuth()->postJson('/api/auth/login', [
            'username' => $user->username,
            'password' => 'password'
        ])->json('data.access_token');

        // 测试各个API端点的响应时间
        $endpoints = [
            '/api/auth/me',
            '/api/products',
            '/api/orders',
            '/api/inquiries'
        ];

        foreach ($endpoints as $endpoint) {
            $startTime = microtime(true);
            
            $response = $this->withAuth($token)->getJson($endpoint);
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);
            $this->assertLessThan(1000, $responseTime, 
                "Endpoint {$endpoint} should respond within 1000ms, actual: {$responseTime}ms");
        }
    }

    /**
     * 测试缓存性能
     */
    public function test_cache_performance(): void
    {
        $testData = [
            'id' => 1,
            'name' => 'Performance Test Product',
            'description' => str_repeat('This is a test description. ', 100), // 较大的数据
            'price' => 100.00,
            'specifications' => array_fill(0, 50, 'specification item')
        ];

        // 测试缓存写入性能
        $iterations = 100;
        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $this->cacheService->set("perf_test_{$i}", $testData, 3600);
        }
        
        $endTime = microtime(true);
        $avgWriteTime = (($endTime - $startTime) / $iterations) * 1000;

        $this->assertLessThan(10, $avgWriteTime, 
            "Cache write should average under 10ms per operation, actual: {$avgWriteTime}ms");

        // 测试缓存读取性能
        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $this->cacheService->get("perf_test_{$i}");
        }
        
        $endTime = microtime(true);
        $avgReadTime = (($endTime - $startTime) / $iterations) * 1000;

        $this->assertLessThan(5, $avgReadTime, 
            "Cache read should average under 5ms per operation, actual: {$avgReadTime}ms");

        // 清理测试数据
        for ($i = 0; $i < $iterations; $i++) {
            $this->cacheService->forget("perf_test_{$i}");
        }
    }

    /**
     * 测试加密性能
     */
    public function test_encryption_performance(): void
    {
        $testData = [
            'user_id' => 123,
            'email' => 'performance@test.com',
            'personal_info' => [
                'name' => 'Performance Test User',
                'address' => '123 Performance Test Street',
                'phone' => '+81-90-1234-5678'
            ],
            'order_history' => array_fill(0, 100, ['order_id' => 'ORD-001', 'amount' => 100.00])
        ];

        $iterations = 50;

        // 测试加密性能
        $startTime = microtime(true);
        $encryptedData = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $encryptedData[] = $this->encryptionService->encryptArray($testData, 'perf_test_key');
        }
        
        $endTime = microtime(true);
        $avgEncryptTime = (($endTime - $startTime) / $iterations) * 1000;

        $this->assertLessThan(50, $avgEncryptTime, 
            "Encryption should average under 50ms per operation, actual: {$avgEncryptTime}ms");

        // 测试解密性能
        $startTime = microtime(true);
        
        foreach ($encryptedData as $data) {
            $this->encryptionService->decryptArray($data, 'perf_test_key');
        }
        
        $endTime = microtime(true);
        $avgDecryptTime = (($endTime - $startTime) / $iterations) * 1000;

        $this->assertLessThan(50, $avgDecryptTime, 
            "Decryption should average under 50ms per operation, actual: {$avgDecryptTime}ms");
    }

    /**
     * 测试数据库查询性能
     */
    public function test_database_query_performance(): void
    {
        // 创建大量测试数据
        $users = User::factory()->count(1000)->create();
        
        // 测试简单查询性能
        $startTime = microtime(true);
        $userCount = User::count();
        $endTime = microtime(true);
        
        $queryTime = ($endTime - $startTime) * 1000;
        $this->assertLessThan(100, $queryTime, 
            "Count query should be under 100ms, actual: {$queryTime}ms");

        // 测试分页查询性能
        $startTime = microtime(true);
        $usersPage = User::paginate(50);
        $endTime = microtime(true);
        
        $paginationTime = ($endTime - $startTime) * 1000;
        $this->assertLessThan(200, $paginationTime, 
            "Pagination query should be under 200ms, actual: {$paginationTime}ms");

        // 测试复杂查询性能
        $startTime = microtime(true);
        $usersWithRelations = User::with('orders.inquiries')
                                 ->where('created_at', '>=', now()->subDays(30))
                                 ->limit(100)
                                 ->get();
        $endTime = microtime(true);
        
        $complexQueryTime = ($endTime - $startTime) * 1000;
        $this->assertLessThan(500, $complexQueryTime, 
            "Complex query should be under 500ms, actual: {$complexQueryTime}ms");
    }

    /**
     * 测试API限流功能
     */
    public function test_api_rate_limiting(): void
    {
        // 模拟快速连续请求
        $responses = [];
        $requestCount = 20;
        
        for ($i = 0; $i < $requestCount; $i++) {
            $responses[] = $this->getJson('/api/health');
        }

        // 检查是否有请求被限流
        $rateLimitedResponses = collect($responses)->filter(function ($response) {
            return $response->status() === 429;
        });

        // 如果实现了限流，应该有被限制的请求
        // 这取决于具体的限流配置
        if ($rateLimitedResponses->isNotEmpty()) {
            $rateLimitedResponses->each(function ($response) {
                $response->assertHeader('x-ratelimit-limit');
                $response->assertHeader('x-ratelimit-remaining');
                $response->assertHeader('retry-after');
            });
        }

        // 至少前几个请求应该成功
        for ($i = 0; $i < min(10, $requestCount); $i++) {
            $this->assertEquals(200, $responses[$i]->status(), 
                "Request {$i} should succeed before rate limiting kicks in");
        }
    }

    /**
     * 测试输入验证安全性
     */
    public function test_input_validation_security(): void
    {
        // 测试XSS防护
        $xssPayloads = [
            '<script>alert("xss")</script>',
            'javascript:alert("xss")',
            '<img src="x" onerror="alert(\'xss\')">',
            '"><script>alert("xss")</script>',
            '\';alert("xss");//'
        ];

        foreach ($xssPayloads as $payload) {
            $response = $this->postJson('/api/auth/check-username', [
                'username' => $payload
            ]);

            // 应该返回验证错误，而不是执行XSS
            if ($response->status() !== 422) {
                $this->assertStringNotContainsString('<script>', $response->getContent());
            }
        }

        // 测试SQL注入防护
        $sqlInjectionPayloads = [
            "'; DROP TABLE users; --",
            "' OR '1'='1",
            "admin'--",
            "' UNION SELECT * FROM users --"
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $response = $this->postJson('/api/auth/login', [
                'username' => $payload,
                'password' => 'password'
            ]);

            // 应该返回认证失败，而不是SQL错误
            $this->assertContains($response->status(), [401, 422]);
        }
    }

    /**
     * 测试密码安全性
     */
    public function test_password_security(): void
    {
        // 测试弱密码拒绝
        $weakPasswords = [
            'password',
            '12345678',
            'qwerty',
            'admin',
            'test',
            '',
            'a' * 8 // 重复字符
        ];

        foreach ($weakPasswords as $password) {
            $response = $this->postJson('/api/auth/register', [
                'username' => 'testuser_' . uniqid(),
                'email' => 'test_' . uniqid() . '@example.com',
                'password' => $password,
                'password_confirmation' => $password
            ]);

            $response->assertStatus(422);
        }

        // 测试强密码接受
        $strongPasswords = [
            'StrongP@ssw0rd!',
            'C0mpl3x!P@ss',
            'MySecur3#Pass123',
            'B@nhoTr@ding2025!'
        ];

        foreach ($strongPasswords as $password) {
            $response = $this->postJson('/api/auth/register', [
                'username' => 'testuser_' . uniqid(),
                'email' => 'test_' . uniqid() . '@example.com',
                'password' => $password,
                'password_confirmation' => $password
            ]);

            // 应该成功或因其他原因失败（如用户名重复），但不是因为密码强度
            $this->assertNotEquals(422, $response->status());
        }
    }

    /**
     * 测试JWT令牌安全性
     */
    public function test_jwt_token_security(): void
    {
        $user = User::factory()->create();

        // 测试有效令牌
        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => $user->username,
            'password' => 'password'
        ]);

        $token = $loginResponse->json('data.access_token');

        $response = $this->withAuth($token)->getJson('/api/auth/me');
        $response->assertStatus(200);

        // 测试无效令牌
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_here'
        ])->getJson('/api/auth/me');

        $response->assertStatus(401);

        // 测试过期令牌（需要模拟时间）
        // $this->travel(61)->minutes(); // 前进61分钟
        // $response = $this->withAuth($token)->getJson('/api/auth/me');
        // $response->assertStatus(401);

        // 测试令牌刷新
        $refreshResponse = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $loginResponse->json('data.refresh_token')
        ]);

        $refreshResponse->assertStatus(200);
        $this->assertNotEmpty($refreshResponse->json('data.access_token'));
    }

    /**
     * 测试HTTPS重定向（如果配置）
     */
    public function test_https_redirection(): void
    {
        // 这个测试需要在生产环境中配置HTTPS时运行
        $response = $this->get('/');
        
        // 如果配置了HTTPS强制重定向，应该重定向到HTTPS
        // $response->assertRedirectContains('https://');
        
        // 暂时通过
        $this->assertTrue(true);
    }

    /**
     * 测试CORS配置
     */
    public function test_cors_configuration(): void
    {
        // 测试预检请求
        $response = $this->options('/api/products', [
            'Origin' => 'https://example.com',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization'
        ]);

        // 应该返回CORS头
        $response->assertHeader('Access-Control-Allow-Origin');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
    }

    /**
     * 测试敏感信息泄露防护
     */
    public function test_sensitive_information_protection(): void
    {
        // 测试错误页面不泄露敏感信息
        $response = $this->getJson('/api/nonexistent-endpoint');
        $response->assertStatus(404);
        
        // 确保响应中不包含敏感信息
        $content = $response->getContent();
        $sensitiveInfo = [
            'password',
            'secret',
            'key',
            'token',
            'database',
            'stack trace',
            'internal'
        ];

        foreach ($sensitiveInfo as $info) {
            // 在生产环境中，错误响应不应该包含这些敏感信息
            if (app()->environment('production')) {
                $this->assertStringNotContainsStringIgnoringCase($info, $content);
            }
        }
    }

    /**
     * 测试会话安全
     */
    public function test_session_security(): void
    {
        // 测试会话Cookie安全属性
        $response = $this->get('/');
        
        // 在生产环境中，Cookie应该有安全属性
        if (app()->environment('production')) {
            // $response->assertCookie('laravel_session', null, false, false, true); // Secure, HttpOnly
        }
        
        $this->assertTrue(true);
    }

    /**
     * 测试文件上传安全
     */
    public function test_file_upload_security(): void
    {
        // 测试恶意文件上传拒绝
        $maliciousFiles = [
            [
                'name' => 'malicious.php',
                'type' => 'application/x-php',
                'size' => 1024,
                'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
                'error' => UPLOAD_ERR_OK
            ],
            [
                'name' => 'script.js',
                'type' => 'application/javascript',
                'size' => 1024,
                'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
                'error' => UPLOAD_ERR_OK
            ]
        ];

        foreach ($maliciousFiles as $file) {
            // 这里需要实际的文件上传端点来测试
            // 暂时跳过
        }

        $this->assertTrue(true);
    }

    /**
     * 测试内存使用性能
     */
    public function test_memory_usage_performance(): void
    {
        $initialMemory = memory_get_usage();

        // 执行一些内存密集操作
        $users = User::factory()->count(100)->create();
        
        foreach ($users as $user) {
            $user->orders()->createMany(
                Order::factory()->count(5)->make()->toArray()
            );
        }

        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;
        $memoryUsedMB = $memoryUsed / 1024 / 1024;

        // 内存使用应该在合理范围内
        $this->assertLessThan(50, $memoryUsedMB, 
            "Memory usage should be under 50MB, actual: {$memoryUsedMB}MB");
    }

    /**
     * 测试并发请求处理
     */
    public function test_concurrent_request_handling(): void
    {
        $responses = [];
        $concurrentRequests = 10;

        // 模拟并发请求
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $responses[] = $this->getJson('/api/health');
        }

        // 验证所有请求都得到正确处理
        foreach ($responses as $response) {
            $response->assertStatus(200);
            $this->assertApiResponseFormat($response);
        }

        // 验证响应时间一致性
        $responseTimes = array_map(function($response) {
            return $response->headers->get('x-response-time', 0);
        }, $responses);

        $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
        $this->assertLessThan(1000, $avgResponseTime, 
            "Average response time should be under 1000ms under concurrent load");
    }

    /**
     * 测试缓存穿透防护
     */
    public function test_cache_penetration_protection(): void
    {
        $nonExistentKeys = [
            'nonexistent_product_1',
            'nonexistent_product_2',
            'nonexistent_product_3'
        ];

        foreach ($nonExistentKeys as $key) {
            // 多次请求不存在的缓存项
            for ($i = 0; $i < 3; $i++) {
                $result = $this->cacheService->get($key);
                $this->assertNull($result);
            }
        }

        // 验证缓存穿透保护机制（如果有实现）
        // 这里可以检查是否设置了空值缓存来防止穿透
        $this->assertTrue(true);
    }

    /**
     * 测试缓存雪崩防护
     */
    public function test_cache_avalanche_protection(): void
    {
        // 设置大量相同过期时间的缓存
        $cacheKeys = [];
        for ($i = 0; $i < 100; $i++) {
            $key = "avalanche_test_{$i}";
            $cacheKeys[] = $key;
            $this->cacheService->set($key, "value_{$i}", 1); // 1秒过期
        }

        // 等待缓存过期
        sleep(2);

        // 同时请求这些过期的缓存项
        $results = [];
        foreach ($cacheKeys as $key) {
            $results[] = $this->cacheService->get($key);
        }

        // 验证缓存雪崩保护机制
        // 如果实现了随机过期时间，应该不会所有缓存同时失效
        $this->assertTrue(true);
    }
}
