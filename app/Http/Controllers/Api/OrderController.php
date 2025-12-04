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
     * 优化：使用缓存和预加载避免 N+1 查询问题
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        
        // 构建缓存键
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status', 'all');
        $cacheKey = "user_orders:{$user->id}:{$status}:{$page}:{$perPage}";
        
        // 使用缓存获取订单列表
        $orders = \App\Services\CacheService::remember($cacheKey, \App\Services\CacheService::SHORT_TTL, function () use ($user, $request, $perPage) {
            $query = Order::where('user_id', $user->id)
                ->with(['items.product']) // 预加载订单项和产品信息，避免 N+1 查询
                ->with(['shipment']); // 预加载物流信息

            // 按状态筛选
            if ($request->has('status') && $request->get('status') !== 'all') {
                $query->where('status', $request->get('status'));
            }

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        });

        $orderList = $orders->getCollection()->map(function ($order) {
            return [
                'order_id' => $order->order_id,
                'created_at' => $order->created_at->toISOString(),
                'total_amount' => $order->total_fee_cny,
                'currency' => 'CNY',
                'status' => $order->status,
                'status_message' => $order->status_message,
                'items_count' => $order->items->count(), // 预加载后可直接访问
                'has_shipment' => $order->shipment !== null, // 预加载后可直接访问
            ];
        });

        return \App\Services\ApiResponseService::paginated(
            $orderList->toArray(),
            $orders,
            '订单列表获取成功'
        );
    }

    /**
     * 获取订单详情
     * 优化：使用预加载避免 N+1 查询问题，添加缓存支持
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        
        // 使用缓存获取订单详情
        $cacheKey = "order_detail:{$id}:{$user->id}";
        $order = \App\Services\CacheService::remember($cacheKey, \App\Services\CacheService::SHORT_TTL, function () use ($id, $user) {
            return Order::where('order_id', $id)
                ->where('user_id', $user->id)
                ->with(['items.product', 'shipment']) // 预加载所有关联数据，避免 N+1 查询
                ->first();
        });

        if (!$order) {
            return \App\Services\ApiResponseService::notFound('订单不存在');
        }

        // 预加载后可以直接访问关联数据，无需额外查询
        $orderItems = $order->items->map(function ($item) {
            return [
                'sku' => $item->sku,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'currency' => $item->currency,
                'product_image' => $item->product ? $item->product->image_url : null, // 预加载的产品信息
                'product_stock' => $item->product ? $item->product->stock : null,
            ];
        });

        $orderData = [
            'order_id' => $order->order_id,
            'created_at' => $order->created_at->toISOString(),
            'total_amount' => $order->total_fee_cny,
            'currency' => 'CNY',
            'status' => $order->status,
            'status_message' => $order->status_message,
            'items' => $orderItems,
            'shipping_address' => $order->shipping_address,
            'total_fee_cny' => $order->total_fee_cny,
            'total_fee_jpy' => $order->total_fee_jpy,
        ];

        // 添加物流信息（如果存在）
        if ($order->shipment) {
            $orderData['tracking'] = [
                'domestic_tracking_number' => $order->shipment->domestic_tracking_number,
                'international_tracking_number' => $order->shipment->international_tracking_number,
                'logistics_company' => $order->shipment->logistics_company,
                'tracking_url' => $order->shipment->tracking_url,
                'status' => $order->shipment->status,
                'updated_at' => $order->shipment->updated_at->toISOString(),
            ];
        }

        return \App\Services\ApiResponseService::success($orderData, '订单详情获取成功');
    }

    /**
     * 获取订单物流追踪链接
     * 优化：使用预加载避免 N+1 查询问题，添加缓存支持
     */
    public function trackingLink(string $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        
        // 使用缓存获取订单和物流信息
        $cacheKey = "order_tracking:{$id}:{$user->id}";
        $order = \App\Services\CacheService::remember($cacheKey, \App\Services\CacheService::SHORT_TTL, function () use ($id, $user) {
            return Order::where('order_id', $id)
                ->where('user_id', $user->id)
                ->with(['shipment']) // 预加载物流信息，避免 N+1 查询
                ->first();
        });

        if (!$order) {
            return \App\Services\ApiResponseService::notFound('订单不存在');
        }

        // 预加载后可以直接访问 shipment，无需额外查询
        if (!$order->shipment) {
            return \App\Services\ApiResponseService::success([
                'message' => '暂无物流信息',
                'tracking_url' => null,
                'logistics_company' => null,
                'status' => 'no_shipment'
            ], '物流信息查询完成');
        }

        return \App\Services\ApiResponseService::success([
            'tracking_url' => $order->shipment->tracking_url,
            'logistics_company' => $order->shipment->logistics_company,
            'domestic_tracking_number' => $order->shipment->domestic_tracking_number,
            'international_tracking_number' => $order->shipment->international_tracking_number,
            'status' => $order->shipment->status,
            'last_updated' => $order->shipment->updated_at->toISOString(),
        ], '物流信息获取成功');
    }
}