<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;

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

// API 版本前缀
Route::prefix('v1')->group(function () {
    
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