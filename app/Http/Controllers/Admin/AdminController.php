<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * 显示管理员仪表板
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * 获取管理员统计数据
     */
    public function getStats()
    {
        // 模拟管理员统计数据
        return response()->json([
            'totalUsers' => 156,
            'totalOrders' => 1234,
            'totalRevenue' => 89456,
            'pendingShipments' => 23,
            'newUsers' => 12,
            'activeUsers' => 89,
            'conversionRate' => 68.5,
            'avgOrderValue' => 728.50,
        ]);
    }

    /**
     * 获取用户列表
     */
    public function getUsers(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $status = $request->get('status');

        // 模拟用户数据
        $allUsers = [
            [
                'id' => 1,
                'name' => '田中太郎',
                'email' => 'tanaka@example.com',
                'username' => 'tanaka_tarou',
                'status' => 'active',
                'company_name' => '田中商事',
                'created_at' => '2025-11-15T10:30:00Z',
                'last_login' => '2025-12-02T09:15:00Z',
                'total_orders' => 15,
                'total_spent' => 12500.00
            ],
            [
                'id' => 2,
                'name' => '佐藤花子',
                'email' => 'sato@example.com',
                'username' => 'sato_hanako',
                'status' => 'pending',
                'company_name' => '佐藤贸易',
                'created_at' => '2025-11-28T14:20:00Z',
                'last_login' => null,
                'total_orders' => 0,
                'total_spent' => 0.00
            ],
            [
                'id' => 3,
                'name' => '铃木一郎',
                'email' => 'suzuki@example.com',
                'username' => 'suzuki_ichiro',
                'status' => 'active',
                'company_name' => '铃木物产',
                'created_at' => '2025-10-20T08:45:00Z',
                'last_login' => '2025-12-01T16:30:00Z',
                'total_orders' => 8,
                'total_spent' => 6800.00
            ]
        ];

        // 状态筛选
        if ($status) {
            $allUsers = array_filter($allUsers, function($user) use ($status) {
                return $user['status'] === $status;
            });
        }

        $total = count($allUsers);
        $lastPage = max(1, ceil($total / $limit));
        $offset = ($page - 1) * $limit;
        $users = array_slice($allUsers, $offset, $limit);

        return response()->json([
            'data' => $users,
            'total' => $total,
            'current_page' => $page,
            'last_page' => $lastPage,
        ]);
    }

    /**
     * 获取订单列表（管理员视图）
     */
    public function getOrders(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $status = $request->get('status');

        // 模拟订单数据（管理员视图）
        $allOrders = [
            [
                'order_id' => 'YO-20251202-00001',
                'user_name' => '田中太郎',
                'user_email' => 'tanaka@example.com',
                'created_at' => '2025-12-02T12:00:00Z',
                'total_amount' => 2781.00,
                'currency' => 'CNY',
                'status' => 'PROCESSING',
                'status_message' => '订单已确认，正在准备发货',
                'shipping_address' => '日本东京都港区测试地址1-2-3',
                'priority' => 'normal',
                'payment_status' => 'paid',
                'items_count' => 2
            ],
            [
                'order_id' => 'YO-20251201-00002',
                'user_name' => '铃木一郎',
                'user_email' => 'suzuki@example.com',
                'created_at' => '2025-12-01T15:30:00Z',
                'total_amount' => 1560.50,
                'currency' => 'CNY',
                'status' => 'SHIPPED',
                'status_message' => '订单已发货，正在运输途中',
                'shipping_address' => '日本大阪府中央区测试地址3-4-5',
                'priority' => 'high',
                'payment_status' => 'paid',
                'items_count' => 1
            ],
            [
                'order_id' => 'YO-20251130-00003',
                'user_name' => '佐藤花子',
                'user_email' => 'sato@example.com',
                'created_at' => '2025-11-30T10:15:00Z',
                'total_amount' => 4250.00,
                'currency' => 'CNY',
                'status' => 'PENDING',
                'status_message' => '等待用户确认订单',
                'shipping_address' => '日本京都府京都市测试地址6-7-8',
                'priority' => 'low',
                'payment_status' => 'pending',
                'items_count' => 3
            ]
        ];

        // 状态筛选
        if ($status) {
            $allOrders = array_filter($allOrders, function($order) use ($status) {
                return $order['status'] === $status;
            });
        }

        $total = count($allOrders);
        $lastPage = max(1, ceil($total / $limit));
        $offset = ($page - 1) * $limit;
        $orders = array_slice($allOrders, $offset, $limit);

        return response()->json([
            'data' => $orders,
            'total' => $total,
            'current_page' => $page,
            'last_page' => $lastPage,
        ]);
    }

    /**
     * 获取系统状态
     */
    public function getSystemStatus()
    {
        return response()->json([
            'api_service' => 'healthy',
            'database' => 'healthy',
            'cache' => 'healthy',
            'queue' => 'delayed',
            'storage' => [
                'used' => 68,
                'total' => 100,
                'status' => 'normal'
            ],
            'memory' => [
                'used' => 45,
                'total' => 100,
                'status' => 'normal'
            ],
            'last_updated' => now()->toISOString()
        ]);
    }

    /**
     * 获取最新活动
     */
    public function getRecentActivities()
    {
        // 模拟最新活动数据
        return response()->json([
            [
                'id' => 1,
                'timestamp' => '2025-12-02T12:30:00Z',
                'user_name' => '用户001',
                'action' => '创建订单',
                'target' => 'YO-20251202-00001',
                'status' => 'success',
                'ip_address' => '192.168.1.100'
            ],
            [
                'id' => 2,
                'timestamp' => '2025-12-02T12:15:00Z',
                'user_name' => '用户002',
                'action' => '注册账户',
                'target' => '新用户注册',
                'status' => 'pending',
                'ip_address' => '192.168.1.101'
            ],
            [
                'id' => 3,
                'timestamp' => '2025-12-02T11:45:00Z',
                'user_name' => '用户003',
                'action' => '订单发货',
                'target' => 'YO-20251201-00002',
                'status' => 'shipped',
                'ip_address' => '192.168.1.102'
            ]
        ]);
    }

    /**
     * 审核用户
     */
    public function approveUser(Request $request, $userId)
    {
        // 模拟用户审核逻辑
        return response()->json([
            'success' => true,
            'message' => '用户审核成功',
            'user_id' => $userId,
            'new_status' => 'active'
        ]);
    }

    /**
     * 更新订单状态
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $newStatus = $request->get('status');
        
        // 模拟订单状态更新逻辑
        return response()->json([
            'success' => true,
            'message' => '订单状态更新成功',
            'order_id' => $orderId,
            'new_status' => $newStatus
        ]);
    }
}