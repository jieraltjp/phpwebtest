<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ApiVersionMiddleware
{
    /**
     * API 版本配置
     */
    protected $versions = [
        'v1' => [
            'supported' => true,
            'deprecated' => false,
            'deprecation_date' => null,
            'sunset_date' => null,
            'migration_guide' => '/api/v1/migration-guide'
        ],
        'v2' => [
            'supported' => true,
            'deprecated' => false,
            'deprecation_date' => null,
            'sunset_date' => null,
            'migration_guide' => '/api/v2/migration-guide'
        ]
    ];

    /**
     * 当前默认版本
     */
    protected $defaultVersion = 'v1';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $version = $this->getRequestedVersion($request);
        
        // 验证版本是否支持
        if (!$this->isVersionSupported($version)) {
            return $this->versionNotSupportedResponse($version);
        }

        // 添加版本信息到响应头
        $response = $next($request);
        
        $response->headers->set('API-Version', $version);
        $response->headers->set('API-Supported-Versions', implode(',', $this->getSupportedVersions()));
        
        // 如果版本已弃用，添加弃用警告
        if ($this->isVersionDeprecated($version)) {
            $response->headers->set('API-Deprecated', 'true');
            $response->headers->set('API-Sunset-Date', $this->versions[$version]['sunset_date']);
            $response->headers->set('API-Migration-Guide', $this->versions[$version]['migration_guide']);
        }

        return $response;
    }

    /**
     * 获取请求的 API 版本
     *
     * @param Request $request
     * @return string
     */
    protected function getRequestedVersion(Request $request): string
    {
        // 1. 从 URL 路径获取版本
        $path = $request->path();
        if (preg_match('#^api/(v\d+)#', $path, $matches)) {
            return $matches[1];
        }

        // 2. 从 Accept header 获取版本
        $acceptHeader = $request->header('Accept');
        if ($acceptHeader && preg_match('/application\/vnd\.banho\.api\+(v\d+)/', $acceptHeader, $matches)) {
            return $matches[1];
        }

        // 3. 从自定义 header 获取版本
        $versionHeader = $request->header('API-Version');
        if ($versionHeader && $this->isVersionSupported($versionHeader)) {
            return $versionHeader;
        }

        // 4. 返回默认版本
        return $this->defaultVersion;
    }

    /**
     * 检查版本是否支持
     *
     * @param string $version
     * @return bool
     */
    protected function isVersionSupported(string $version): bool
    {
        return isset($this->versions[$version]) && $this->versions[$version]['supported'];
    }

    /**
     * 检查版本是否已弃用
     *
     * @param string $version
     * @return bool
     */
    protected function isVersionDeprecated(string $version): bool
    {
        return isset($this->versions[$version]) && $this->versions[$version]['deprecated'];
    }

    /**
     * 获取所有支持的版本
     *
     * @return array
     */
    protected function getSupportedVersions(): array
    {
        return array_keys(array_filter($this->versions, function($version) {
            return $version['supported'];
        }));
    }

    /**
     * 版本不支持响应
     *
     * @param string $requestedVersion
     * @return \Illuminate\Http\JsonResponse
     */
    protected function versionNotSupportedResponse(string $requestedVersion)
    {
        return response()->json([
            'status' => 'error',
            'message' => "API version {$requestedVersion} is not supported",
            'data' => [
                'requested_version' => $requestedVersion,
                'supported_versions' => $this->getSupportedVersions(),
                'default_version' => $this->defaultVersion,
                'documentation' => '/api/versions'
            ],
            'timestamp' => now()->toISOString()
        ], 400);
    }
}