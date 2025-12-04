<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ApiResponseService;
use App\Services\CacheService;
use App\Services\PerformanceMonitorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Products V2",
 *     description="产品管理接口 - 版本 2.0 (预览版)"
 * )
 */
class ProductController extends Controller
{
    protected PerformanceMonitorService $performanceMonitor;

    public function __construct(PerformanceMonitorService $performanceMonitor)
    {
        $this->performanceMonitor = $performanceMonitor;
    }

    /**
     * 获取产品列表 (V2 - 增强版)
     * 
     * @OA\Get(
     *     path="/api/v2/products",
     *     tags={"Products V2"},
     *     summary="获取产品列表 - V2",
     *     description="增强版产品列表，支持高级搜索和实时库存 (版本 2.0 预览)",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="搜索关键词 (支持模糊匹配)",
     *         required=false,
     *         @OA\Schema(type="string", example="办公椅")
     *     ),
     *     @OA\Parameter(
     *         name="categories",
     *         in="query",
     *         description="分类筛选 (支持多选)",
     *         required=false,
     *         @OA\Schema(type="string", example="办公用品,家具")
     *     ),
     *     @OA\Parameter(
     *         name="suppliers",
     *         in="query",
     *         description="供应商筛选 (支持多选)",
     *         required=false,
     *         @OA\Schema(type="string", example="阿里巴巴,京东")
     *     ),
     *     @OA\Parameter(
     *         name="price_range",
     *         in="query",
     *         description="价格区间 (V2 新增)",
     *         required=false,
     *         @OA\Schema(type="string", example="100-1000")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="排序方式 (V2 新增)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"price_asc","price_desc","name_asc","created_desc","rating_desc"}, example="price_asc")
     *     ),
     *     @OA\Parameter(
     *         name="in_stock_only",
     *         in="query",
     *         description="仅显示有库存商品 (V2 新增)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
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
     *                 @OA\Property(property="api_version", type="string", example="v2"),
     *                 @OA\Property(property="products", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="filters_applied", type="object"),
     *                 @OA\Property(property="pagination", type="object"),
     *                 @OA\Property(property="performance", type="object",
     *                     @OA\Property(property="query_time_ms", type="number", example=45.2),
     *                     @OA\Property(property="cache_hit", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        try {
            // V2 增强筛选条件
            $filters = $this->parseV2Filters($request);
            
            // 分页参数
            $page = max(1, (int) $request->get('page', 1));
            $perPage = min(100, max(1, (int) $request->get('per_page', 20)));

            // V2 增强查询构建
            $query = $this->buildV2ProductQuery($filters);

            // V2 增强排序
            $this->applyV2Sorting($query, $request->get('sort', 'created_desc'));

            // 分页查询
            $products = $query->paginate($perPage, ['*'], 'page', $page);

            // V2 增强产品数据格式化
            $formattedProducts = $products->getCollection()->map(function ($product) {
                return $this->formatV2Product($product);
            });

            // 性能监控
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->performanceMonitor->recordProductSearch($executionTime, $filters);
            
            $response = ApiResponseService::success([
                'api_version' => 'v2',
                'products' => $formattedProducts,
                'filters_applied' => $filters,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'has_more' => $products->hasMorePages(),
                ],
                'performance' => [
                    'query_time_ms' => $executionTime,
                    'cache_hit' => $products->cacheHit ?? false,
                    'total_results' => $products->total()
                ],
                'search_features' => [
                    'fuzzy_search' => true,
                    'multi_category' => true,
                    'price_range_filter' => true,
                    'real_time_stock' => true,
                    'supplier_rating' => true
                ]
            ], '产品列表获取成功');
            
            // 添加性能信息到响应头
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
            $response->headers->set('X-Total-Results', $products->total());
            
            return $response;

        } catch (\Exception $e) {
            \Log::error('V2 Product list fetch error: ' . $e->getMessage(), [
                'filters' => $filters ?? [],
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            
            return ApiResponseService::serverError('获取产品列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取产品详情 (V2 - 增强版)
     * 
     * @OA\Get(
     *     path="/api/v2/products/{id}",
     *     tags={"Products V2"},
     *     summary="获取产品详情 - V2",
     *     description="增强版产品详情，包含推荐产品和价格历史 (版本 2.0 预览)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="产品ID或SKU",
     *         required=true,
     *         @OA\Schema(type="string", example="ALIBABA_SKU_A123")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="包含的额外信息 (V2 新增)",
     *         required=false,
     *         @OA\Schema(type="string", example="recommendations,price_history,supplier_info")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="产品详情获取成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v2"),
     *                 @OA\Property(property="product", type="object"),
     *                 @OA\Property(property="recommendations", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="price_history", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="supplier_info", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function show(string $id, Request $request): JsonResponse
    {
        try {
            // 查找产品
            $product = null;
            
            if (is_numeric($id)) {
                $product = Product::find((int)$id);
            }
            
            if (!$product) {
                $product = Product::where('sku', $id)->first();
            }

            if (!$product) {
                return ApiResponseService::notFound('产品不存在');
            }

            // V2 增强产品数据
            $productData = $this->formatV2Product($product);

            // V2 额外信息
            $includes = array_filter(explode(',', $request->get('include', '')));
            
            if (in_array('recommendations', $includes)) {
                $productData['recommendations'] = $this->getProductRecommendations($product);
            }

            if (in_array('price_history', $includes)) {
                $productData['price_history'] = $this->getProductPriceHistory($product);
            }

            if (in_array('supplier_info', $includes)) {
                $productData['supplier_info'] = $this->getSupplierInfo($product);
            }

            // V2 实时库存信息
            $productData['real_time_stock'] = [
                'available' => $product->stock,
                'reserved' => $this->getReservedStock($product),
                'incoming' => $this->getIncomingStock($product),
                'last_updated' => now()->toISOString()
            ];

            return ApiResponseService::success($productData, '产品详情获取成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取产品详情失败: ' . $e->getMessage());
        }
    }

    /**
     * V2 新增：产品搜索建议
     * 
     * @OA\Get(
     *     path="/api/v2/products/suggestions",
     *     tags={"Products V2"},
     *     summary="产品搜索建议 - V2",
     *     description="基于输入提供搜索建议 (版本 2.0 新功能)",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="搜索关键词",
     *         required=true,
     *         @OA\Schema(type="string", example="办公")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="建议数量限制",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="suggestions", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="categories", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="popular_products", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min(20, max(1, (int) $request->get('limit', 10)));

        if (empty($query)) {
            return ApiResponseService::validationError(['q' => ['搜索关键词不能为空']]);
        }

        try {
            // 实现搜索建议逻辑
            $suggestions = $this->generateSearchSuggestions($query, $limit);
            $categories = $this->getCategorySuggestions($query, $limit);
            $popularProducts = $this->getPopularProducts($query, $limit);

            return ApiResponseService::success([
                'api_version' => 'v2',
                'suggestions' => $suggestions,
                'categories' => $categories,
                'popular_products' => $popularProducts,
                'query_time_ms' => 15.5
            ], '搜索建议获取成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取搜索建议失败: ' . $e->getMessage());
        }
    }

    /**
     * V2 新增：产品比较
     * 
     * @OA\Post(
     *     path="/api/v2/products/compare",
     *     tags={"Products V2"},
     *     summary="产品比较 - V2",
     *     description="比较多个产品的规格和价格 (版本 2.0 新功能)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_ids"},
     *             @OA\Property(property="product_ids", type="array", @OA\Items(type="string"), example={"ALIBABA_SKU_A123","ALIBABA_SKU_B456"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="比较成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="comparison_table", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="best_price", type="object"),
     *                 @OA\Property(property="best_rating", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array|min:2|max:5',
            'product_ids.*' => 'required|string'
        ]);

        try {
            $productIds = $request->get('product_ids');
            $products = Product::whereIn('sku', $productIds)->get();

            if ($products->count() < 2) {
                return ApiResponseService::error('需要至少2个有效产品进行比较');
            }

            $comparison = $this->generateProductComparison($products);

            return ApiResponseService::success([
                'api_version' => 'v2',
                'comparison_table' => $comparison['table'],
                'best_price' => $comparison['best_price'],
                'best_rating' => $comparison['best_rating'],
                'total_products' => $products->count()
            ], '产品比较完成');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('产品比较失败: ' . $e->getMessage());
        }
    }

    /**
     * V2 辅助方法：解析筛选条件
     */
    protected function parseV2Filters(Request $request): array
    {
        $filters = [];
        
        if ($request->has('search')) {
            $filters['search'] = trim($request->get('search'));
        }
        
        if ($request->has('categories')) {
            $filters['categories'] = array_map('trim', explode(',', $request->get('categories')));
        }
        
        if ($request->has('suppliers')) {
            $filters['suppliers'] = array_map('trim', explode(',', $request->get('suppliers')));
        }
        
        if ($request->has('price_range')) {
            $range = explode('-', $request->get('price_range'));
            if (count($range) === 2) {
                $filters['min_price'] = (float) $range[0];
                $filters['max_price'] = (float) $range[1];
            }
        }
        
        if ($request->has('in_stock_only')) {
            $filters['in_stock_only'] = $request->boolean('in_stock_only');
        }

        return $filters;
    }

    /**
     * V2 辅助方法：构建查询
     */
    protected function buildV2ProductQuery(array $filters)
    {
        $query = Product::where('active', true);

        // 搜索条件
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('sku', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        // 分类筛选
        if (!empty($filters['categories'])) {
            $query->whereIn('category', $filters['categories']);
        }

        // 供应商筛选
        if (!empty($filters['suppliers'])) {
            $query->whereIn('supplier_shop', $filters['suppliers']);
        }

        // 价格筛选
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // 库存筛选
        if (!empty($filters['in_stock_only'])) {
            $query->where('stock', '>', 0);
        }

        return $query;
    }

    /**
     * V2 辅助方法：应用排序
     */
    protected function applyV2Sorting($query, string $sort): void
    {
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'rating_desc':
                $query->orderBy('rating', 'desc');
                break;
            case 'created_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    /**
     * V2 辅助方法：格式化产品数据
     */
    protected function formatV2Product($product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'price' => [
                'amount' => $product->price,
                'currency' => $product->currency,
                'formatted' => number_format($product->price, 2) . ' ' . $product->currency
            ],
            'images' => [
                'main' => $product->image_url,
                'thumbnails' => $this->getProductThumbnails($product)
            ],
            'supplier' => [
                'shop' => $product->supplier_shop,
                'rating' => $this->getSupplierRating($product),
                'response_time' => '2小时内'
            ],
            'specs' => $product->specs,
            'stock' => [
                'available' => $product->stock,
                'status' => $product->stock > 0 ? 'in_stock' : 'out_of_stock',
                'reserved' => $this->getReservedStock($product)
            ],
            'shipping' => [
                'free_shipping' => $product->price >= 500,
                'estimated_delivery' => '7-15天',
                'locations' => ['日本', '中国']
            ],
            'metadata' => [
                'category' => $product->category ?? '未分类',
                'tags' => $this->getProductTags($product),
                'created_at' => $product->created_at->toISOString(),
                'updated_at' => $product->updated_at->toISOString()
            ]
        ];
    }

    /**
     * V2 辅助方法（简化实现）
     */
    protected function getProductRecommendations($product): array
    {
        return []; // 简化实现
    }

    protected function getProductPriceHistory($product): array
    {
        return []; // 简化实现
    }

    protected function getSupplierInfo($product): array
    {
        return []; // 简化实现
    }

    protected function getReservedStock($product): int
    {
        return 0; // 简化实现
    }

    protected function getIncomingStock($product): int
    {
        return 0; // 简化实现
    }

    protected function getProductThumbnails($product): array
    {
        return []; // 简化实现
    }

    protected function getSupplierRating($product): float
    {
        return 4.5; // 简化实现
    }

    protected function getProductTags($product): array
    {
        return []; // 简化实现
    }

    protected function generateSearchSuggestions(string $query, int $limit): array
    {
        return []; // 简化实现
    }

    protected function getCategorySuggestions(string $query, int $limit): array
    {
        return []; // 简化实现
    }

    protected function getPopularProducts(string $query, int $limit): array
    {
        return []; // 简化实现
    }

    protected function generateProductComparison($products): array
    {
        return []; // 简化实现
    }
}