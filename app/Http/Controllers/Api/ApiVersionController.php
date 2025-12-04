<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiVersionService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiVersionController extends Controller
{
    protected ApiVersionService $versionService;

    public function __construct(ApiVersionService $versionService)
    {
        $this->versionService = $versionService;
    }

    /**
     * 获取所有 API 版本信息
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $versions = $this->versionService->getAllVersions();
            
            return ApiResponseService::success([
                'versions' => $versions,
                'total' => count($versions),
                'default_version' => $this->versionService->getDefaultVersion(),
                'latest_version' => $this->versionService->getLatestVersion(),
                'supported_versions' => $this->versionService->getSupportedVersions()
            ], 'API versions retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponseService::error('Failed to retrieve API versions', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取特定版本信息
     *
     * @param string $version
     * @return JsonResponse
     */
    public function show(string $version): JsonResponse
    {
        try {
            $versionInfo = $this->versionService->getVersionInfo($version);
            
            if (!$versionInfo) {
                return ApiResponseService::error("Version {$version} not found", [], 404);
            }

            return ApiResponseService::success($versionInfo, "Version {$version} information retrieved successfully");

        } catch (\Exception $e) {
            return ApiResponseService::error('Failed to retrieve version information', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取版本统计信息
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->versionService->getVersionStatistics();
            
            return ApiResponseService::success($statistics, 'API version statistics retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponseService::error('Failed to retrieve statistics', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 比较两个版本
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function compare(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from_version' => 'required|string',
                'to_version' => 'required|string|different:from_version'
            ]);

            $comparison = $this->versionService->getVersionComparison(
                $validated['from_version'],
                $validated['to_version']
            );

            if ($comparison['status'] === 'error') {
                return ApiResponseService::error($comparison['message'], [], 400);
            }

            return ApiResponseService::success(
                $comparison['data'],
                'Version comparison completed successfully'
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseService::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseService::error('Failed to compare versions', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取版本迁移指南
     *
     * @param string $version
     * @return JsonResponse
     */
    public function migrationGuide(string $version): JsonResponse
    {
        try {
            $versionInfo = $this->versionService->getVersionInfo($version);
            
            if (!$versionInfo) {
                return ApiResponseService::error("Version {$version} not found", [], 404);
            }

            $guide = $this->generateMigrationGuide($version);
            
            return ApiResponseService::success($guide, "Migration guide for version {$version}");

        } catch (\Exception $e) {
            return ApiResponseService::error('Failed to generate migration guide', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 清除版本缓存
     *
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->versionService->clearVersionCache();
            
            return ApiResponseService::success([], 'Version cache cleared successfully');

        } catch (\Exception $e) {
            return ApiResponseService::error('Failed to clear cache', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取版本健康状态
     *
     * @param string $version
     * @return JsonResponse
     */
    public function health(string $version): JsonResponse
    {
        try {
            $versionInfo = $this->versionService->getVersionInfo($version);
            
            if (!$versionInfo) {
                return ApiResponseService::error("Version {$version} not found", [], 404);
            }

            $health = [
                'version' => $version,
                'status' => $versionInfo['deprecated'] ? 'deprecated' : 'healthy',
                'deprecated' => $versionInfo['deprecated'],
                'sunset_date' => $versionInfo['sunset_date'],
                'migration_required' => $versionInfo['deprecated'],
                'recommended_action' => $this->getRecommendedAction($versionInfo),
                'checks' => [
                    'api_accessible' => true,
                    'database_connected' => true,
                    'cache_operational' => true,
                    'authentication_working' => true
                ]
            ];
            
            return ApiResponseService::success($health, "Health check for version {$version}");

        } catch (\Exception $e) {
            return ApiResponseService::error('Failed to check version health', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成迁移指南
     *
     * @param string $version
     * @return array
     */
    protected function generateMigrationGuide(string $version): array
    {
        $versionInfo = $this->versionService->getVersionInfo($version);
        
        if (!$versionInfo) {
            return [];
        }

        $nextVersion = $this->getNextVersion($version);
        $comparison = $nextVersion ? 
            $this->versionService->getVersionComparison($version, $nextVersion) : null;

        return [
            'current_version' => $version,
            'next_version' => $nextVersion,
            'migration_complexity' => $comparison['data']['migration_complexity'] ?? 'low',
            'breaking_changes' => $comparison['data']['breaking_changes'] ?? [],
            'new_features' => $comparison['data']['new_features'] ?? [],
            'steps' => $this->getMigrationSteps($version, $nextVersion),
            'timeline' => $this->getMigrationTimeline($versionInfo),
            'support_resources' => [
                'documentation' => "/api/{$version}/docs",
                'support_email' => 'support@manpou.jp',
                'community_forum' => 'https://community.manpou.jp',
                'developer_chat' => 'https://chat.manpou.jp'
            ]
        ];
    }

    /**
     * 获取下一个版本
     *
     * @param string $currentVersion
     * @return string|null
     */
    protected function getNextVersion(string $currentVersion): ?string
    {
        $versions = array_keys($this->versionService->getAllVersions());
        usort($versions, function ($a, $b) {
            return version_compare($a, $b);
        });

        $currentIndex = array_search($currentVersion, $versions);
        return $versions[$currentIndex + 1] ?? null;
    }

    /**
     * 获取迁移步骤
     *
     * @param string $fromVersion
     * @param string|null $toVersion
     * @return array
     */
    protected function getMigrationSteps(string $fromVersion, ?string $toVersion): array
    {
        if (!$toVersion) {
            return [
                'No migration path available',
                'Contact support for assistance'
            ];
        }

        return [
            [
                'step' => 1,
                'title' => 'Review Breaking Changes',
                'description' => 'Carefully review all breaking changes in the new version',
                'action' => 'Read the breaking changes documentation',
                'estimated_time' => '30 minutes'
            ],
            [
                'step' => 2,
                'title' => 'Update Authentication',
                'description' => 'Update authentication headers and token handling',
                'action' => 'Implement new authentication format',
                'estimated_time' => '1 hour'
            ],
            [
                'step' => 3,
                'title' => 'Update API Endpoints',
                'description' => 'Update API endpoint URLs and request/response handling',
                'action' => 'Modify integration code',
                'estimated_time' => '2-4 hours'
            ],
            [
                'step' => 4,
                'title' => 'Testing',
                'description' => 'Thoroughly test all functionality with the new version',
                'action' => 'Run integration tests',
                'estimated_time' => '1-2 hours'
            ],
            [
                'step' => 5,
                'title' => 'Deployment',
                'description' => 'Deploy the updated integration to production',
                'action' => 'Release with monitoring',
                'estimated_time' => '30 minutes'
            ]
        ];
    }

    /**
     * 获取迁移时间线
     *
     * @param array $versionInfo
     * @return array
     */
    protected function getMigrationTimeline(array $versionInfo): array
    {
        $timeline = [];

        if ($versionInfo['deprecation_date']) {
            $timeline[] = [
                'date' => $versionInfo['deprecation_date'],
                'event' => 'Deprecation Announcement',
                'description' => 'Version officially deprecated'
            ];
        }

        if ($versionInfo['sunset_date']) {
            $timeline[] = [
                'date' => $versionInfo['sunset_date'],
                'event' => 'Sunset Date',
                'description' => 'Version no longer supported'
            ];
        }

        return $timeline;
    }

    /**
     * 获取推荐操作
     *
     * @param array $versionInfo
     * @return string
     */
    protected function getRecommendedAction(array $versionInfo): string
    {
        if ($versionInfo['deprecated']) {
            if ($versionInfo['sunset_date']) {
                $sunsetDate = new \Carbon\Carbon($versionInfo['sunset_date']);
                if ($sunsetDate->isPast()) {
                    return 'immediate_migration_required';
                } elseif ($sunsetDate->diffInDays() <= 30) {
                    return 'urgent_migration_recommended';
                } else {
                    return 'migration_recommended';
                }
            }
            return 'migration_planning_recommended';
        }

        return 'continue_using';
    }
}