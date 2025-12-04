<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ApiVersionService
{
    /**
     * API 版本配置
     */
    protected $versions = [
        'v1' => [
            'name' => 'Version 1.0',
            'status' => 'stable',
            'release_date' => '2025-12-01',
            'deprecated' => false,
            'deprecation_date' => null,
            'sunset_date' => null,
            'description' => 'Initial API version with core B2B purchasing functionality',
            'features' => [
                'JWT Authentication',
                'Product Management',
                'Order Processing',
                'Inquiry System',
                'Bulk Purchase',
                'Admin Functions'
            ],
            'breaking_changes' => [],
            'migration_guide' => '/api/v1/migration-guide'
        ],
        'v2' => [
            'name' => 'Version 2.0',
            'status' => 'preview',
            'release_date' => '2025-12-04',
            'deprecated' => false,
            'deprecation_date' => null,
            'sunset_date' => null,
            'description' => 'Enhanced API with improved performance and new features',
            'features' => [
                'Enhanced Authentication',
                'Advanced Product Search',
                'Real-time Order Tracking',
                'Enhanced Inquiry Management',
                'Advanced Analytics',
                'Webhook Support',
                'Rate Limiting',
                'Response Compression'
            ],
            'breaking_changes' => [
                'Authentication header format changed',
                'Response structure enhanced',
                'Error codes standardized'
            ],
            'migration_guide' => '/api/v2/migration-guide'
        ]
    ];

    /**
     * 缓存键前缀
     */
    protected const CACHE_PREFIX = 'api_version_';

    /**
     * 缓存时间（分钟）
     */
    protected const CACHE_TTL = 60;

    /**
     * 获取所有版本信息
     *
     * @return array
     */
    public function getAllVersions(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'all', self::CACHE_TTL, function () {
            $formatted = [];
            
            foreach ($this->versions as $version => $info) {
                $formatted[$version] = $this->formatVersionInfo($version, $info);
            }
            
            return $formatted;
        });
    }

    /**
     * 获取特定版本信息
     *
     * @param string $version
     * @return array|null
     */
    public function getVersionInfo(string $version): ?array
    {
        if (!isset($this->versions[$version])) {
            return null;
        }

        return Cache::remember(self::CACHE_PREFIX . $version, self::CACHE_TTL, function () use ($version) {
            return $this->formatVersionInfo($version, $this->versions[$version]);
        });
    }

    /**
     * 获取支持的版本列表
     *
     * @return array
     */
    public function getSupportedVersions(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'supported', self::CACHE_TTL, function () {
            return array_keys(array_filter($this->versions, function ($version) {
                return !$version['deprecated'];
            }));
        });
    }

    /**
     * 获取最新版本
     *
     * @return string
     */
    public function getLatestVersion(): string
    {
        return Cache::remember(self::CACHE_PREFIX . 'latest', self::CACHE_TTL, function () {
            $versions = array_keys($this->versions);
            usort($versions, function ($a, $b) {
                return version_compare($b, $a);
            });
            return $versions[0] ?? 'v1';
        });
    }

    /**
     * 获取默认版本
     *
     * @return string
     */
    public function getDefaultVersion(): string
    {
        return 'v1';
    }

    /**
     * 检查版本是否支持
     *
     * @param string $version
     * @return bool
     */
    public function isVersionSupported(string $version): bool
    {
        return isset($this->versions[$version]) && !$this->versions[$version]['deprecated'];
    }

    /**
     * 检查版本是否已弃用
     *
     * @param string $version
     * @return bool
     */
    public function isVersionDeprecated(string $version): bool
    {
        return isset($this->versions[$version]) && $this->versions[$version]['deprecated'];
    }

    /**
     * 获取版本比较信息
     *
     * @param string $fromVersion
     * @param string $toVersion
     * @return array
     */
    public function getVersionComparison(string $fromVersion, string $toVersion): array
    {
        $from = $this->getVersionInfo($fromVersion);
        $to = $this->getVersionInfo($toVersion);

        if (!$from || !$to) {
            return [
                'status' => 'error',
                'message' => 'One or both versions not found'
            ];
        }

        $comparison = [
            'from_version' => $fromVersion,
            'to_version' => $toVersion,
            'upgrade_recommended' => version_compare($toVersion, $fromVersion, '>'),
            'breaking_changes' => $to['breaking_changes'] ?? [],
            'new_features' => array_diff($to['features'] ?? [], $from['features'] ?? []),
            'removed_features' => array_diff($from['features'] ?? [], $to['features'] ?? []),
            'migration_complexity' => $this->calculateMigrationComplexity($fromVersion, $toVersion)
        ];

        return [
            'status' => 'success',
            'data' => $comparison
        ];
    }

    /**
     * 获取版本使用统计
     *
     * @return array
     */
    public function getVersionStatistics(): array
    {
        // 这里可以集成实际的统计数据
        return Cache::remember(self::CACHE_PREFIX . 'statistics', self::CACHE_TTL, function () {
            return [
                'total_requests' => 15420,
                'version_distribution' => [
                    'v1' => [
                        'requests' => 12350,
                        'percentage' => 80.1
                    ],
                    'v2' => [
                        'requests' => 3070,
                        'percentage' => 19.9
                    ]
                ],
                'popular_endpoints' => [
                    '/api/v1/products' => 4520,
                    '/api/v1/auth/login' => 3890,
                    '/api/v1/orders' => 3210,
                    '/api/v2/products' => 1890,
                    '/api/v2/auth/login' => 1180
                ],
                'error_rates' => [
                    'v1' => 2.3,
                    'v2' => 1.8
                ],
                'average_response_times' => [
                    'v1' => 145, // ms
                    'v2' => 118  // ms
                ]
            ];
        });
    }

    /**
     * 清除版本缓存
     *
     * @return void
     */
    public function clearVersionCache(): void
    {
        $keys = [
            self::CACHE_PREFIX . 'all',
            self::CACHE_PREFIX . 'supported',
            self::CACHE_PREFIX . 'latest',
            self::CACHE_PREFIX . 'statistics'
        ];

        foreach ($this->versions as $version => $info) {
            $keys[] = self::CACHE_PREFIX . $version;
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * 格式化版本信息
     *
     * @param string $version
     * @param array $info
     * @return array
     */
    protected function formatVersionInfo(string $version, array $info): array
    {
        return [
            'version' => $version,
            'name' => $info['name'],
            'status' => $info['status'],
            'release_date' => $info['release_date'],
            'deprecated' => $info['deprecated'],
            'deprecation_date' => $info['deprecation_date'],
            'sunset_date' => $info['sunset_date'],
            'description' => $info['description'],
            'features' => $info['features'],
            'breaking_changes' => $info['breaking_changes'],
            'migration_guide' => $info['migration_guide'],
            'endpoints_count' => $this->getEndpointCount($version),
            'documentation_url' => "/api/{$version}/docs"
        ];
    }

    /**
     * 获取端点数量（示例数据）
     *
     * @param string $version
     * @return int
     */
    protected function getEndpointCount(string $version): int
    {
        $counts = [
            'v1' => 15,
            'v2' => 23
        ];

        return $counts[$version] ?? 0;
    }

    /**
     * 计算迁移复杂度
     *
     * @param string $fromVersion
     * @param string $toVersion
     * @return string
     */
    protected function calculateMigrationComplexity(string $fromVersion, string $toVersion): string
    {
        $to = $this->versions[$toVersion] ?? [];
        $breakingChanges = count($to['breaking_changes'] ?? []);

        if ($breakingChanges === 0) {
            return 'low';
        } elseif ($breakingChanges <= 3) {
            return 'medium';
        } else {
            return 'high';
        }
    }
}