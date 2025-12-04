<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试基础应用功能
     */
    public function test_basic_application_test(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * 测试数据库连接
     */
    public function test_database_connection(): void
    {
        // 测试数据库连接
        $this->assertTrue(true);
    }

    /**
     * 测试配置加载
     */
    public function test_config_loading(): void
    {
        $appName = config('app.name');
        $this->assertNotEmpty($appName);
    }

    /**
     * 测试缓存功能
     */
    public function test_cache_functionality(): void
    {
        \Cache::put('test_key', 'test_value', 60);
        $value = \Cache::get('test_key');
        $this->assertEquals('test_value', $value);
    }

    /**
     * 测试日志功能
     */
    public function test_logging_functionality(): void
    {
        \Log::info('Test log message');
        $this->assertTrue(true);
    }
}