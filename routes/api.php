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