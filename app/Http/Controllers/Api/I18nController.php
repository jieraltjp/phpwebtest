<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\I18nService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class I18nController extends Controller
{
    /**
     * 获取支持的语言列表
     */
    public function getSupportedLocales(): JsonResponse
    {
        try {
            $locales = I18nService::getSupportedLocales();
            $currentLocale = I18nService::getCurrentLocale();
            
            return ApiResponseService::success([
                'current_locale' => $currentLocale,
                'current_info' => I18nService::getCurrentLocaleInfo(),
                'supported_locales' => $locales,
            ], '获取语言列表成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取语言列表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 切换语言
     */
    public function switchLocale(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'locale' => 'required|string|in:zh,ja,en',
            ]);
            
            $locale = $request->input('locale');
            $success = I18nService::setLocale($locale);
            
            if ($success) {
                return ApiResponseService::success([
                    'locale' => $locale,
                    'info' => I18nService::getCurrentLocaleInfo(),
                ], '语言切换成功');
            } else {
                return ApiResponseService::error('不支持的语言');
            }
        } catch (\Exception $e) {
            return ApiResponseService::serverError('语言切换失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取翻译
     */
    public function getTranslations(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'locale' => 'string|in:zh,ja,en',
                'group' => 'string|required',
            ]);
            
            $locale = $request->input('locale', I18nService::getCurrentLocale());
            $group = $request->input('group');
            
            $translations = $this->loadTranslations($group, $locale);
            
            return ApiResponseService::success([
                'locale' => $locale,
                'group' => $group,
                'translations' => $translations,
            ], '获取翻译成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取翻译失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 格式化数据
     */
    public function formatData(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string|in:currency,date,time,datetime,number',
                'value' => 'required',
                'locale' => 'string|in:zh,ja,en',
            ]);
            
            $type = $request->input('type');
            $value = $request->input('value');
            $locale = $request->input('locale', I18nService::getCurrentLocale());
            
            $formatted = match($type) {
                'currency' => I18nService::formatCurrency((float) $value, $locale),
                'date' => I18nService::formatDate($value, $locale),
                'time' => I18nService::formatTime($value, $locale),
                'datetime' => I18nService::formatDateTime($value, $locale),
                'number' => I18nService::formatNumber((float) $value, $locale),
                default => $value,
            };
            
            return ApiResponseService::success([
                'type' => $type,
                'value' => $value,
                'formatted' => $formatted,
                'locale' => $locale,
            ], '格式化成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('格式化失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取语言配置
     */
    public function getConfig(): JsonResponse
    {
        try {
            $config = [
                'default_locale' => config('i18n.default_locale'),
                'fallback_locale' => config('i18n.fallback_locale'),
                'auto_detect' => config('i18n.auto_detect'),
                'url_prefix' => config('i18n.url_prefix'),
                'hide_default_locale' => config('i18n.hide_default_locale'),
                'translation' => [
                    'load_from' => config('i18n.translation.load_from'),
                    'cache_enabled' => config('i18n.translation.cache_enabled'),
                    'cache_ttl' => config('i18n.translation.cache_ttl'),
                ],
                'currency' => config('i18n.currency'),
                'date_format' => config('i18n.date_format'),
                'time_format' => config('i18n.time_format'),
                'datetime_format' => config('i18n.datetime_format'),
                'number_format' => config('i18n.number_format'),
                'rtl_locales' => I18nService::getRtlLocales(),
            ];
            
            return ApiResponseService::success($config, '获取语言配置成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取语言配置失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 清除翻译缓存
     */
    public function clearCache(): JsonResponse
    {
        try {
            $cleared = I18nService::clearCache();
            
            return ApiResponseService::success([
                'cleared_keys' => $cleared,
            ], '翻译缓存清除成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('清除翻译缓存失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 加载翻译文件
     */
    protected function loadTranslations(string $group, string $locale): array
    {
        $path = resource_path("lang/{$locale}/{$group}.php");
        
        if (!file_exists($path)) {
            return [];
        }
        
        return include $path;
    }
}