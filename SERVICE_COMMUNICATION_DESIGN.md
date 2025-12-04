# 微服务通信机制设计

## 概述

本文档详细描述了万方商事 B2B 采购门户微服务架构中的服务通信机制，包括服务注册发现、API 网关、服务间通信协议和负载均衡策略。

## 1. 服务注册与发现

### 1.1 Consul 服务注册中心

```yaml
# docker-compose-consul.yml
version: '3.8'
services:
  consul:
    image: consul:1.16
    container_name: consul
    ports:
      - "8500:8500"
      - "8600:8600/udp"
    networks:
      - microservices
    command: agent -server -bootstrap -ui -client=0.0.0.0
    environment:
      - CONSUL_BIND_INTERFACE=eth0
    volumes:
      - consul_data:/consul/data

volumes:
  consul_data:

networks:
  microservices:
    driver: bridge
```

### 1.2 服务自动注册实现

```php
<?php
// app/Services/ServiceRegistry.php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ServiceRegistry
{
    private Client $httpClient;
    private string $consulHost;
    private int $consulPort;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->consulHost = config('services.consul.host', 'consul');
        $this->consulPort = config('services.consul.port', 8500);
    }

    /**
     * 注册服务到 Consul
     */
    public function register(array $serviceConfig): bool
    {
        try {
            $response = $this->httpClient->put("http://{$this->consulHost}:{$this->consulPort}/v1/agent/service/register", [
                'json' => [
                    'ID' => $serviceConfig['id'],
                    'Name' => $serviceConfig['name'],
                    'Tags' => $serviceConfig['tags'] ?? [],
                    'Address' => $serviceConfig['address'],
                    'Port' => $serviceConfig['port'],
                    'Check' => [
                        'HTTP' => "http://{$serviceConfig['address']}:{$serviceConfig['port']}/health",
                        'Interval' => '10s',
                        'Timeout' => '5s',
                        'DeregisterCriticalServiceAfter' => '30s'
                    ]
                ]
            ]);

            Log::info("Service {$serviceConfig['name']} registered successfully");
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error("Failed to register service: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 从 Consul 注销服务
     */
    public function deregister(string $serviceId): bool
    {
        try {
            $response = $this->httpClient->put("http://{$this->consulHost}:{$this->consulPort}/v1/agent/service/deregister/{$serviceId}");
            
            Log::info("Service {$serviceId} deregistered successfully");
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error("Failed to deregister service: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 发现服务实例
     */
    public function discover(string $serviceName): array
    {
        try {
            $response = $this->httpClient->get("http://{$this->consulHost}:{$this->consulPort}/v1/health/service/{$serviceName}");
            
            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $services = json_decode($response->getBody()->getContents(), true);
            $healthyServices = array_filter($services, function ($service) {
                return $service['Checks'][1]['Status'] === 'passing';
            });

            return array_map(function ($service) {
                return [
                    'address' => $service['Service']['Address'],
                    'port' => $service['Service']['Port'],
                    'id' => $service['Service']['ID']
                ];
            }, $healthyServices);
        } catch (\Exception $e) {
            Log::error("Failed to discover service {$serviceName}: " . $e->getMessage());
            return [];
        }
    }
}
```

### 1.3 服务发现客户端

```php
<?php
// app/Services/ServiceDiscovery.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ServiceDiscovery
{
    private ServiceRegistry $registry;
    private array $serviceCache = [];

    public function __construct(ServiceRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * 获取服务地址
     */
    public function getServiceUrl(string $serviceName, string $path = ''): string
    {
        $cacheKey = "service.{$serviceName}";
        
        // 使用缓存减少 Consul 查询
        $service = Cache::remember($cacheKey, 30, function () use ($serviceName) {
            $services = $this->registry->discover($serviceName);
            
            if (empty($services)) {
                throw new \Exception("No healthy instances found for service: {$serviceName}");
            }

            // 简单的负载均衡：随机选择
            return $services[array_rand($services)];
        });

        return "http://{$service['address']}:{$service['port']}" . $path;
    }

    /**
     * 获取所有服务实例
     */
    public function getAllServices(string $serviceName): array
    {
        return $this->registry->discover($serviceName);
    }
}
```

## 2. API 网关设计

### 2.1 API 网关配置

```php
<?php
// routes/gateway.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 路由到用户服务
Route::prefix('api/auth')->group(function () {
    Route::any('{path}', function (Request $request, $path) {
        return forwardRequest('user-service', "/api/auth/{$path}", $request);
    })->where('path', '.*');
});

Route::prefix('api/users')->group(function () {
    Route::any('{path}', function (Request $request, $path) {
        return forwardRequest('user-service', "/api/users/{$path}", $request);
    })->where('path', '.*');
});

// 路由到产品服务
Route::prefix('api/products')->group(function () {
    Route::any('{path}', function (Request $request, $path) {
        return forwardRequest('product-service', "/api/products/{$path}", $request);
    })->where('path', '.*');
});

// 路由到订单服务
Route::prefix('api/orders')->group(function () {
    Route::any('{path}', function (Request $request, $path) {
        return forwardRequest('order-service', "/api/orders/{$path}", $request);
    })->where('path', '.*');
});

// 路由到询价服务
Route::prefix('api/inquiries')->group(function () {
    Route::any('{path}', function (Request $request, $path) {
        return forwardRequest('inquiry-service', "/api/inquiries/{$path}", $request);
    })->where('path', '.*');
});

// 路由到采购服务
Route::prefix('api/bulk-purchase')->group(function () {
    Route::any('{path}', function (Request $request, $path) {
        return forwardRequest('purchase-service', "/api/bulk-purchase/{$path}", $request);
    })->where('path', '.*');
});

/**
 * 转发请求到微服务
 */
function forwardRequest(string $serviceName, string $path, Request $request)
{
    try {
        $discovery = app(\App\Services\ServiceDiscovery::class);
        $serviceUrl = $discovery->getServiceUrl($serviceName, $path);

        $client = new \GuzzleHttp\Client();
        
        $response = $client->request($request->method(), $serviceUrl, [
            'headers' => $request->headers->all(),
            'json' => $request->json()->all(),
            'form_params' => $request->all(),
            'query' => $request->query->all(),
            'timeout' => 30
        ]);

        return response($response->getBody()->getContents(), $response->getStatusCode())
            ->withHeaders($response->getHeaders());

    } catch (\Exception $e) {
        \Log::error("Gateway request failed: " . $e->getMessage());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Service temporarily unavailable',
            'timestamp' => now()->toISOString()
        ], 503);
    }
}
```

### 2.2 网关中间件

```php
<?php
// app/Http/Middleware/GatewayAuth.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GatewayAuth
{
    /**
     * 处理传入请求
     */
    public function handle(Request $request, Closure $next)
    {
        // JWT 令牌验证
        $token = $request->bearerToken();
        
        if (!$token && $this->requiresAuth($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required',
                'timestamp' => now()->toISOString()
            ], 401);
        }

        if ($token && $this->requiresAuth($request)) {
            try {
                $userServiceUrl = app(\App\Services\ServiceDiscovery::class)
                    ->getServiceUrl('user-service', '/api/auth/validate');
                
                $client = new \GuzzleHttp\Client();
                $response = $client->post($userServiceUrl, [
                    'headers' => ['Authorization' => "Bearer {$token}"]
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \Exception('Invalid token');
                }

                // 将用户信息添加到请求中
                $userInfo = json_decode($response->getBody()->getContents(), true);
                $request->merge(['user' => $userInfo]);

            } catch (\Exception $e) {
                Log::warning("Gateway auth failed: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid authentication token',
                    'timestamp' => now()->toISOString()
                ], 401);
            }
        }

        return $next($request);
    }

    /**
     * 检查请求是否需要认证
     */
    private function requiresAuth(Request $request): bool
    {
        $publicPaths = [
            'api/auth/login',
            'api/auth/register',
            'api/products',
            'api/config',
            'api/health'
        ];

        $path = $request->path();
        return !in_array($path, $publicPaths);
    }
}
```

### 2.3 限流中间件

```php
<?php
// app/Http/Middleware/RateLimiting.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class RateLimiting
{
    /**
     * 限流配置
     */
    private array $limits = [
        'default' => ['requests' => 100, 'window' => 60], // 100 requests per minute
        'api/auth/login' => ['requests' => 10, 'window' => 60], // 10 login attempts per minute
        'api/products/search' => ['requests' => 30, 'window' => 60], // 30 searches per minute
    ];

    /**
     * 处理限流
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $this->getRateLimitKey($request);
        $limit = $this->getLimitForPath($request->path());

        if (!$this->checkRateLimit($key, $limit)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rate limit exceeded',
                'timestamp' => now()->toISOString(),
                'retry_after' => $limit['window']
            ], 429);
        }

        return $next($request);
    }

    /**
     * 获取限流键
     */
    private function getRateLimitKey(Request $request): string
    {
        $ip = $request->ip();
        $userId = $request->user()['id'] ?? 'anonymous';
        $path = $request->path();
        
        return "rate_limit:{$path}:{$userId}:{$ip}";
    }

    /**
     * 获取路径限制
     */
    private function getLimitForPath(string $path): array
    {
        foreach ($this->limits as $pattern => $limit) {
            if (str_starts_with($path, $pattern)) {
                return $limit;
            }
        }
        
        return $this->limits['default'];
    }

    /**
     * 检查限流
     */
    private function checkRateLimit(string $key, array $limit): bool
    {
        $current = Redis::incr($key);
        
        if ($current === 1) {
            Redis::expire($key, $limit['window']);
        }

        return $current <= $limit['requests'];
    }
}
```

## 3. 服务间通信

### 3.1 HTTP 客户端封装

```php
<?php
// app/Services/ServiceClient.php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ServiceClient
{
    private Client $httpClient;
    private ServiceDiscovery $discovery;

    public function __construct(ServiceDiscovery $discovery)
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'connect_timeout' => 5,
            'http_errors' => false
        ]);
        $this->discovery = $discovery;
    }

    /**
     * 发送 GET 请求
     */
    public function get(string $serviceName, string $path, array $query = []): array
    {
        return $this->request('GET', $serviceName, $path, ['query' => $query]);
    }

    /**
     * 发送 POST 请求
     */
    public function post(string $serviceName, string $path, array $data = []): array
    {
        return $this->request('POST', $serviceName, $path, ['json' => $data]);
    }

    /**
     * 发送 PUT 请求
     */
    public function put(string $serviceName, string $path, array $data = []): array
    {
        return $this->request('PUT', $serviceName, $path, ['json' => $data]);
    }

    /**
     * 发送 DELETE 请求
     */
    public function delete(string $serviceName, string $path): array
    {
        return $this->request('DELETE', $serviceName, $path);
    }

    /**
     * 通用请求方法
     */
    private function request(string $method, string $serviceName, string $path, array $options = []): array
    {
        try {
            $url = $this->discovery->getServiceUrl($serviceName, $path);
            
            // 添加服务间认证头
            $options['headers'] = array_merge($options['headers'] ?? [], [
                'X-Service-Auth' => config('services.internal_token'),
                'X-Request-ID' => uniqid('req_', true),
                'X-Forwarded-For' => request()->ip()
            ]);

            $response = $this->httpClient->request($method, $url, $options);
            
            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody()->getContents(), true),
                'headers' => $response->getHeaders()
            ];

        } catch (RequestException $e) {
            Log::error("Service request failed: {$serviceName}{$path} - " . $e->getMessage());
            
            return [
                'status' => $e->getCode() ?: 500,
                'error' => $e->getMessage(),
                'data' => null
            ];
        } catch (\Exception $e) {
            Log::error("Service communication error: {$serviceName}{$path} - " . $e->getMessage());
            
            return [
                'status' => 500,
                'error' => 'Service communication failed',
                'data' => null
            ];
        }
    }
}
```

### 3.2 断路器模式实现

```php
<?php
// app/Services/CircuitBreaker.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CircuitBreaker
{
    private string $serviceName;
    private int $failureThreshold;
    private int $recoveryTimeout;
    private string $cacheKey;

    public function __construct(string $serviceName, int $failureThreshold = 5, int $recoveryTimeout = 60)
    {
        $this->serviceName = $serviceName;
        $this->failureThreshold = $failureThreshold;
        $this->recoveryTimeout = $recoveryTimeout;
        $this->cacheKey = "circuit_breaker:{$serviceName}";
    }

    /**
     * 执行操作
     */
    public function call(callable $operation): mixed
    {
        if ($this->isOpen()) {
            throw new \Exception("Circuit breaker is open for service: {$this->serviceName}");
        }

        try {
            $result = $operation();
            $this->onSuccess();
            return $result;
        } catch (\Exception $e) {
            $this->onFailure();
            throw $e;
        }
    }

    /**
     * 检查断路器是否开启
     */
    private function isOpen(): bool
    {
        $state = Cache::get($this->cacheKey, [
            'state' => 'closed',
            'failures' => 0,
            'last_failure' => null
        ]);

        if ($state['state'] === 'open') {
            // 检查是否应该进入半开状态
            if ($state['last_failure'] && (time() - $state['last_failure']) > $this->recoveryTimeout) {
                $this->setState('half-open');
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * 操作成功
     */
    private function onSuccess(): void
    {
        $this->setState('closed', 0);
        Log::info("Circuit breaker closed for service: {$this->serviceName}");
    }

    /**
     * 操作失败
     */
    private function onFailure(): void
    {
        $state = Cache::get($this->cacheKey, [
            'state' => 'closed',
            'failures' => 0,
            'last_failure' => null
        ]);

        $failures = $state['failures'] + 1;

        if ($failures >= $this->failureThreshold) {
            $this->setState('open', $failures);
            Log::warning("Circuit breaker opened for service: {$this->serviceName} after {$failures} failures");
        } else {
            $this->setState($state['state'], $failures);
        }
    }

    /**
     * 设置断路器状态
     */
    private function setState(string $state, int $failures = 0): void
    {
        Cache::put($this->cacheKey, [
            'state' => $state,
            'failures' => $failures,
            'last_failure' => $state === 'open' ? time() : null
        ], 3600);
    }
}
```

### 3.3 重试机制

```php
<?php
// app/Services/RetryPolicy.php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class RetryPolicy
{
    private int $maxAttempts;
    private array $retryDelays;
    private array $retryableErrors;

    public function __construct(int $maxAttempts = 3, array $retryDelays = [100, 500, 1000])
    {
        $this->maxAttempts = $maxAttempts;
        $this->retryDelays = $retryDelays;
        $this->retryableErrors = [500, 502, 503, 504];
    }

    /**
     * 执行重试操作
     */
    public function execute(callable $operation): mixed
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            try {
                return $operation();
            } catch (\Exception $e) {
                $lastException = $e;
                
                if (!$this->shouldRetry($e) || $attempt === $this->maxAttempts) {
                    throw $e;
                }

                $delay = $this->retryDelays[$attempt - 1] ?? 1000;
                Log::warning("Operation failed, retrying in {$delay}ms (attempt {$attempt}/{$this->maxAttempts}): " . $e->getMessage());
                
                usleep($delay * 1000);
            }
        }

        throw $lastException;
    }

    /**
     * 判断是否应该重试
     */
    private function shouldRetry(\Exception $e): bool
    {
        // 检查是否为可重试的HTTP错误
        if (method_exists($e, 'getCode') && in_array($e->getCode(), $this->retryableErrors)) {
            return true;
        }

        // 检查是否为网络错误
        $networkErrors = ['connection', 'timeout', 'dns'];
        $message = strtolower($e->getMessage());
        
        foreach ($networkErrors as $error) {
            if (str_contains($message, $error)) {
                return true;
            }
        }

        return false;
    }
}
```

## 4. 负载均衡策略

### 4.1 负载均衡器

```php
<?php
// app/Services/LoadBalancer.php
namespace App\Services;

class LoadBalancer
{
    private array $strategies = [
        'round_robin' => RoundRobinStrategy::class,
        'random' => RandomStrategy::class,
        'least_connections' => LeastConnectionsStrategy::class,
        'weighted' => WeightedStrategy::class
    ];

    /**
     * 选择服务实例
     */
    public function selectInstance(array $instances, string $strategy = 'round_robin'): ?array
    {
        if (empty($instances)) {
            return null;
        }

        $strategyClass = $this->strategies[$strategy] ?? $this->strategies['round_robin'];
        $strategyInstance = new $strategyClass();

        return $strategyInstance->select($instances);
    }
}

/**
 * 轮询策略
 */
class RoundRobinStrategy
{
    private array $counters = [];

    public function select(array $instances): array
    {
        $serviceName = $instances[0]['service_name'] ?? 'default';
        $index = ($this->counters[$serviceName] ?? 0) % count($instances);
        $this->counters[$serviceName] = $index + 1;

        return $instances[$index];
    }
}

/**
 * 随机策略
 */
class RandomStrategy
{
    public function select(array $instances): array
    {
        return $instances[array_rand($instances)];
    }
}

/**
 * 最少连接策略
 */
class LeastConnectionsStrategy
{
    public function select(array $instances): array
    {
        $selected = $instances[0];
        $minConnections = $selected['connections'] ?? 0;

        foreach ($instances as $instance) {
            $connections = $instance['connections'] ?? 0;
            if ($connections < $minConnections) {
                $selected = $instance;
                $minConnections = $connections;
            }
        }

        return $selected;
    }
}

/**
 * 权重策略
 */
class WeightedStrategy
{
    public function select(array $instances): array
    {
        $totalWeight = array_sum(array_column($instances, 'weight'));
        
        if ($totalWeight === 0) {
            return $instances[0];
        }

        $random = mt_rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($instances as $instance) {
            $currentWeight += $instance['weight'] ?? 1;
            if ($random <= $currentWeight) {
                return $instance;
            }
        }

        return $instances[0];
    }
}
```

### 4.2 健康检查

```php
<?php
// app/Http/Controllers/HealthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    /**
     * 健康检查端点
     */
    public function health(Request $request)
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'service' => config('app.name'),
            'version' => config('app.version', '1.0.0'),
            'checks' => []
        ];

        // 数据库连接检查
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // Redis 连接检查
        try {
            Redis::ping();
            $health['checks']['redis'] = [
                'status' => 'healthy',
                'message' => 'Redis connection successful'
            ];
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['redis'] = [
                'status' => 'unhealthy',
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ];
        }

        // 内存使用检查
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryUsage > 256) { // 256MB 警告阈值
            $health['checks']['memory'] = [
                'status' => 'warning',
                'message' => "High memory usage: {$memoryUsage}MB"
            ];
        } else {
            $health['checks']['memory'] = [
                'status' => 'healthy',
                'message' => "Memory usage: {$memoryUsage}MB"
            ];
        }

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    /**
     * 就绪检查端点
     */
    public function readiness(Request $request)
    {
        // 检查服务是否准备好接收流量
        $ready = true;
        $checks = [];

        // 检查必要的服务依赖
        $requiredServices = config('services.dependencies', []);
        
        foreach ($requiredServices as $service) {
            try {
                $discovery = app(\App\Services\ServiceDiscovery::class);
                $instances = $discovery->getAllServices($service);
                
                if (empty($instances)) {
                    $ready = false;
                    $checks[$service] = [
                        'status' => 'not_ready',
                        'message' => "No healthy instances of {$service} found"
                    ];
                } else {
                    $checks[$service] = [
                        'status' => 'ready',
                        'message' => count($instances) . " healthy instances found"
                    ];
                }
            } catch (\Exception $e) {
                $ready = false;
                $checks[$service] = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        $response = [
            'status' => $ready ? 'ready' : 'not_ready',
            'timestamp' => now()->toISOString(),
            'checks' => $checks
        ];

        return response()->json($response, $ready ? 200 : 503);
    }
}
```

## 5. 配置文件

### 5.1 服务配置

```php
<?php
// config/services.php
return [
    'consul' => [
        'host' => env('CONSUL_HOST', 'consul'),
        'port' => env('CONSUL_PORT', 8500),
    ],

    'internal_token' => env('INTERNAL_SERVICE_TOKEN', 'internal-service-secret'),

    'discovery' => [
        'cache_ttl' => env('SERVICE_DISCOVERY_CACHE_TTL', 30),
    ],

    'circuit_breaker' => [
        'failure_threshold' => env('CIRCUIT_BREAKER_FAILURE_THRESHOLD', 5),
        'recovery_timeout' => env('CIRCUIT_BREAKER_RECOVERY_TIMEOUT', 60),
    ],

    'retry_policy' => [
        'max_attempts' => env('RETRY_MAX_ATTEMPTS', 3),
        'delays' => [100, 500, 1000], // milliseconds
    ],

    'dependencies' => [
        // 定义服务依赖关系
        'order-service' => ['user-service', 'product-service'],
        'purchase-service' => ['user-service', 'product-service'],
        'inquiry-service' => ['user-service', 'product-service'],
    ],

    'rate_limiting' => [
        'default' => [
            'requests' => env('RATE_LIMIT_DEFAULT_REQUESTS', 100),
            'window' => env('RATE_LIMIT_DEFAULT_WINDOW', 60), // seconds
        ],
        'auth' => [
            'requests' => env('RATE_LIMIT_AUTH_REQUESTS', 10),
            'window' => env('RATE_LIMIT_AUTH_WINDOW', 60),
        ],
    ],
];
```

### 5.2 Docker Compose 配置

```yaml
# docker-compose-services.yml
version: '3.8'
services:
  # API 网关
  api-gateway:
    build:
      context: .
      dockerfile: docker/gateway/Dockerfile
    ports:
      - "8080:8080"
    environment:
      - APP_NAME=api-gateway
      - APP_ENV=production
      - CONSUL_HOST=consul
      - CONSUL_PORT=8500
    depends_on:
      - consul
      - user-service
      - product-service
    networks:
      - microservices

  # 用户服务
  user-service:
    build:
      context: .
      dockerfile: docker/user-service/Dockerfile
    ports:
      - "8001:8001"
    environment:
      - APP_NAME=user-service
      - APP_ENV=production
      - DB_HOST=mysql-user
      - DB_PORT=3306
      - DB_DATABASE=user_db
      - DB_USERNAME=root
      - DB_PASSWORD=secret
      - REDIS_HOST=redis
      - CONSUL_HOST=consul
    depends_on:
      - mysql-user
      - redis
      - consul
    networks:
      - microservices

  # 产品服务
  product-service:
    build:
      context: .
      dockerfile: docker/product-service/Dockerfile
    ports:
      - "8002:8002"
    environment:
      - APP_NAME=product-service
      - APP_ENV=production
      - DB_HOST=mysql-product
      - DB_PORT=3306
      - DB_DATABASE=product_db
      - DB_USERNAME=root
      - DB_PASSWORD=secret
      - REDIS_HOST=redis
      - ELASTICSEARCH_HOST=elasticsearch
      - CONSUL_HOST=consul
    depends_on:
      - mysql-product
      - redis
      - elasticsearch
      - consul
    networks:
      - microservices

  # 其他服务配置...

networks:
  microservices:
    driver: bridge

volumes:
  mysql_user_data:
  mysql_product_data:
  mysql_order_data:
  consul_data:
  elasticsearch_data:
```

## 6. 监控和日志

### 6.1 请求追踪中间件

```php
<?php
// app/Http/Middleware/RequestTracing.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RequestTracing
{
    /**
     * 处理请求追踪
     */
    public function handle(Request $request, Closure $next)
    {
        $traceId = $request->header('X-Trace-ID') ?: Str::uuid()->toString();
        $spanId = Str::uuid()->toString();

        // 将追踪信息添加到请求中
        $request->merge([
            'trace_id' => $traceId,
            'span_id' => $spanId
        ]);

        // 添加追踪头到响应
        $response = $next($request);
        $response->headers->set('X-Trace-ID', $traceId);
        $response->headers->set('X-Span-ID', $spanId);

        // 记录请求日志
        $this->logRequest($request, $response, $traceId, $spanId);

        return $response;
    }

    /**
     * 记录请求日志
     */
    private function logRequest(Request $request, $response, string $traceId, string $spanId): void
    {
        $logData = [
            'trace_id' => $traceId,
            'span_id' => $spanId,
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration' => microtime(true) - LARAVEL_START,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'service' => config('app.name')
        ];

        if ($response->getStatusCode() >= 400) {
            Log::error('Request failed', $logData);
        } else {
            Log::info('Request completed', $logData);
        }
    }
}
```

---

**文档版本**: v1.0.0  
**创建日期**: 2025年12月4日  
**最后更新**: 2025年12月4日  
**维护团队**: 万方商事技术团队