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