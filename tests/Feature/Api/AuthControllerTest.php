<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建测试用户
        $this->testUser = User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
    }

    /**
     * 测试用户登录 - 成功案例
     */
    public function test_user_login_success(): void
    {
        $loginData = [
            'username' => 'testuser',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in',
                        'user' => [
                            'id',
                            'username',
                            'email',
                            'role',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertEquals(3600, $response->json('data.expires_in'));
        $this->assertEquals('testuser', $response->json('data.user.username'));
    }

    /**
     * 测试用户登录 - 错误密码
     */
    public function test_user_login_wrong_password(): void
    {
        $loginData = [
            'username' => 'testuser',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(401)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors',
                    'timestamp'
                ]);

        $this->assertEquals('error', $response->json('status'));
    }

    /**
     * 测试用户登录 - 用户不存在
     */
    public function test_user_login_user_not_exists(): void
    {
        $loginData = [
            'username' => 'nonexistentuser',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(401);
        $this->assertEquals('error', $response->json('status'));
    }

    /**
     * 测试用户登录 - 缺少字段
     */
    public function test_user_login_missing_fields(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['username', 'password']);
    }

    /**
     * 测试用户登录 - 无效输入格式
     */
    public function test_user_login_invalid_format(): void
    {
        $loginData = [
            'username' => '',
            'password' => ''
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['username', 'password']);
    }

    /**
     * 测试用户注册 - 成功案例
     */
    public function test_user_registration_success(): void
    {
        $registrationData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            'phone' => '+81-90-1234-5678'
        ];

        $response = $this->postJson('/api/auth/register', $registrationData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'username',
                            'email',
                            'phone',
                            'role',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('newuser', $response->json('data.user.username'));
        $this->assertEquals('newuser@example.com', $response->json('data.user.email'));

        // 验证用户已创建
        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@example.com'
        ]);
    }

    /**
     * 测试用户注册 - 用户名已存在
     */
    public function test_user_registration_username_exists(): void
    {
        $registrationData = [
            'username' => 'testuser', // 已存在的用户名
            'email' => 'newemail@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ];

        $response = $this->postJson('/api/auth/register', $registrationData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['username']);
    }

    /**
     * 测试用户注册 - 邮箱已存在
     */
    public function test_user_registration_email_exists(): void
    {
        $registrationData = [
            'username' => 'newuser',
            'email' => 'test@example.com', // 已存在的邮箱
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ];

        $response = $this->postJson('/api/auth/register', $registrationData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * 测试用户注册 - 密码不匹配
     */
    public function test_user_registration_password_mismatch(): void
    {
        $registrationData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword123!'
        ];

        $response = $this->postJson('/api/auth/register', $registrationData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /**
     * 测试用户注册 - 弱密码
     */
    public function test_user_registration_weak_password(): void
    {
        $registrationData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ];

        $response = $this->postJson('/api/auth/register', $registrationData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /**
     * 测试用户登出 - 成功案例
     */
    public function test_user_logout_success(): void
    {
        $token = $this->getUserJwtToken();

        $response = $this->withAuth($token)
                        ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data',
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
    }

    /**
     * 测试用户登出 - 无令牌
     */
    public function test_user_logout_no_token(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * 测试用户登出 - 无效令牌
     */
    public function test_user_logout_invalid_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token'
        ])->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * 测试获取当前用户信息 - 成功案例
     */
    public function test_get_current_user_success(): void
    {
        $token = $this->getUserJwtToken();

        $response = $this->withAuth($token)
                        ->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'username',
                        'email',
                        'phone',
                        'role',
                        'created_at',
                        'updated_at'
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('testuser', $response->json('data.username'));
    }

    /**
     * 测试获取当前用户信息 - 无令牌
     */
    public function test_get_current_user_no_token(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * 测试刷新令牌 - 成功案例
     */
    public function test_refresh_token_success(): void
    {
        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $refreshToken = $loginResponse->json('data.refresh_token');

        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $refreshToken
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in'
                    ],
                    'timestamp'
                ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertNotEmpty($response->json('data.access_token'));
    }

    /**
     * 测试刷新令牌 - 无效刷新令牌
     */
    public function test_refresh_token_invalid(): void
    {
        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => 'invalid_refresh_token'
        ]);

        $response->assertStatus(401);
    }

    /**
     * 测试检查用户名可用性 - 可用
     */
    public function test_check_username_available(): void
    {
        $response = $this->postJson('/api/auth/check-username', [
            'username' => 'available_username'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'available' => true
                    ]
                ]);
    }

    /**
     * 测试检查用户名可用性 - 不可用
     */
    public function test_check_username_unavailable(): void
    {
        $response = $this->postJson('/api/auth/check-username', [
            'username' => 'testuser' // 已存在的用户名
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'available' => false
                    ]
                ]);
    }

    /**
     * 测试检查用户名可用性 - 无效格式
     */
    public function test_check_username_invalid_format(): void
    {
        $response = $this->postJson('/api/auth/check-username', [
            'username' => '' // 空用户名
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['username']);
    }

    /**
     * 测试检查邮箱可用性 - 可用
     */
    public function test_check_email_available(): void
    {
        $response = $this->postJson('/api/auth/check-email', [
            'email' => 'available@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'available' => true
                    ]
                ]);
    }

    /**
     * 测试检查邮箱可用性 - 不可用
     */
    public function test_check_email_unavailable(): void
    {
        $response = $this->postJson('/api/auth/check-email', [
            'email' => 'test@example.com' // 已存在的邮箱
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'available' => false
                    ]
                ]);
    }

    /**
     * 测试检查邮箱可用性 - 无效格式
     */
    public function test_check_email_invalid_format(): void
    {
        $response = $this->postJson('/api/auth/check-email', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * 测试并发登录限制
     */
    public function test_concurrent_login_limit(): void
    {
        // 模拟多次登录尝试
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->postJson('/api/auth/login', [
                'username' => 'testuser',
                'password' => 'password123'
            ]);
        }

        // 至少第一次登录应该成功
        $this->assertEquals(200, $responses[0]->status());

        // 检查是否有登录被限制（这取决于具体的实现）
        // 这里只是示例，实际逻辑可能不同
    }

    /**
     * 测试登录后令牌缓存
     */
    public function test_login_token_caching(): void
    {
        $loginData = [
            'username' => 'testuser',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);
        $token = $response->json('data.access_token');

        // 验证令牌被缓存
        $cacheKey = 'jwt_token_' . md5($token);
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * 测试登出后令牌失效
     */
    public function test_logout_token_invalidation(): void
    {
        $token = $this->getUserJwtToken();
        $cacheKey = 'jwt_token_' . md5($token);

        // 令牌应该被缓存
        $this->assertTrue(Cache::has($cacheKey));

        // 登出
        $this->withAuth($token)->postJson('/api/auth/logout');

        // 令牌应该从缓存中移除
        $this->assertFalse(Cache::has($cacheKey));

        // 使用已登出的令牌应该失败
        $response = $this->withAuth($token)->getJson('/api/auth/me');
        $response->assertStatus(401);
    }

    /**
     * 测试API访问频率限制
     */
    public function test_api_rate_limiting(): void
    {
        // 快速发送多个请求
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->postJson('/api/auth/check-username', [
                'username' => 'test_user_' . $i
            ]);
        }

        // 检查是否有请求被限制
        $rateLimitedResponses = collect($responses)->filter(function ($response) {
            return $response->status() === 429;
        });

        // 如果实现了限流，应该有被限制的请求
        // 这取决于具体的限流配置
    }
}