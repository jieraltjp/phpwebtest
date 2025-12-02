<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试用户登录成功
     */
    public function test_user_can_login_successfully()
    {
        // 创建测试用户
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
        ]);

        // 发送登录请求
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'expires_in'
                ]);
    }

    /**
     * 测试用户登录失败
     */
    public function test_user_login_fails_with_invalid_credentials()
    {
        // 创建测试用户
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
        ]);

        // 发送错误的登录请求
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'error' => 'Unauthorized',
                    'message' => '用户名或密码错误'
                ]);
    }

    /**
     * 测试登录验证
     */
    public function test_login_validation()
    {
        // 发送不完整的登录请求
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'testuser',
            // 缺少密码
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /**
     * 测试获取当前用户信息
     */
    public function test_get_current_user_info()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ]);
    }

    /**
     * 测试退出登录
     */
    public function test_logout()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Successfully logged out'
                ]);
    }

    /**
     * 测试令牌刷新
     */
    public function test_refresh_token()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'expires_in'
                ]);
    }
}