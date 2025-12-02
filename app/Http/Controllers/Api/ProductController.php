<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * 获取产品列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->where('active', true);

        // 分页参数
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        // 搜索筛选
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('supplier_shop', 'like', "%{$search}%");
            });
        }

        // 价格筛选
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        // 供应商筛选
        if ($request->has('supplier')) {
            $query->where('supplier_shop', 'like', "%{$request->get('supplier')}%");
        }

        $products = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => $products->items(),
            'total' => $products->total(),
            'page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'last_page' => $products->lastPage(),
        ]);
    }

    /**
     * 获取产品详情
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::where('sku', $id)->orWhere('id', $id)->first();

        if (!$product) {
            return response()->json([
                'error' => 'Product not found',
                'message' => '产品不存在'
            ], 404);
        }

        return response()->json([
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'currency' => $product->currency,
            'image_url' => $product->image_url,
            'supplier_shop' => $product->supplier_shop,
            'specs' => $product->specs,
            'stock' => $product->stock,
        ]);
    }
}