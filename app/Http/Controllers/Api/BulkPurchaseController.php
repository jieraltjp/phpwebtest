<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ApiResponseService;
use App\Services\CacheService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkPurchaseController extends Controller
{
    /**
     * 创建批量采购订单
     */
    public function store(Request $request)
    {
        // 清理输入数据
        $sanitizedData = ValidationService::sanitizeInput($request->all());
        
        // 验证数据
        $validation = ValidationService::validateBulkPurchase($sanitizedData);
        
        if (!$validation['valid']) {
            return ApiResponseService::validationError($validation['errors']);
        }

        try {
            DB::beginTransaction();

            $orderItems = [];
            $totalAmount = 0;
            $currency = 'CNY';

            // 验证并处理每个采购项
            foreach ($sanitizedData['items'] as $index => $item) {
                $product = Product::where('sku', $item['sku'])
                    ->where('active', true)
                    ->first();

                if (!$product) {
                    DB::rollBack();
                    return ApiResponseService::error(
                        "第 " . ($index + 1) . " 个产品不存在: {$item['sku']}",
                        null,
                        400
                    );
                }

                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return ApiResponseService::error(
                        "产品 {$product->name} 库存不足，当前库存: {$product->stock}",
                        null,
                        400
                    );
                }

                $itemTotal = $product->price * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                    'currency' => $currency,
                    'specs' => $product->specs,
                ];

                // 扣减库存
                $product->stock -= $item['quantity'];
                $product->save();
            }

            // 创建订单
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'currency' => $currency,
                'status' => 'pending',
                'shipping_address' => $sanitizedData['shipping_address'],
                'notes' => $sanitizedData['notes'] ?? null,
                'contact_info' => $sanitizedData['contact_info'],
                'order_type' => 'bulk_purchase', // 标记为批量采购
            ]);

            // 创建订单项
            foreach ($orderItems as $orderItem) {
                $orderItem['order_id'] = $order->id;
                OrderItem::create($orderItem);
            }

            DB::commit();

            // 清除相关缓存
            CacheService::clearProductCache();
            CacheService::clearOrderCache();

            // 返回订单详情
            $orderData = [
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => $order->status,
                'items' => $orderItems,
                'created_at' => $order->created_at,
            ];

            return ApiResponseService::success($orderData, '批量采购订单创建成功', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponseService::serverError('创建批量采购订单失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取批量采购报价
     */
    public function getQuote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1|max:50',
            'items.*.sku' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1|max:10000',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        try {
            $quoteItems = [];
            $totalAmount = 0;
            $totalSavings = 0;
            $currency = 'CNY';

            foreach ($request->items as $index => $item) {
                $product = Product::where('sku', $item['sku'])
                    ->where('active', true)
                    ->first();

                if (!$product) {
                    return ApiResponseService::error(
                        "第 " . ($index + 1) . " 个产品不存在: {$item['sku']}",
                        null,
                        400
                    );
                }

                $unitPrice = $product->price;
                $itemTotal = $unitPrice * $item['quantity'];
                
                // 批量采购折扣逻辑
                $discountRate = $this->getDiscountRate($item['quantity']);
                $discountedPrice = $unitPrice * (1 - $discountRate);
                $discountedTotal = $discountedPrice * $item['quantity'];
                $savings = $itemTotal - $discountedTotal;

                $totalAmount += $discountedTotal;
                $totalSavings += $savings;

                $quoteItems[] = [
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'original_unit_price' => $unitPrice,
                    'discounted_unit_price' => $discountedPrice,
                    'original_total' => $itemTotal,
                    'discounted_total' => $discountedTotal,
                    'savings' => $savings,
                    'discount_rate' => $discountRate * 100,
                    'currency' => $currency,
                    'stock_available' => $product->stock,
                ];
            }

            $quoteData = [
                'items' => $quoteItems,
                'total_original_amount' => $totalAmount + $totalSavings,
                'total_discounted_amount' => $totalAmount,
                'total_savings' => $totalSavings,
                'savings_percentage' => $totalSavings > 0 ? round(($totalSavings / ($totalAmount + $totalSavings)) * 100, 2) : 0,
                'currency' => $currency,
                'valid_until' => now()->addHours(24)->toISOString(),
            ];

            return ApiResponseService::success($quoteData, '批量采购报价获取成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取批量采购报价失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取批量采购历史
     */
    public function history(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);

            $orders = Order::where('user_id', auth()->id())
                ->where('order_type', 'bulk_purchase')
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $orderData = $orders->getCollection()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'currency' => $order->currency,
                    'status' => $order->status,
                    'item_count' => $order->items->count(),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            });

            return ApiResponseService::paginated($orderData, $orders, '批量采购历史获取成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取批量采购历史失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取批量采购统计
     */
    public function statistics()
    {
        try {
            $userId = auth()->id();

            $stats = [
                'total_orders' => Order::where('user_id', $userId)
                    ->where('order_type', 'bulk_purchase')
                    ->count(),
                'total_amount' => Order::where('user_id', $userId)
                    ->where('order_type', 'bulk_purchase')
                    ->sum('total_amount'),
                'total_items' => OrderItem::whereHas('order', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('order_type', 'bulk_purchase');
                })->sum('quantity'),
                'total_savings' => 0, // TODO: 计算总节省金额
                'average_order_value' => Order::where('user_id', $userId)
                    ->where('order_type', 'bulk_purchase')
                    ->avg('total_amount'),
                'status_breakdown' => Order::where('user_id', $userId)
                    ->where('order_type', 'bulk_purchase')
                    ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as amount')
                    ->groupBy('status')
                    ->get(),
                'monthly_trend' => Order::where('user_id', $userId)
                    ->where('order_type', 'bulk_purchase')
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as orders, SUM(total_amount) as amount')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
            ];

            return ApiResponseService::success($stats, '批量采购统计获取成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取批量采购统计失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成订单编号
     */
    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = Order::whereDate('created_at', today())->count() + 1;
        
        return "BULK{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 获取折扣率
     */
    private function getDiscountRate(int $quantity): float
    {
        if ($quantity >= 1000) {
            return 0.15; // 15% 折扣
        } elseif ($quantity >= 500) {
            return 0.10; // 10% 折扣
        } elseif ($quantity >= 100) {
            return 0.05; // 5% 折扣
        } elseif ($quantity >= 50) {
            return 0.02; // 2% 折扣
        }
        
        return 0; // 无折扣
    }
}