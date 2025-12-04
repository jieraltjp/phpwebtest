<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ProductController as BaseProductController;
use App\Models\Product;
use App\Services\ApiResponseService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Products V1",
 *     description="产品管理接口 - 版本 1.0"
 * )
 */
class ProductController extends BaseProductController
{
    /**
     * 获取产品列表 (V1)
     * 
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Products V1"},
     *     summary="获取产品列表 - V1",
     *     description="获取产品列表，支持搜索和筛选 (版本 1.0)",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="搜索关键词",
     *         required=false,
     *         @OA\Schema(type="string", example="办公椅")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="最低价格",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=100)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="最高价格",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=1000)
     *     ),
     *     @OA\Parameter(
     *         name="supplier",
     *         in="query",
     *         description="供应商筛选",
     *         required=false,
     *         @OA\Schema(type="string", example="阿里巴巴")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="分类筛选",
     *         required=false,
     *         @OA\Schema(type="string", example="办公用品")
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
     *             @OA\Property(property="message", type="string", example="产品列表获取成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="total_pages", type="integer", example=5),
     *                     @OA\Property(property="total_items", type="integer", example=100)
     *                 )
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
            $data['data']['features'] = [
                'basic_search' => true,
                'price_filtering' => true,
                'supplier_filtering' => true,
                'category_filtering' => true,
                'pagination' => true,
                'caching' => true
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 获取产品详情 (V1)
     * 
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     tags={"Products V1"},
     *     summary="获取产品详情 - V1",
     *     description="根据ID或SKU获取产品详细信息 (版本 1.0)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="产品ID或SKU",
     *         required=true,
     *         @OA\Schema(type="string", example="ALIBABA_SKU_A123")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="产品详情获取成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="sku", type="string", example="ALIBABA_SKU_A123"),
     *                 @OA\Property(property="name", type="string", example="日本客户专用 办公椅"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number", example=1250.50),
     *                 @OA\Property(property="currency", type="string", example="CNY"),
     *                 @OA\Property(property="image_url", type="string"),
     *                 @OA\Property(property="supplier_shop", type="string"),
     *                 @OA\Property(property="specs", type="object"),
     *                 @OA\Property(property="stock", type="integer", example=100),
     *                 @OA\Property(property="active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="产品不存在",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="产品不存在")
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
            $data['data']['version_info'] = [
                'data_format' => 'v1_standard',
                'fields_compatibility' => 'full',
                'deprecated_fields' => [],
                'new_fields_available_in_v2' => [
                    'enhanced_specs',
                    'supplier_rating',
                    'shipping_info',
                    'bulk_pricing'
                ]
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }
}