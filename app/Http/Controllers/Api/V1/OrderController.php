<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\OrderController as BaseOrderController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Orders V1",
 *     description="订单管理接口 - 版本 1.0"
 * )
 */
class OrderController extends BaseOrderController
{
    /**
     * 创建新订单 (V1)
     * 
     * @OA\Post(
     *     path="/api/v1/orders",
     *     tags={"Orders V1"},
     *     summary="创建新订单 - V1",
     *     description="创建新的采购订单 (版本 1.0)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items","shipping_address"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 type="object",
     *                 required={"sku","quantity"},
     *                 @OA\Property(property="sku", type="string", example="ALIBABA_SKU_A123"),
     *                 @OA\Property(property="quantity", type="integer", example=2)
     *             )),
     *             @OA\Property(property="shipping_address", type="string", example="東京都千代田区丸の内1-1-1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="订单创建成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="订单已成功提交到阿里巴巴平台"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="order_id", type="string", example="ORD20251204001"),
     *                 @OA\Property(property="total_amount_cny", type="number", example=2501.00),
     *                 @OA\Property(property="total_amount_jpy", type="number", example=51270.50)
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $response = parent::store($request);
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 201) {
            $data = json_decode($response->getContent(), true);
            $data['api_version'] = 'v1';
            $data['data']['api_version'] = 'v1';
            $data['data']['order_features'] = [
                'currency_support' => ['CNY', 'JPY'],
                'stock_validation' => true,
                'real_time_pricing' => true,
                'order_tracking' => true
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 获取用户订单列表 (V1)
     * 
     * @OA\Get(
     *     path="/api/v1/orders",
     *     tags={"Orders V1"},
     *     summary="获取订单列表 - V1",
     *     description="获取当前用户的订单列表 (版本 1.0)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="订单状态筛选",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","processing","shipped","delivered","cancelled"}, example="pending")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="每页数量",
     *         required=false,
     *         @OA\Schema(type="integer", default=20, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="订单列表获取成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="pagination", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $response = parent::index($request);
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $data['data']['filter_options'] = [
                'status_filter' => true,
                'date_range_filter' => false,
                'price_range_filter' => false
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 获取订单详情 (V1)
     * 
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     tags={"Orders V1"},
     *     summary="获取订单详情 - V1",
     *     description="获取指定订单的详细信息 (版本 1.0)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="订单ID",
     *         required=true,
     *         @OA\Schema(type="string", example="ORD20251204001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="订单详情获取成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="order_id", type="string"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="total_amount", type="number"),
     *                 @OA\Property(property="currency", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="items", type="array"),
     *                 @OA\Property(property="shipping_address", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $response = parent::show($id);
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $data['data']['data_format'] = 'v1_standard';
            $data['data']['includes'] = [
                'order_items' => true,
                'product_details' => true,
                'tracking_info' => 'if_available'
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 获取订单物流追踪链接 (V1)
     * 
     * @OA\Get(
     *     path="/api/v1/orders/{id}/tracking-link",
     *     tags={"Orders V1"},
     *     summary="获取物流追踪链接 - V1",
     *     description="获取订单的物流追踪信息 (版本 1.0)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="订单ID",
     *         required=true,
     *         @OA\Schema(type="string", example="ORD20251204001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="物流信息获取成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="tracking_url", type="string", example="https://track.1688.com/logistics/detail.htm?logisticsId=123456"),
     *                 @OA\Property(property="logistics_company", type="string", example="顺丰速运"),
     *                 @OA\Property(property="domestic_tracking_number", type="string"),
     *                 @OA\Property(property="international_tracking_number", type="string"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function trackingLink(string $id): JsonResponse
    {
        $response = parent::trackingLink($id);
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $data['data']['tracking_features'] = [
                'domestic_tracking' => true,
                'international_tracking' => true,
                'real_time_updates' => true,
                'tracking_history' => false
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }
}