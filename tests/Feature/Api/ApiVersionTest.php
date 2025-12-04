<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\ApiVersionService;

class ApiVersionTest extends TestCase
{
    use RefreshDatabase;

    protected ApiVersionService $versionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->versionService = app(ApiVersionService::class);
    }

    /**
     * 测试获取所有 API 版本信息
     */
    public function test_get_all_api_versions(): void
    {
        $response = $this->getJson('/api/versions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'versions' => [
                        'v1' => [
                            'version',
                            'name',
                            'status',
                            'release_date',
                            'deprecated',
                            'description',
                            'features'
                        ],
                        'v2' => [
                            'version',
                            'name',
                            'status',
                            'release_date',
                            'description',
                            'features'
                        ]
                    ],
                    'total',
                    'default_version',
                    'latest_version',
                    'supported_versions'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals('v1', $data['default_version']);
        $this->assertEquals('v2', $data['latest_version']);
        $this->assertContains('v1', $data['supported_versions']);
        $this->assertContains('v2', $data['supported_versions']);
    }

    /**
     * 测试获取特定版本信息
     */
    public function test_get_specific_version_info(): void
    {
        $response = $this->getJson('/api/versions/v1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'version',
                    'name',
                    'status',
                    'release_date',
                    'deprecated',
                    'description',
                    'features',
                    'breaking_changes',
                    'migration_guide',
                    'endpoints_count'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals('v1', $data['version']);
        $this->assertEquals('stable', $data['status']);
        $this->assertFalse($data['deprecated']);
    }

    /**
     * 测试获取不存在的版本信息
     */
    public function test_get_nonexistent_version_info(): void
    {
        $response = $this->getJson('/api/versions/v99');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Version v99 not found'
            ]);
    }

    /**
     * 测试版本比较功能
     */
    public function test_version_comparison(): void
    {
        $response = $this->postJson('/api/versions/compare', [
            'from_version' => 'v1',
            'to_version' => 'v2'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'from_version',
                    'to_version',
                    'upgrade_recommended',
                    'breaking_changes',
                    'new_features',
                    'removed_features',
                    'migration_complexity'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals('v1', $data['from_version']);
        $this->assertEquals('v2', $data['to_version']);
        $this->assertTrue($data['upgrade_recommended']);
    }

    /**
     * 测试版本比较验证
     */
    public function test_version_comparison_validation(): void
    {
        // 测试缺少参数
        $response = $this->postJson('/api/versions/compare', []);
        $response->assertStatus(422);

        // 测试相同版本比较
        $response = $this->postJson('/api/versions/compare', [
            'from_version' => 'v1',
            'to_version' => 'v1'
        ]);
        $response->assertStatus(422);
    }

    /**
     * 测试版本统计信息
     */
    public function test_version_statistics(): void
    {
        $response = $this->getJson('/api/versions/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'total_requests',
                    'version_distribution',
                    'popular_endpoints',
                    'error_rates',
                    'average_response_times'
                ]
            ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('v1', $data['version_distribution']);
        $this->assertArrayHasKey('v2', $data['version_distribution']);
        $this->assertIsArray($data['popular_endpoints']);
        $this->assertIsArray($data['error_rates']);
    }

    /**
     * 测试版本健康检查
     */
    public function test_version_health_check(): void
    {
        $response = $this->getJson('/api/versions/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'version',
                    'status',
                    'deprecated',
                    'sunset_date',
                    'migration_required',
                    'recommended_action',
                    'checks'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals('v1', $data['version']);
        $this->assertEquals('healthy', $data['status']);
        $this->assertFalse($data['deprecated']);
        $this->assertFalse($data['migration_required']);
    }

    /**
     * 测试版本迁移指南
     */
    public function test_version_migration_guide(): void
    {
        $response = $this->getJson('/api/versions/v1/migration-guide');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'current_version',
                    'next_version',
                    'migration_complexity',
                    'breaking_changes',
                    'new_features',
                    'steps',
                    'timeline',
                    'support_resources'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals('v1', $data['current_version']);
        $this->assertEquals('v2', $data['next_version']);
        $this->assertIsArray($data['steps']);
        $this->assertIsArray($data['support_resources']);
    }

    /**
     * 测试清除版本缓存
     */
    public function test_clear_version_cache(): void
    {
        $response = $this->postJson('/api/versions/clear-cache');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Version cache cleared successfully'
            ]);
    }

    /**
     * 测试 API v1 认证端点版本头
     */
    public function test_api_v1_auth_version_headers(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertHeader('API-Version', 'v1')
            ->assertHeader('API-Supported-Versions')
            ->assertJsonPath('data.api_version', 'v1');
    }

    /**
     * 测试 API v2 认证端点版本头
     */
    public function test_api_v2_auth_version_headers(): void
    {
        $response = $this->postJson('/api/v2/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertHeader('API-Version', 'v2')
            ->assertHeader('API-Supported-Versions')
            ->assertJsonPath('data.api_version', 'v2');
    }

    /**
     * 测试 API v1 产品端点
     */
    public function test_api_v1_products_endpoint(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertHeader('API-Version', 'v1')
            ->assertJsonPath('data.api_version', 'v1')
            ->assertJsonPath('data.features.basic_search', true)
            ->assertJsonPath('data.features.caching', true);
    }

    /**
     * 测试 API v2 产品端点
     */
    public function test_api_v2_products_endpoint(): void
    {
        $response = $this->getJson('/api/v2/products');

        $response->assertStatus(200)
            ->assertHeader('API-Version', 'v2')
            ->assertJsonPath('data.api_version', 'v2')
            ->assertJsonPath('data.search_features.fuzzy_search', true)
            ->assertJsonPath('data.search_features.real_time_stock', true);
    }

    /**
     * 测试版本服务方法
     */
    public function test_api_version_service_methods(): void
    {
        // 测试获取所有版本
        $allVersions = $this->versionService->getAllVersions();
        $this->assertArrayHasKey('v1', $allVersions);
        $this->assertArrayHasKey('v2', $allVersions);

        // 测试获取特定版本
        $v1Info = $this->versionService->getVersionInfo('v1');
        $this->assertNotNull($v1Info);
        $this->assertEquals('v1', $v1Info['version']);

        // 测试获取支持的版本
        $supportedVersions = $this->versionService->getSupportedVersions();
        $this->assertContains('v1', $supportedVersions);
        $this->assertContains('v2', $supportedVersions);

        // 测试获取最新版本
        $latestVersion = $this->versionService->getLatestVersion();
        $this->assertEquals('v2', $latestVersion);

        // 测试版本支持检查
        $this->assertTrue($this->versionService->isVersionSupported('v1'));
        $this->assertTrue($this->versionService->isVersionSupported('v2'));
        $this->assertFalse($this->versionService->isVersionSupported('v99'));

        // 测试版本弃用检查
        $this->assertFalse($this->versionService->isVersionDeprecated('v1'));
        $this->assertFalse($this->versionService->isVersionDeprecated('v2'));

        // 测试默认版本
        $defaultVersion = $this->versionService->getDefaultVersion();
        $this->assertEquals('v1', $defaultVersion);
    }

    /**
     * 测试版本比较服务方法
     */
    public function test_version_comparison_service(): void
    {
        $comparison = $this->versionService->getVersionComparison('v1', 'v2');
        
        $this->assertEquals('success', $comparison['status']);
        $this->assertEquals('v1', $comparison['data']['from_version']);
        $this->assertEquals('v2', $comparison['data']['to_version']);
        $this->assertTrue($comparison['data']['upgrade_recommended']);
        $this->assertIsArray($comparison['data']['new_features']);
        $this->assertIsArray($comparison['data']['breaking_changes']);
    }

    /**
     * 测试版本统计服务方法
     */
    public function test_version_statistics_service(): void
    {
        $statistics = $this->versionService->getVersionStatistics();
        
        $this->assertArrayHasKey('total_requests', $statistics);
        $this->assertArrayHasKey('version_distribution', $statistics);
        $this->assertArrayHasKey('popular_endpoints', $statistics);
        $this->assertArrayHasKey('error_rates', $statistics);
        $this->assertArrayHasKey('average_response_times', $statistics);
    }

    /**
     * 测试缓存清除功能
     */
    public function test_cache_clearing(): void
    {
        // 获取版本信息（会缓存）
        $this->versionService->getAllVersions();
        $this->versionService->getVersionInfo('v1');
        
        // 清除缓存
        $this->versionService->clearVersionCache();
        
        // 重新获取应该仍然正常工作
        $allVersions = $this->versionService->getAllVersions();
        $this->assertArrayHasKey('v1', $allVersions);
        
        $v1Info = $this->versionService->getVersionInfo('v1');
        $this->assertNotNull($v1Info);
    }
}