<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheServiceTest extends TestCase
{
    protected CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new CacheService();
    }

    /**
     * 测试缓存设置和获取
     */
    public function test_cache_set_and_get(): void
    {
        $key = 'test_key';
        $value = ['data' => 'test_value'];
        $ttl = 3600;

        // 设置缓存
        $result = $this->cacheService->set($key, $value, $ttl);
        $this->assertTrue($result);

        // 获取缓存
        $cached = $this->cacheService->get($key);
        $this->assertEquals($value, $cached);
    }

    /**
     * 测试缓存不存在时返回默认值
     */
    public function test_cache_get_with_default(): void
    {
        $key = 'non_existent_key';
        $default = 'default_value';

        $result = $this->cacheService->get($key, $default);
        $this->assertEquals($default, $result);
    }

    /**
     * 测试缓存检查是否存在
     */
    public function test_cache_has(): void
    {
        $key = 'test_has_key';
        $value = 'test_value';

        // 不存在的缓存
        $this->assertFalse($this->cacheService->has($key));

        // 设置缓存
        $this->cacheService->set($key, $value);
        $this->assertTrue($this->cacheService->has($key));
    }

    /**
     * 测试缓存删除
     */
    public function test_cache_forget(): void
    {
        $key = 'test_forget_key';
        $value = 'test_value';

        // 设置缓存
        $this->cacheService->set($key, $value);
        $this->assertTrue($this->cacheService->has($key));

        // 删除缓存
        $result = $this->cacheService->forget($key);
        $this->assertTrue($result);
        $this->assertFalse($this->cacheService->has($key));
    }

    /**
     * 测试缓存清空
     */
    public function test_cache_clear(): void
    {
        // 设置多个缓存
        $this->cacheService->set('key1', 'value1');
        $this->cacheService->set('key2', 'value2');
        $this->cacheService->set('key3', 'value3');

        // 验证缓存存在
        $this->assertTrue($this->cacheService->has('key1'));
        $this->assertTrue($this->cacheService->has('key2'));
        $this->assertTrue($this->cacheService->has('key3'));

        // 清空缓存
        $result = $this->cacheService->clear();
        $this->assertTrue($result);

        // 验证缓存已清空
        $this->assertFalse($this->cacheService->has('key1'));
        $this->assertFalse($this->cacheService->has('key2'));
        $this->assertFalse($this->cacheService->has('key3'));
    }

    /**
     * 测试记住回调模式
     */
    public function test_cache_remember(): void
    {
        $key = 'test_remember_key';
        $callbackValue = 'callback_result';

        // 第一次调用应该执行回调
        $result = $this->cacheService->remember($key, 3600, function () use ($callbackValue) {
            return $callbackValue;
        });

        $this->assertEquals($callbackValue, $result);
        $this->assertTrue($this->cacheService->has($key));

        // 第二次调用应该从缓存获取，不执行回调
        $result = $this->cacheService->remember($key, 3600, function () {
            return 'should_not_execute';
        });

        $this->assertEquals($callbackValue, $result);
    }

    /**
     * 测试产品缓存方法
     */
    public function test_product_caching(): void
    {
        $productId = 1;
        $productData = [
            'id' => $productId,
            'name' => 'Test Product',
            'price' => 100.00
        ];

        // 缓存产品
        $result = $this->cacheService->cacheProduct($productId, $productData);
        $this->assertTrue($result);

        // 获取产品缓存
        $cached = $this->cacheService->getProduct($productId);
        $this->assertEquals($productData, $cached);

        // 删除产品缓存
        $result = $this->cacheService->forgetProduct($productId);
        $this->assertTrue($result);

        // 验证缓存已删除
        $cached = $this->cacheService->getProduct($productId);
        $this->assertNull($cached);
    }

    /**
     * 测试用户缓存方法
     */
    public function test_user_caching(): void
    {
        $userId = 1;
        $userData = [
            'id' => $userId,
            'username' => 'testuser',
            'email' => 'test@example.com'
        ];

        // 缓存用户数据
        $result = $this->cacheService->cacheUser($userId, $userData);
        $this->assertTrue($result);

        // 获取用户缓存
        $cached = $this->cacheService->getUser($userId);
        $this->assertEquals($userData, $cached);

        // 删除用户缓存
        $result = $this->cacheService->forgetUser($userId);
        $this->assertTrue($result);
    }

    /**
     * 测试搜索结果缓存
     */
    public function test_search_caching(): void
    {
        $query = 'test_query';
        $searchResults = [
            'products' => [
                ['id' => 1, 'name' => 'Product 1'],
                ['id' => 2, 'name' => 'Product 2']
            ],
            'total' => 2
        ];

        // 缓存搜索结果
        $result = $this->cacheService->cacheSearchResults($query, $searchResults);
        $this->assertTrue($result);

        // 获取搜索结果
        $cached = $this->cacheService->getSearchResults($query);
        $this->assertEquals($searchResults, $cached);
    }

    /**
     * 测试统计数据缓存
     */
    public function test_statistics_caching(): void
    {
        $statsKey = 'daily_sales';
        $statsData = [
            'total_orders' => 100,
            'total_revenue' => 10000.00,
            'date' => '2025-12-04'
        ];

        // 缓存统计数据
        $result = $this->cacheService->cacheStatistics($statsKey, $statsData);
        $this->assertTrue($result);

        // 获取统计数据
        $cached = $this->cacheService->getStatistics($statsKey);
        $this->assertEquals($statsData, $cached);
    }

    /**
     * 测试缓存标签功能
     */
    public function test_cache_tags(): void
    {
        $tags = ['products', 'category_electronics'];
        $key = 'tagged_product_1';
        $value = ['id' => 1, 'name' => 'Tagged Product'];

        // 设置带标签的缓存
        $result = $this->cacheService->setWithTags($key, $value, 3600, $tags);
        $this->assertTrue($result);

        // 获取带标签的缓存
        $cached = $this->cacheService->getWithTags($key);
        $this->assertEquals($value, $cached);

        // 清除特定标签的缓存
        $result = $this->cacheService->clearTaggedCache('products');
        $this->assertTrue($result);

        // 验证标签缓存已清除
        $cached = $this->cacheService->getWithTags($key);
        $this->assertNull($cached);
    }

    /**
     * 测试缓存统计信息
     */
    public function test_cache_statistics(): void
    {
        // 设置一些缓存数据
        $this->cacheService->set('stat_test_1', 'value1');
        $this->cacheService->set('stat_test_2', 'value2');

        // 获取缓存统计
        $stats = $this->cacheService->getStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('memory_usage', $stats);
        $this->assertArrayHasKey('hit_rate', $stats);
        $this->assertArrayHasKey('total_keys', $stats);
    }

    /**
     * 测试缓存预热
     */
    public function test_cache_warmup(): void
    {
        // 模拟预热数据
        $warmupData = [
            'products' => [
                1 => ['id' => 1, 'name' => 'Product 1'],
                2 => ['id' => 2, 'name' => 'Product 2']
            ],
            'categories' => [
                'electronics' => ['id' => 1, 'name' => 'Electronics']
            ]
        ];

        // 执行缓存预热
        $result = $this->cacheService->warmup($warmupData);
        $this->assertTrue($result);

        // 验证预热数据已缓存
        $this->assertTrue($this->cacheService->has('product_1'));
        $this->assertTrue($this->cacheService->has('product_2'));
        $this->assertTrue($this->cacheService->has('category_electronics'));
    }

    /**
     * 测试缓存失效策略
     */
    public function test_cache_invalidation(): void
    {
        $productId = 1;
        $userId = 1;

        // 设置相关缓存
        $this->cacheService->cacheProduct($productId, ['id' => $productId]);
        $this->cacheService->cacheUser($userId, ['id' => $userId]);
        $this->cacheService->cacheSearchResults('product_search', ['products' => [$productId]]);

        // 产品更新时失效相关缓存
        $result = $this->cacheService->invalidateProductCache($productId);
        $this->assertTrue($result);

        // 验证产品缓存已清除
        $this->assertFalse($this->cacheService->has('product_' . $productId));
    }
}