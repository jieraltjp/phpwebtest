<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * 创建新订单
     */
    public function store(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::guard('api')->user();
            $items = $request->get('items');
            $shippingAddress = $request->get('shipping_address');

            // 检查库存并计算总金额
            $totalAmountCNY = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = Product::where('sku', $item['sku'])->first();
                
                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Product not found',
                        'message' => "产品 SKU {$item['sku']} 不存在"
                    ], 400);
                }

                if (!$product->hasStock($item['quantity'])) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Insufficient stock',
                        'message' => "产品 {$product->name} 库存不足"
                    ], 400);
                }

                $itemTotal = $product->price * $item['quantity'];
                $totalAmountCNY += $itemTotal;

                $orderItems[] = [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                    'currency' => $product->currency,
                ];
            }

            // 汇率转换 (CNY to JPY) - 简化处理，实际应该从汇率API获取
            $exchangeRate = 20.5; // 1 CNY = 20.5 JPY
            $totalAmountJPY = $totalAmountCNY * $exchangeRate;

            // 创建订单
            $order = Order::create([
                'order_id' => Order::generateOrderId(),
                'user_id' => $user->id,
                'status' => Order::STATUS_PENDING,
                'status_message' => '订单已成功提交到阿里巴巴平台。',
                'total_fee_cny' => $totalAmountCNY,
                'total_fee_jpy' => $totalAmountJPY,
                'shipping_address' => $shippingAddress,
            ]);

            // 创建订单项并减少库存
            foreach ($orderItems as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
                
                $product = Product::where('sku', $item['sku'])->first();
                $product->decreaseStock($item['quantity']);
            }

            DB::commit();

            return response()->json([
                'order_id' => $order->order_id,
                'message' => '订单已成功提交到阿里巴巴平台。',
                'total_amount_cny' => $totalAmountCNY,
                'total_amount_jpy' => $totalAmountJPY,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Order creation failed',
                'message' => '订单创建失败，请稍后重试'
            ], 500);
        }
    }

    /**
     * 获取用户订单列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $query = Order::where('user_id', $user->id);

        // 按状态筛选
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $orderList = $orders->map(function ($order) {
            return [
                'order_id' => $order->order_id,
                'created_at' => $order->created_at->toISOString(),
                'total_amount' => $order->total_fee_cny,
                'currency' => 'CNY',
                'status' => $order->status,
                'status_message' => $order->status_message,
            ];
        });

        return response()->json([
            'data' => $orderList,
            'total' => $orderList->count(),
        ]);
    }

    /**
     * 获取订单详情
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $order = Order::where('order_id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json([
                'error' => 'Order not found',
                'message' => '订单不存在'
            ], 404);
        }

        $orderItems = $order->orderItems->map(function ($item) {
            return [
                'sku' => $item->sku,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        });

        return response()->json([
            'order_id' => $order->order_id,
            'created_at' => $order->created_at->toISOString(),
            'total_amount' => $order->total_fee_cny,
            'currency' => 'CNY',
            'status' => $order->status,
            'status_message' => $order->status_message,
            'items' => $orderItems,
            'shipping_address' => $order->shipping_address,
            'domestic_tracking_number' => $order->domestic_tracking_number,
            'international_tracking_number' => $order->international_tracking_number,
            'total_fee_cny' => $order->total_fee_cny,
            'total_fee_jpy' => $order->total_fee_jpy,
        ]);
    }

    /**
     * 获取订单物流追踪链接
     */
    public function trackingLink(string $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $order = Order::where('order_id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json([
                'error' => 'Order not found',
                'message' => '订单不存在'
            ], 404);
        }

        $shipment = $order->shipment;

        if (!$shipment) {
            return response()->json([
                'message' => '暂无物流信息',
                'tracking_url' => null,
                'logistics_company' => null,
            ]);
        }

        return response()->json([
            'tracking_url' => $shipment->tracking_url,
            'logistics_company' => $shipment->logistics_company,
            'domestic_tracking_number' => $shipment->domestic_tracking_number,
            'international_tracking_number' => $shipment->international_tracking_number,
        ]);
    }
}