<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ApiResponseService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * 获取产品列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // 构建筛选条件
            $filters = [];
            
            if ($request->has('search')) {
                $filters['search'] = $request->get('search');
            }
            
            if ($request->has('min_price')) {
                $filters['min_price'] = $request->get('min_price');
            }
            
            if ($request->has('max_price')) {
                $filters['max_price'] = $request->get('max_price');
            }
            
            if ($request->has('supplier')) {
                $filters['supplier'] = $request->get('supplier');
            }

            // 分页参数
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', $request->get('limit', 20));

            // 使用缓存获取产品列表
            $products = CacheService::getProducts($filters, $page, $perPage);

            return ApiResponseService::paginated(
                $products->items(),
                $products,
                '产品列表获取成功'
            );

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取产品列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取产品详情
     */
    public function show(string $id): JsonResponse
    {
        try {
            // 尝试按ID查找，如果失败则按SKU查找
            $product = null;
            
            if (is_numeric($id)) {
                $product = CacheService::getProduct((int)$id);
            }
            
            if (!$product) {
                $product = Product::where('sku', $id)->first();
                if ($product) {
                    CacheService::set("product:{$product->id}", $product, CacheService::LONG_TTL);
                }
            }

            if (!$product) {
                return ApiResponseService::notFound('产品不存在');
            }

            $productData = [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'currency' => $product->currency,
                'image_url' => $product->image_url,
                'supplier_shop' => $product->supplier_shop,
                'specs' => $product->specs,
                'stock' => $product->stock,
                'active' => $product->active,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];

            return ApiResponseService::success($productData, '产品详情获取成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取产品详情失败: ' . $e->getMessage());
        }
    }
}