<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * 测试基础连接
     */
    public function index()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'API 运行正常',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * 测试数据库连接
     */
    public function database()
    {
        try {
            $userCount = User::count();
            $productCount = Product::count();
            
            return response()->json([
                'status' => 'ok',
                'database' => 'connected',
                'users' => $userCount,
                'products' => $productCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 测试产品查询
     */
    public function products()
    {
        $products = Product::limit(3)->get(['sku', 'name', 'price', 'currency']);
        
        return response()->json([
            'status' => 'ok',
            'count' => $products->count(),
            'data' => $products,
        ]);
    }

    /**
     * 简单的登录测试（不使用JWT）
     */
    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        
        $user = User::where('username', $username)->first();
        
        if ($user && \Hash::check($password, $user->password)) {
            // 生成简单的API token（仅用于测试）
            $token = md5($user->id . time());
            
            return response()->json([
                'status' => 'ok',
                'message' => '登录成功',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                ],
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => '用户名或密码错误',
        ], 401);
    }

    /**
     * 模拟订单列表数据
     */
    public function orders(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $status = $request->get('status');
        
        // 模拟订单数据
        $allOrders = [
            [
                'order_id' => 'YO-20251202-00001',
                'created_at' => '2025-12-02T12:00:00Z',
                'total_amount' => 2781.00,
                'currency' => 'CNY',
                'status' => 'PROCESSING',
                'status_message' => '订单已确认，正在准备发货',
                'shipping_address' => '日本东京都港区测试地址1-2-3',
                'domestic_tracking_number' => null,
                'international_tracking_number' => null,
                'total_fee_cny' => 2781.00,
                'total_fee_jpy' => 56900.50,
                'items' => [
                    [
                        'sku' => 'ALIBABA_SKU_A123',
                        'name' => '日本客户专用 办公椅',
                        'quantity' => 2,
                        'unit_price' => 1250.50
                    ]
                ]
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
     * 模拟单个订单详情
     */
    public function orderDetail($orderId)
    {
        // 模拟订单详情数据
        $order = [
            'order_id' => $orderId,
            'created_at' => '2025-12-02T12:00:00Z',
            'total_amount' => 2781.00,
            'currency' => 'CNY',
            'status' => 'PROCESSING',
            'status_message' => '订单已确认，正在准备发货',
            'shipping_address' => '日本东京都港区测试地址1-2-3',
            'domestic_tracking_number' => null,
            'international_tracking_number' => null,
            'total_fee_cny' => 2781.00,
            'total_fee_jpy' => 56900.50,
            'items' => [
                [
                    'sku' => 'ALIBABA_SKU_A123',
                    'name' => '日本客户专用 办公椅',
                    'quantity' => 2,
                    'unit_price' => 1250.50
                ]
            ]
        ];

        return response()->json($order);
    }

    /**
     * 模拟创建订单
     */
    public function createOrder(Request $request)
    {
        $items = $request->get('items', []);
        $shippingAddress = $request->get('shipping_address');

        if (empty($items) || !$shippingAddress) {
            return response()->json([
                'error' => 'Invalid request',
                'message' => '订单项目和配送地址不能为空'
            ], 400);
        }

        // 生成订单ID
        $orderId = 'YO-' . date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        // 计算总金额（模拟）
        $totalAmountCNY = 2781.00;
        $totalAmountJPY = $totalAmountCNY * 20.5;

        return response()->json([
            'order_id' => $orderId,
            'message' => '订单已成功提交到阿里巴巴平台。',
            'total_amount_cny' => $totalAmountCNY,
            'total_amount_jpy' => $totalAmountJPY,
        ], 201);
    }
}