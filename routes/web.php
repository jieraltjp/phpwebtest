<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;

// 主页路由 - 指向新的首页
Route::get('/', function () {
    return view('home');
});

// 仪表板路由
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

// 产品管理页面
Route::get('/products', function() {
    return view('products');
});

// 订单管理页面
Route::get('/orders', function() {
    return view('orders');
});

// 管理员路由
Route::prefix('admin')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard']);
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard']);
    Route::get('/stats', [App\Http\Controllers\Admin\AdminController::class, 'getStats']);
    Route::get('/users', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getUsers']);
    Route::get('/orders', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getOrders']);
    Route::get('/system-status', [App\Http\Controllers\Admin\AdminController::class, 'getSystemStatus']);
    Route::get('/activities', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getRecentActivities']);
    Route::post('/users/{id}/approve', [App\Http\Controllers\Admin\AdminController::class, 'approveUser']);
    Route::post('/orders/{id}/status', [App\Http\Controllers\Admin\Admin\AdminController::class, 'updateOrderStatus']);
});

// Swagger 文档路由
Route::get('/docs', [App\Http\Controllers\SwaggerController::class, 'index']);
Route::get('/api/openapi', [App\Http\Controllers\SwaggerController::class, 'openApi']);

// 欢迎页面（备用）
Route::get('/welcome', function () {
    return view('welcome');
});

// API 路由
Route::prefix('api')->group(function () {
    Route::get('/health', function() {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    });

    // 认证相关路由
    Route::post('/auth/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/auth/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/auth/me', [App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::post('/auth/refresh', [App\Http\Controllers\Api\AuthController::class, 'refresh']);

    // 产品相关路由
    Route::get('/products', [App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::get('/products/{id}', [App\Http\Controllers\Api\ProductController::class, 'show']);

    // 订单相关路由
    Route::post('/orders', [App\Http\Controllers\Api\OrderController::class, 'store']);
    Route::get('/orders', [App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::get('/orders/{id}', [App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::get('/orders/{id}/tracking-link', [App\Http\Controllers\Api\OrderController::class, 'trackingLink']);

    // 测试路由
    Route::get('/test', [App\Http\Controllers\TestController::class, 'index']);
    Route::get('/test/database', [App\Http\Controllers\TestController::class, 'database']);
    Route::get('/test/products', [App\Http\Controllers\TestController::class, 'products']);
    Route::post('/test/login', [App\Http\Controllers\TestController::class, 'login']);

    // 管理员 API 路由
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getStats']);
        Route::get('/users', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getUsers']);
        Route::get('/orders', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getOrders']);
        Route::get('/system-status', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getSystemStatus']);
        Route::get('/activities', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getRecentActivities']);
        Route::post('/users/{id}/approve', [App\Http\Controllers\Admin\Admin\AdminController::class, 'approveUser']);
        Route::post('/orders/{id}/status', [App\Http\Controllers\Admin\Admin\AdminController::class, 'updateOrderStatus']);
    });
});

// 欢迎页面（备用）
Route::get('/welcome', function () {
    return view('welcome');
});

// 产品管理页面
Route::get('/products', function() {
    return view('products');
});

// 订单管理页面
Route::get('/orders', function() {
    return view('orders');
});

// Swagger 文档路由
Route::get('/docs', [App\Http\Controllers\SwaggerController::class, 'index']);
Route::get('/api/openapi', [App\Http\Controllers\SwaggerController::class, 'openApi']);

// 管理员路由
Route::prefix('admin')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard']);
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard']);
    Route::get('/stats', [App\Http\Controllers\Admin\AdminController::class, 'getStats']);
    Route::get('/users', [App\Http\Controllers\Admin\Admin\AdminController::class, 'getUsers']);
    Route::get('/orders', [App\Http\Controllers\Admin\AdminController::class, 'getOrders']);
    Route::get('/system-status', [App\Http\Controllers\Admin\AdminController::class, 'getSystemStatus']);
    Route::get('/activities', [App\Http\Controllers\Admin\AdminController::class, 'getRecentActivities']);
    Route::post('/users/{id}/approve', [App\Http\Controllers\Admin\AdminController::class, 'approveUser']);
    Route::post('/orders/{id}/status', [App\Http\Controllers\Admin\AdminController::class, 'updateOrderStatus']);
});

// 欢迎页面（备用）
Route::get('/welcome', function () {
    return view('welcome');
});
