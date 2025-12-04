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
     * 优化：增强缓存策略，添加查询性能监控
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        try {
            // 构建筛选条件
            $filters = [];
            
            if ($request->has('search')) {
                $filters['search'] = trim($request->get('search'));
            }
            
            if ($request->has('min_price')) {
                $filters['min_price'] = (float) $request->get('min_price');
            }
            
            if ($request->has('max_price')) {
                $filters['max_price'] = (float) $request->get('max_price');
            }
            
            if ($request->has('supplier')) {
                $filters['supplier'] = trim($request->get('supplier'));
            }
            
            if ($request->has('category')) {
                $filters['category'] = trim($request->get('category'));
            }

            // 分页参数
            $page = max(1, (int) $request->get('page', 1));
            $perPage = min(100, max(1, (int) $request->get('per_page', $request->get('limit', 20))));

            // 使用优化的缓存获取产品列表
            $products = CacheService::getProductsOptimized($filters, $page, $perPage);

            // 性能监控
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $response = ApiResponseService::paginated(
                $products->items(),
                $products,
                '产品列表获取成功'
            );
            
            // 添加性能信息到响应头
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
            $response->headers->set('X-Cache-Hit', $products->cacheHit ?? 'false');
            
            return $response;

        } catch (\Exception $e) {
            // 记录错误日志
            \Log::error('Product list fetch error: ' . $e->getMessage(), [
                'filters' => $filters ?? [],
                'page' => $page ?? 1,
                'per_page' => $perPage ?? 20,
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            
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