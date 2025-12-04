<?php

namespace App\Http\Controllers;

use App\Services\BanhoConfigService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class BanhoController extends Controller
{
    /**
     * 获取公司配置信息
     */
    public function config()
    {
        try {
            $config = BanhoConfigService::getFullConfig();
            return ApiResponseService::success($config, '配置信息获取成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('配置信息获取失败');
        }
    }
    
    /**
     * 获取品牌信息
     */
    public function brand()
    {
        try {
            $brand = BanhoConfigService::getBrandConfig();
            return ApiResponseService::success($brand, '品牌信息获取成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('品牌信息获取失败');
        }
    }
    
    /**
     * 获取多语言配置
     */
    public function language()
    {
        try {
            $language = BanhoConfigService::getLanguageConfig();
            return ApiResponseService::success($language, '语言配置获取成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('语言配置获取失败');
        }
    }
    
    /**
     * 获取业务配置
     */
    public function business()
    {
        try {
            $business = BanhoConfigService::getBusinessConfig();
            return ApiResponseService::success($business, '业务配置获取成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('业务配置获取失败');
        }
    }
    
    /**
     * 获取支持配置
     */
    public function support()
    {
        try {
            $support = BanhoConfigService::getSupportConfig();
            return ApiResponseService::success($support, '支持配置获取成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('支持配置获取失败');
        }
    }
    
    /**
     * 汇率转换API
     */
    public function exchangeRate(Request $request)
    {
        try {
            $request->validate([
                'from' => 'required|string|in:JPY,CNY,USD',
                'to' => 'required|string|in:JPY,CNY,USD',
                'amount' => 'required|numeric|min:0',
            ]);
            
            $businessConfig = BanhoConfigService::getBusinessConfig();
            $rates = $businessConfig['currency']['exchange_rates'];
            
            $rateKey = strtoupper($request->from . '_' . $request->to);
            $rate = $rates[$rateKey] ?? 1;
            
            $convertedAmount = $request->amount * $rate;
            
            return ApiResponseService::success([
                'from' => $request->from,
                'to' => $request->to,
                'amount' => $request->amount,
                'rate' => $rate,
                'converted_amount' => round($convertedAmount, 2),
                'timestamp' => now()->toISOString(),
            ], '汇率转换成功');
            
        } catch (\Exception $e) {
            return ApiResponseService::serverError('汇率转换失败');
        }
    }
    
    /**
     * 清除配置缓存
     */
    public function clearCache()
    {
        try {
            BanhoConfigService::clearConfigCache();
            return ApiResponseService::success(null, '配置缓存清除成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('配置缓存清除失败');
        }
    }
}