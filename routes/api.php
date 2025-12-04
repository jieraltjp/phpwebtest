<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ApiVersionController;
use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\ProductController as V1ProductController;
use App\Http\Controllers\Api\V1\OrderController as V1OrderController;
use App\Http\Controllers\Api\V2\AuthController as V2AuthController;
use App\Http\Controllers\Api\V2\ProductController as V2ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API 版本管理路由
Route::prefix('versions')->group(function () {
    Route::get('/', [ApiVersionController::class, 'index']);
    Route::get('/statistics', [ApiVersionController::class, 'statistics']);
    Route::post('/compare', [ApiVersionController::class, 'compare']);
    Route::post('/clear-cache', [ApiVersionController::class, 'clearCache']);
    Route::get('/{version}', [ApiVersionController::class, 'show']);
    Route::get('/{version}/health', [ApiVersionController::class, 'health']);
    Route::get('/{version}/migration-guide', [ApiVersionController::class, 'migrationGuide']);
});

// API v1 路由 (稳定版本)
Route::prefix('v1')->middleware(['api.version'])->group(function () {
    
    // 认证相关路由（不需要token验证）
    Route::post('/auth/login', [V1AuthController::class, 'login']);
    Route::post('/auth/register', [V1AuthController::class, 'register']);
    Route::post('/auth/check-username', [V1AuthController::class, 'checkUsername']);
    Route::post('/auth/check-email', [V1AuthController::class, 'checkEmail']);
    
    // 需要认证的路由组
    Route::middleware(['jwt.auth'])->group(function () {
        
        // 用户认证信息
        Route::post('/auth/logout', [V1AuthController::class, 'logout']);
        Route::post('/auth/refresh', [V1AuthController::class, 'refresh']);
        Route::get('/auth/me', [V1AuthController::class, 'me']);
        
        // 产品相关路由
        Route::get('/products', [V1ProductController::class, 'index']);
        Route::get('/products/{id}', [V1ProductController::class, 'show']);
        
        // 订单相关路由
        Route::get('/orders', [V1OrderController::class, 'index']);
        Route::post('/orders', [V1OrderController::class, 'store']);
        Route::get('/orders/{id}', [V1OrderController::class, 'show']);
        Route::get('/orders/{id}/tracking-link', [V1OrderController::class, 'trackingLink']);
    });
});

// API v2 路由 (预览版本)
Route::prefix('v2')->middleware(['api.version'])->group(function () {
    
    // 认证相关路由（不需要token验证）
    Route::post('/auth/login', [V2AuthController::class, 'login']);
    Route::post('/auth/register', [V2AuthController::class, 'register']);
    Route::post('/auth/verify-email', [V2AuthController::class, 'verifyEmail']);
    
    // 需要认证的路由组
    Route::middleware(['jwt.auth'])->group(function () {
        
        // 用户认证信息
        Route::post('/auth/logout', [V2AuthController::class, 'logout']);
        Route::post('/auth/refresh', [V2AuthController::class, 'refresh']);
        Route::get('/auth/me', [V2AuthController::class, 'me']);
        Route::post('/auth/enable-2fa', [V2AuthController::class, 'enable2FA']);
        
        // 产品相关路由
        Route::get('/products', [V2ProductController::class, 'index']);
        Route::get('/products/{id}', [V2ProductController::class, 'show']);
        Route::get('/products/suggestions', [V2ProductController::class, 'suggestions']);
        Route::post('/products/compare', [V2ProductController::class, 'compare']);
    });
});

// 兼容性路由 - 默认指向 v1
Route::fallback(function () {
    return redirect()->route('api.v1.auth.login');
});

// 保持原有路由的向后兼容性 (临时)
Route::prefix('legacy')->group(function () {
    
    // 认证相关路由（不需要token验证）
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // 需要认证的路由组
    Route::middleware(['jwt.auth'])->group(function () {
        
        // 用户认证信息
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        
        // 产品相关路由
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        
        // 订单相关路由
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::get('/orders/{id}/tracking-link', [OrderController::class, 'trackingLink']);
    });
});

// API 健康检查
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// 测试路由（不需要认证）
Route::get('/test', [App\Http\Controllers\TestController::class, 'index']);
Route::get('/test/database', [App\Http\Controllers\TestController::class, 'database']);
Route::get('/test/products', [App\Http\Controllers\TestController::class, 'products']);
Route::post('/test/login', [App\Http\Controllers\TestController::class, 'login']);

// 测试订单路由
Route::get('/test/orders', [App\Http\Controllers\TestController::class, 'orders']);
Route::get('/test/orders/{id}', [App\Http\Controllers\TestController::class, 'orderDetail']);
Route::post('/test/orders', [App\Http\Controllers\TestController::class, 'createOrder']);

// 权限管理 API 路由
Route::middleware(['jwt.auth'])->prefix('permissions')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\PermissionController::class, 'index'])->middleware('permission:view-permissions');
    Route::post('/', [App\Http\Controllers\Api\PermissionController::class, 'store'])->middleware('permission:create-permissions');
    Route::get('/all', [App\Http\Controllers\Api\PermissionController::class, 'all'])->middleware('permission:view-permissions');
    Route::get('/{id}', [App\Http\Controllers\Api\PermissionController::class, 'show'])->middleware('permission:view-permissions');
    Route::put('/{id}', [App\Http\Controllers\Api\PermissionController::class, 'update'])->middleware('permission:edit-permissions');
    Route::delete('/{id}', [App\Http\Controllers\Api\PermissionController::class, 'destroy'])->middleware('permission:delete-permissions');
    Route::get('/groups/list', [App\Http\Controllers\Api\PermissionController::class, 'getGroups'])->middleware('permission:view-permissions');
});

Route::middleware(['jwt.auth'])->prefix('roles')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\PermissionController::class, 'roles'])->middleware('permission:view-roles');
    Route::post('/', [App\Http\Controllers\Api\PermissionController::class, 'createRole'])->middleware('permission:create-roles');
    Route::get('/all', [App\Http\Controllers\Api\PermissionController::class, 'allRoles'])->middleware('permission:view-roles');
    Route::get('/{id}', [App\Http\Controllers\Api\PermissionController::class, 'showRole'])->middleware('permission:view-roles');
    Route::put('/{id}', [App\Http\Controllers\Api\PermissionController::class, 'updateRole'])->middleware('permission:edit-roles');
    Route::delete('/{id}', [App\Http\Controllers\Api\PermissionController::class, 'deleteRole'])->middleware('permission:delete-roles');
    Route::get('/{id}/permissions', [App\Http\Controllers\Api\PermissionController::class, 'rolePermissions'])->middleware('permission:view-roles');
    Route::post('/{id}/permissions', [App\Http\Controllers\Api\PermissionController::class, 'assignPermissionsToRole'])->middleware('permission:edit-roles');
});

Route::middleware(['jwt.auth'])->prefix('users')->group(function () {
    Route::get('/permissions', [App\Http\Controllers\Api\PermissionController::class, 'users'])->middleware('permission:view-user-permissions');
    Route::get('/{id}/roles', [App\Http\Controllers\Api\PermissionController::class, 'userRoles'])->middleware('permission:view-user-permissions');
    Route::post('/{id}/roles', [App\Http\Controllers\Api\PermissionController::class, 'assignRole'])->middleware('permission:edit-user-permissions');
    Route::get('/{id}/permissions', [App\Http\Controllers\Api\PermissionController::class, 'userPermissions'])->middleware('permission:view-user-permissions');
    Route::post('/{id}/permissions', [App\Http\Controllers\Api\PermissionController::class, 'assignPermissions'])->middleware('permission:edit-user-permissions');
    Route::get('/{id}/permissions/details', [App\Http\Controllers\Api\PermissionController::class, 'userPermissionDetails'])->middleware('permission:view-user-permissions');
    Route::post('/batch/permissions', [App\Http\Controllers\Api\PermissionController::class, 'batchAssignPermissions'])->middleware('permission:edit-user-permissions');
});

// API限流管理路由
Route::middleware(['jwt.auth'])->prefix('throttle')->group(function () {
    Route::get('/stats', [App\Http\Controllers\Api\ApiThrottleController::class, 'getStats'])->middleware('permission:view-system-logs');
    Route::get('/config', [App\Http\Controllers\Api\ApiThrottleController::class, 'getConfig'])->middleware('permission:view-system-logs');
    Route::get('/blacklist', [App\Http\Controllers\Api\ApiThrottleController::class, 'getBlacklist'])->middleware('permission:manage-api-keys');
    Route::post('/blacklist/add', [App\Http\Controllers\Api\ApiThrottleController::class, 'addToBlacklist'])->middleware('permission:manage-api-keys');
    Route::post('/blacklist/remove', [App\Http\Controllers\Api\ApiThrottleController::class, 'removeFromBlacklist'])->middleware('permission:manage-api-keys');
    Route::get('/anomalies', [App\Http\Controllers\Api\ApiThrottleController::class, 'getAnomalies'])->middleware('permission:view-system-logs');
    Route::post('/cleanup', [App\Http\Controllers\Api\ApiThrottleController::class, 'cleanup'])->middleware('permission:manage-cache');
});

// 多语言管理路由
Route::prefix('i18n')->group(function () {
    Route::get('/locales', [App\Http\Controllers\Api\I18nController::class, 'getSupportedLocales']);
    Route::get('/config', [App\Http\Controllers\Api\I18nController::class, 'getConfig']);
    Route::get('/translations', [App\Http\Controllers\Api\I18nController::class, 'getTranslations']);
    Route::post('/switch', [App\Http\Controllers\Api\I18nController::class, 'switchLocale']);
    Route::post('/format', [App\Http\Controllers\Api\I18nController::class, 'formatData']);
    Route::post('/clear-cache', [App\Http\Controllers\Api\I18nController::class, 'clearCache'])->middleware('permission:manage-cache');
});

// 报表管理路由
Route::middleware(['jwt.auth'])->prefix('reports')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Api\ReportController::class, 'getDashboardOverview'])->middleware('permission:view-sales-reports');
    Route::get('/sales', [App\Http\Controllers\Api\ReportController::class, 'getSalesReport'])->middleware('permission:view-sales-reports');
    Route::get('/user-behavior', [App\Http\Controllers\Api\ReportController::class, 'getUserBehaviorReport'])->middleware('permission:view-user-reports');
    Route::get('/product-analysis', [App\Http\Controllers\Api\ReportController::class, 'getProductAnalysisReport'])->middleware('permission:view-product-reports');
    Route::get('/inquiry-analysis', [App\Http\Controllers\Api\ReportController::class, 'getInquiryAnalysisReport'])->middleware('permission:view-sales-reports');
    Route::get('/financial', [App\Http\Controllers\Api\ReportController::class, 'getFinancialReport'])->middleware('permission:view-financial-data');
    Route::get('/config', [App\Http\Controllers\Api\ReportController::class, 'getConfig'])->middleware('permission:view-sales-reports');
    Route::post('/export', [App\Http\Controllers\Api\ReportController::class, 'exportReport'])->middleware('permission:export-reports');
    Route::post('/clear-cache', [App\Http\Controllers\Api\ReportController::class, 'clearCache'])->middleware('permission:manage-cache');
});

// 管理员 API 路由
Route::prefix('admin')->group(function () {
    Route::get('/stats', [App\Http\Controllers\Admin\AdminController::class, 'getStats']);
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'getUsers']);
    Route::get('/orders', [App\Http\Controllers\Admin\AdminController::class, 'getOrders']);
    Route::get('/system-status', [App\Http\Controllers\Admin\AdminController::class, 'getSystemStatus']);
    Route::get('/activities', [App\Http\Controllers\Admin\AdminController::class, 'getRecentActivities']);
    Route::post('/users/{id}/approve', [App\Http\Controllers\Admin\AdminController::class, 'approveUser']);
    Route::post('/orders/{id}/status', [App\Http\Controllers\Admin\AdminController::class, 'updateOrderStatus']);
});

// 加密服务 API 路由（需要管理员权限）
Route::prefix('encryption')->middleware(['jwt.auth', 'throttle:admin'])->group(function () {
    Route::get('/status', [App\Http\Controllers\Api\EncryptionController::class, 'status']);
    Route::post('/test', [App\Http\Controllers\Api\EncryptionController::class, 'test']);
    Route::post('/encrypt-batch', [App\Http\Controllers\Api\EncryptionController::class, 'encryptBatch']);
    Route::post('/generate-token', [App\Http\Controllers\Api\EncryptionController::class, 'generateToken']);
    Route::post('/mask-data', [App\Http\Controllers\Api\EncryptionController::class, 'maskData']);
    Route::post('/verify-hash', [App\Http\Controllers\Api\EncryptionController::class, 'verifyHash']);
    Route::post('/generate-hash', [App\Http\Controllers\Api\EncryptionController::class, 'generateHash']);
});

// 事件系统 API 路由（需要管理员权限）
Route::prefix('events')->middleware(['jwt.auth', 'throttle:admin'])->group(function () {
    Route::get('/statistics', [App\Http\Controllers\EventController::class, 'statistics']);
    Route::get('/history', [App\Http\Controllers\EventController::class, 'history']);
    Route::get('/monitoring', [App\Http\Controllers\EventController::class, 'monitoring']);
    Route::get('/performance', [App\Http\Controllers\EventController::class, 'performance']);
    Route::get('/realtime', [App\Http\Controllers\EventController::class, 'realtime']);
    Route::get('/debug', [App\Http\Controllers\EventController::class, 'debug']);
    Route::post('/toggle', [App\Http\Controllers\EventController::class, 'toggle']);
    Route::post('/monitoring/toggle', [App\Http\Controllers\EventController::class, 'monitoringToggle']);
    Route::delete('/history', [App\Http\Controllers\EventController::class, 'clearHistory']);
    Route::delete('/monitoring', [App\Http\Controllers\EventController::class, 'clearMonitoring']);
    Route::get('/export', [App\Http\Controllers\EventController::class, 'export']);
    Route::post('/reset', [App\Http\Controllers\EventController::class, 'reset']);
});

// WebSocket API 路由
Route::prefix('websocket')->middleware(['jwt.auth'])->group(function () {
    Route::get('/config', [App\Http\Controllers\Api\WebSocketController::class, 'getConfig']);
    Route::post('/auth', [App\Http\Controllers\Api\WebSocketController::class, 'authenticate'])->withoutMiddleware('jwt.auth');
    Route::post('/system-message', [App\Http\Controllers\Api\WebSocketController::class, 'sendSystemMessage'])->middleware('throttle:admin');
    Route::post('/maintenance-notification', [App\Http\Controllers\Api\WebSocketController::class, 'sendMaintenanceNotification'])->middleware('throttle:admin');
    Route::post('/chat-message', [App\Http\Controllers\Api\WebSocketController::class, 'sendChatMessage']);
    Route::get('/stats', [App\Http\Controllers\Api\WebSocketController::class, 'getStats'])->middleware('throttle:admin');
    Route::post('/disconnect', [App\Http\Controllers\Api\WebSocketController::class, 'disconnectConnection'])->middleware('throttle:admin');
    Route::post('/broadcast-test', [App\Http\Controllers\Api\WebSocketController::class, 'broadcastTest'])->middleware('throttle:admin');
    Route::get('/online-users', [App\Http\Controllers\Api\WebSocketController::class, 'getOnlineUsers']);
    Route::post('/cleanup', [App\Http\Controllers\Api\WebSocketController::class, 'cleanupConnections'])->middleware('throttle:admin');
    Route::get('/message-history', [App\Http\Controllers\Api\WebSocketController::class, 'getMessageHistory']);
});

// 实时通信 API 路由
Route::prefix('realtime')->middleware(['jwt.auth'])->group(function () {
    Route::get('/notifications', [App\Http\Controllers\Api\RealtimeController::class, 'getNotifications']);
    Route::post('/notifications/read', [App\Http\Controllers\Api\RealtimeController::class, 'markNotificationsAsRead']);
    Route::delete('/notifications', [App\Http\Controllers\Api\RealtimeController::class, 'clearNotifications']);
    Route::get('/chat/history', [App\Http\Controllers\Api\RealtimeController::class, 'getChatHistory']);
    Route::post('/chat/read', [App\Http\Controllers\Api\RealtimeController::class, 'markChatAsRead']);
    Route::get('/stats', [App\Http\Controllers\Api\RealtimeController::class, 'getRealtimeStats']);
});

// 高级分析 API 路由
Route::prefix('analytics')->middleware(['jwt.auth', 'throttle:60,1'])->group(function () {
    
    // 业务分析
    Route::get('/sales-trends', [App\Http\Controllers\Api\AnalyticsController::class, 'salesTrends']);
    Route::get('/customer-value', [App\Http\Controllers\Api\AnalyticsController::class, 'customerValue']);
    Route::get('/inventory-optimization', [App\Http\Controllers\Api\AnalyticsController::class, 'inventoryOptimization']);
    Route::get('/financials', [App\Http\Controllers\Api\AnalyticsController::class, 'financials']);
    Route::get('/product-performance', [App\Http\Controllers\Api\AnalyticsController::class, 'productPerformance']);
    Route::get('/market-analysis', [App\Http\Controllers\Api\AnalyticsController::class, 'marketAnalysis']);
    
    // 报表系统
    Route::post('/reports/generate', [App\Http\Controllers\Api\AnalyticsController::class, 'generateReport']);
    Route::get('/reports/templates', [App\Http\Controllers\Api\AnalyticsController::class, 'reportTemplates']);
    Route::post('/reports/export', [App\Http\Controllers\Api\AnalyticsController::class, 'exportReport']);
    
    // 可视化仪表板
    Route::get('/dashboards/executive', [App\Http\Controllers\Api\AnalyticsController::class, 'executiveDashboard']);
    Route::get('/dashboards/sales', [App\Http\Controllers\Api\AnalyticsController::class, 'salesDashboard']);
    Route::get('/dashboards/customer', [App\Http\Controllers\Api\AnalyticsController::class, 'customerDashboard']);
    Route::get('/dashboards/inventory', [App\Http\Controllers\Api\AnalyticsController::class, 'inventoryDashboard']);
    Route::get('/realtime-stream', [App\Http\Controllers\Api\AnalyticsController::class, 'realTimeStream']);
    Route::post('/charts/interactive', [App\Http\Controllers\Api\AnalyticsController::class, 'interactiveChart']);
    Route::post('/charts/heatmap', [App\Http\Controllers\Api\AnalyticsController::class, 'heatmap']);
    Route::post('/charts/funnel', [App\Http\Controllers\Api\AnalyticsController::class, 'funnelChart']);
    Route::post('/charts/geo-map', [App\Http\Controllers\Api\AnalyticsController::class, 'geoMap']);
    
    // 预测分析
    Route::post('/forecast/time-series', [App\Http\Controllers\Api\AnalyticsController::class, 'timeSeriesForecast']);
    Route::post('/forecast/sales', [App\Http\Controllers\Api\AnalyticsController::class, 'salesForecast']);
    Route::post('/predict/churn', [App\Http\Controllers\Api\AnalyticsController::class, 'churnPrediction']);
    Route::post('/forecast/inventory-demand', [App\Http\Controllers\Api\AnalyticsController::class, 'inventoryDemandForecast']);
    Route::post('/predict/market-trends', [App\Http\Controllers\Api\AnalyticsController::class, 'marketTrendPrediction']);
    Route::post('/predict/price-elasticity', [App\Http\Controllers\Api\AnalyticsController::class, 'priceElasticityPrediction']);
    
    // ETL 和数据管理
    Route::post('/etl/run', [App\Http\Controllers\Api\AnalyticsController::class, 'runETL'])->middleware('throttle:admin');
    Route::get('/etl/status', [App\Http\Controllers\Api\AnalyticsController::class, 'etlStatus']);
    Route::get('/overview', [App\Http\Controllers\Api\AnalyticsController::class, 'analyticsOverview']);
    Route::get('/system-health', [App\Http\Controllers\Api\AnalyticsController::class, 'systemHealth']);
});