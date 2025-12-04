<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BanhoController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SwaggerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// 主要页面路由 - 原有和风首页
Route::get('/', function () {
    return view('home');
});

// 万方商事品牌页面
Route::get('/banho', function () {
    return view('banho-home');
});

// 网站选择页面
Route::get('/portal', function () {
    return view('portal');
});

Route::get('/banho/dashboard', function () {
    return view('banho-dashboard');
});

// 认证页面路由
Route::get('/auth', [AuthController::class, 'showAuthPage']);
Route::get('/login', [AuthController::class, 'showLoginPage']);
Route::get('/register', [AuthController::class, 'showRegisterPage']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/dashboard', [DashboardController::class, 'index']);

// 高级分析中心
Route::get('/analytics', function () {
    return view('analytics');
})->middleware('auth');

// 实时通信门户
Route::get('/realtime', function () {
    return view('realtime-portal');
})->middleware('auth');
Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
Route::get('/products', function() {
    return view('products');
});
Route::get('/orders', function() {
    return view('orders');
});

// 管理员路由组
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/stats', [AdminController::class, 'getStats']);
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/orders', [AdminController::class, 'getOrders']);
    Route::get('/system-status', [AdminController::class, 'getSystemStatus']);
    Route::get('/activities', [AdminController::class, 'getRecentActivities']);
    Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
    Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
});

// API文档路由
Route::get('/docs', function() {
    return view('swagger.index');
});
Route::get('/docs/interactive', function() {
    return view('swagger.interactive');
});
Route::get('/api/openapi', [SwaggerController::class, 'openApi']);

// 万方商事配置API路由
Route::prefix('api/banho')->group(function () {
    Route::get('/config', [BanhoController::class, 'config']);
    Route::get('/brand', [BanhoController::class, 'brand']);
    Route::get('/language', [BanhoController::class, 'language']);
    Route::get('/business', [BanhoController::class, 'business']);
    Route::get('/support', [BanhoController::class, 'support']);
    Route::post('/exchange-rate', [BanhoController::class, 'exchangeRate']);
    Route::post('/clear-cache', [BanhoController::class, 'clearCache']);
});

// 欢迎页面（备用）
Route::get('/welcome', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API路由使用api中间件，自动添加/api前缀
|
*/

// API健康检查
Route::prefix('api')->group(function () {
    Route::get('/health', function() {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    });

    // 认证相关路由
    Route::prefix('auth')->group(function () {
        Route::post('/login', [ApiAuthController::class, 'login']);
        Route::post('/register', [ApiAuthController::class, 'register']);
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::get('/me', [ApiAuthController::class, 'me']);
        Route::post('/refresh', [ApiAuthController::class, 'refresh']);
        Route::post('/check-username', [ApiAuthController::class, 'checkUsername']);
        Route::post('/check-email', [ApiAuthController::class, 'checkEmail']);
    });

    // 产品相关路由
    Route::apiResource('products', ProductController::class)->middleware('api.throttle:search');

    // 订单相关路由
    Route::apiResource('orders', OrderController::class)->middleware('api.throttle:order');
    Route::get('/orders/{id}/tracking-link', [OrderController::class, 'trackingLink'])->middleware('api.throttle:order');

    // 询价功能路由
    Route::apiResource('inquiries', App\Http\Controllers\Api\InquiryController::class)->middleware('api.throttle:inquiry');

    // 批量采购路由
    Route::prefix('bulk-purchase')->middleware('jwt.auth')->group(function () {
        Route::post('/orders', [App\Http\Controllers\Api\BulkPurchaseController::class, 'store'])->middleware('api.throttle:order');
        Route::get('/quote', [App\Http\Controllers\Api\BulkPurchaseController::class, 'getQuote'])->middleware('api.throttle:search');
        Route::get('/history', [App\Http\Controllers\Api\BulkPurchaseController::class, 'history'])->middleware('api.throttle:search');
        Route::get('/statistics', [App\Http\Controllers\Api\BulkPurchaseController::class, 'statistics'])->middleware('api.throttle:search');
    });

    // 管理员API路由
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'getStats']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/orders', [AdminController::class, 'getOrders']);
        Route::get('/system-status', [AdminController::class, 'getSystemStatus']);
        Route::get('/activities', [AdminController::class, 'getRecentActivities']);
        Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    });

    // 测试路由（仅开发环境）
    if (app()->environment('local', 'testing')) {
        Route::prefix('test')->group(function () {
            Route::get('/', [TestController::class, 'index']);
            Route::get('/database', [TestController::class, 'database']);
            Route::get('/products', [TestController::class, 'products']);
            Route::post('/login', [TestController::class, 'login']);
        });
    }
});