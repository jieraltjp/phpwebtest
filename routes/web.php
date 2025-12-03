<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Api\AuthController;
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

// 主要页面路由
Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', [DashboardController::class, 'index']);
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
Route::get('/docs', [SwaggerController::class, 'index']);
Route::get('/api/openapi', [SwaggerController::class, 'openApi']);

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
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // 产品相关路由
    Route::apiResource('products', ProductController::class);

    // 订单相关路由
    Route::apiResource('orders', OrderController::class);
    Route::get('/orders/{id}/tracking-link', [OrderController::class, 'trackingLink']);

    // 询价功能路由
    Route::post('/inquiries', [App\Http\Controllers\Api\InquiryController::class, 'store']);
    Route::get('/inquiries', [App\Http\Controllers\Api\InquiryController::class, 'index']);
    Route::get('/inquiries/{id}', [App\Http\Controllers\Api\InquiryController::class, 'show']);

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