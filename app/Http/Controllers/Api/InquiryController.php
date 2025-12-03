<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\ApiResponseService;

class InquiryController extends Controller
{
    /**
     * 创建新的询价
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_sku' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'message' => 'nullable|string|max:1000',
            'contact_info' => 'required|array',
            'contact_info.email' => 'required|email',
            'contact_info.phone' => 'nullable|string',
            'contact_info.company' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        try {
            // 模拟创建询价记录
            $inquiry = [
                'id' => uniqid('INQ_'),
                'product_sku' => $request->product_sku,
                'quantity' => $request->quantity,
                'message' => $request->message,
                'contact_info' => $request->contact_info,
                'status' => 'pending',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            return ApiResponseService::success($inquiry, '询价提交成功', 201);

        } catch (\Exception $e) {
            return ApiResponseService::serverError('询价提交失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取询价列表
     */
    public function index(Request $request)
    {
        try {
            // 模拟询价数据
            $inquiries = [
                [
                    'id' => 'INQ_001',
                    'product_sku' => 'ALIBABA_SKU_A123',
                    'product_name' => '日本客户专用 办公椅',
                    'quantity' => 100,
                    'status' => 'pending',
                    'created_at' => now()->subDays(1)->toISOString(),
                ],
                [
                    'id' => 'INQ_002',
                    'product_sku' => 'ALIBABA_SKU_B456',
                    'product_name' => '无线蓝牙键盘',
                    'quantity' => 50,
                    'status' => 'quoted',
                    'created_at' => now()->subDays(2)->toISOString(),
                ]
            ];

            return ApiResponseService::success($inquiries, '询价列表获取成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取询价列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取询价详情
     */
    public function show($id)
    {
        try {
            // 模拟询价详情数据
            $inquiry = [
                'id' => $id,
                'product_sku' => 'ALIBABA_SKU_A123',
                'product_name' => '日本客户专用 办公椅',
                'product_description' => '人体工学设计，适合长时间办公使用，支持多角度调节',
                'quantity' => 100,
                'unit_price' => 1250.50,
                'total_price' => 125050.00,
                'currency' => 'CNY',
                'message' => '需要定制颜色，希望了解批量采购价格',
                'contact_info' => [
                    'email' => 'customer@example.com',
                    'phone' => '+81-3-1234-5678',
                    'company' => '示例株式会社',
                    'contact_person' => '田中太郎'
                ],
                'status' => 'pending',
                'quotes' => [
                    [
                        'id' => 'QUOTE_001',
                        'price' => 1180.00,
                        'total_price' => 118000.00,
                        'valid_until' => now()->addDays(7)->toISOString(),
                        'notes' => '批量采购优惠价格',
                        'created_at' => now()->subHours(6)->toISOString()
                    ]
                ],
                'created_at' => now()->subDays(1)->toISOString(),
                'updated_at' => now()->subHours(2)->toISOString()
            ];

            return ApiResponseService::success($inquiry, '询价详情获取成功');

        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取询价详情失败: ' . $e->getMessage());
        }
    }
}