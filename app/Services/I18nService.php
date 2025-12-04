<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class I18nService
{
    /**
     * 获取当前语言
     */
    public static function getCurrentLocale(): string
    {
        // 优先级：会话 > Cookie > 自动检测 > 默认语言
        $locale = Session::get(config('i18n.locale_session_key'));
        
        if (!$locale) {
            $locale = Cookie::get(config('i18n.locale_cookie_key'));
        }
        
        if (!$locale && config('i18n.auto_detect')) {
            $locale = self::detectLocale();
        }
        
        if (!$locale || !self::isSupported($locale)) {
            $locale = config('i18n.default_locale', 'zh');
        }
        
        return $locale;
    }
    
    /**
     * 设置当前语言
     */
    public static function setLocale(string $locale): bool
    {
        if (!self::isSupported($locale)) {
            return false;
        }
        
        Session::put(config('i18n.locale_session_key'), $locale);
        Cookie::queue(config('i18n.locale_cookie_key'), $locale, 60 * 24 * 30); // 30天
        App::setLocale($locale);
        
        return true;
    }
    
    /**
     * 检测用户语言
     */
    public static function detectLocale(): ?string
    {
        $request = request();
        if (!$request) {
            return null;
        }
        
        // 从HTTP Accept-Language头检测
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            $locales = config('i18n.supported_locales');
            
            // 解析Accept-Language头
            preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/i', $acceptLanguage, $matches);
            
            if (isset($matches[1])) {
                $languages = [];
                foreach ($matches[1] as $key => $lang) {
                    $quality = isset($matches[2][$key]) ? (float) $matches[2][$key] : 1.0;
                    $languages[$lang] = $quality;
                }
                
                // 按质量排序
                arsort($languages);
                
                foreach ($languages as $lang => $quality) {
                    $lang = strtolower(substr($lang, 0, 2));
                    if (isset($locales[$lang])) {
                        return $lang;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * 检查语言是否支持
     */
    public static function isSupported(string $locale): bool
    {
        return array_key_exists($locale, config('i18n.supported_locales', []));
    }
    
    /**
     * 获取支持的语言列表
     */
    public static function getSupportedLocales(): array
    {
        return config('i18n.supported_locales', []);
    }
    
    /**
     * 获取当前语言信息
     */
    public static function getCurrentLocaleInfo(): array
    {
        $locale = self::getCurrentLocale();
        $locales = self::getSupportedLocales();
        
        return $locales[$locale] ?? [
            'name' => 'Unknown',
            'native_name' => 'Unknown',
            'flag' => '🌐',
            'code' => 'unknown',
        ];
    }
    
    /**
     * 翻译文本
     */
    public static function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = self::getCurrentLocale();
        }
        
        // 尝试从语言包获取翻译
        $translation = self::getFromFile($key, $locale);
        
        if ($translation === $key) {
            // 如果没有找到翻译，尝试使用回退语言
            $fallbackLocale = config('i18n.fallback_locale', 'zh');
            if ($fallbackLocale !== $locale) {
                $translation = self::getFromFile($key, $fallbackLocale);
            }
        }
        
        // 替换占位符
        foreach ($replace as $placeholder => $value) {
            $translation = str_replace(':' . $placeholder, $value, $translation);
        }
        
        return $translation;
    }
    
    /**
     * 从文件获取翻译
     */
    protected static function getFromFile(string $key, string $locale): string
    {
        $cacheKey = "translation:{$locale}:{$key}";
        
        if (config('i18n.translation.cache_enabled', true)) {
            return Cache::remember($cacheKey, config('i18n.translation.cache_ttl', 3600), function () use ($key, $locale) {
                return self::loadTranslationFromFile($key, $locale);
            });
        }
        
        return self::loadTranslationFromFile($key, $locale);
    }
    
    /**
     * 从文件加载翻译
     */
    protected static function loadTranslationFromFile(string $key, string $locale): string
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);
        
        $path = resource_path("lang/{$locale}/{$file}.php");
        
        if (!file_exists($path)) {
            return $key;
        }
        
        $translations = include $path;
        
        foreach ($parts as $part) {
            if (!isset($translations[$part])) {
                return $key;
            }
            $translations = $translations[$part];
        }
        
        return $translations;
    }
    
    /**
     * 格式化货币
     */
    public static function formatCurrency(float $amount, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = self::getCurrentLocale();
        }
        
        $currency = config("i18n.currency.{$locale}", 'CNY');
        $format = config("i18n.number_format.{$locale}", [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'decimals' => 2,
        ]);
        
        $formatted = number_format(
            $amount,
            $format['decimals'],
            $format['decimal_separator'],
            $format['thousands_separator']
        );
        
        switch ($currency) {
            case 'CNY':
                return "¥{$formatted}";
            case 'JPY':
                return "¥{$formatted}";
            case 'USD':
                return "${$formatted}";
            default:
                return "{$formatted} {$currency}";
        }
    }
    
    /**
     * 格式化日期
     */
    public static function formatDate($date, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = self::getCurrentLocale();
        }
        
        $format = config("i18n.date_format.{$locale}", 'Y-m-d');
        
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * 格式化时间
     */
    public static function formatTime($time, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = self::getCurrentLocale();
        }
        
        $format = config("i18n.time_format.{$locale}", 'H:i:s');
        
        if ($time instanceof \DateTime) {
            return $time->format($format);
        }
        
        return date($format, strtotime($time));
    }
    
    /**
     * 格式化日期时间
     */
    public static function formatDateTime($datetime, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = self::getCurrentLocale();
        }
        
        $format = config("i18n.datetime_format.{$locale}", 'Y-m-d H:i:s');
        
        if ($datetime instanceof \DateTime) {
            return $datetime->format($format);
        }
        
        return date($format, strtotime($datetime));
    }
    
    /**
     * 格式化数字
     */
    public static function formatNumber(float $number, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = self::getCurrentLocale();
        }
        
        $format = config("i18n.number_format.{$locale}", [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'decimals' => 2,
        ]);
        
        return number_format(
            $number,
            $format['decimals'],
            $format['decimal_separator'],
            $format['thousands_separator']
        );
    }
    
    /**
     * 获取语言URL
     */
    public static function getLocaleUrl(string $locale, ?string $url = null): string
    {
        if (!self::isSupported($locale)) {
            return $url ?: url()->current();
        }
        
        if ($url === null) {
            $url = url()->current();
        }
        
        // 如果启用了URL前缀
        if (config('i18n.url_prefix', false)) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '/';
            
            // 移除现有的语言前缀
            foreach (array_keys(self::getSupportedLocales()) as $supportedLocale) {
                if (Str::startsWith($path, "/{$supportedLocale}")) {
                    $path = substr($path, strlen("/{$supportedLocale}"));
                    break;
                }
            }
            
            // 添加新的语言前缀（如果不是默认语言或隐藏默认语言被禁用）
            if ($locale !== config('i18n.default_locale') || !config('i18n.hide_default_locale', true)) {
                $path = "/{$locale}" . ($path === '/' ? '' : $path);
            }
            
            $parsedUrl['path'] = $path;
            return http_build_url($parsedUrl);
        }
        
        return $url;
    }
    
    /**
     * 清除翻译缓存
     */
    public static function clearCache(): int
    {
        $cleared = 0;
        
        try {
            if (app()->cache->getStore() instanceof \Illuminate\Cache\RedisStore) {
                $pattern = 'translation:*';
                $keys = app('redis')->keys($pattern);
                
                if (!empty($keys)) {
                    $cleared = app('redis')->del($keys);
                }
            }
        } catch (\Exception $e) {
            // 忽略清除失败
        }
        
        return $cleared;
    }
    
    /**
     * 获取RTL语言列表
     */
    public static function getRtlLocales(): array
    {
        return [
            'ar', // Arabic
            'he', // Hebrew
            'fa', // Persian
            'ur', // Urdu
        ];
    }
    
    /**
     * 检查当前语言是否为RTL
     */
    public static function isRtl(?string $locale = null): bool
    {
        if ($locale === null) {
            $locale = self::getCurrentLocale();
        }
        
        return in_array($locale, self::getRtlLocales());
    }
    
    /**
     * 获取文本方向
     */
    public static function getTextDirection(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }
}

// 辅助函数
if (!function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null): string
    {
        return I18nService::translate($key, $replace, $locale);
    }
}

if (!function_exists('currency')) {
    function currency(float $amount, ?string $locale = null): string
    {
        return I18nService::formatCurrency($amount, $locale);
    }
}

if (!function_exists('i18n_date')) {
    function i18n_date($date, ?string $locale = null): string
    {
        return I18nService::formatDate($date, $locale);
    }
}

if (!function_exists('i18n_time')) {
    function i18n_time($time, ?string $locale = null): string
    {
        return I18nService::formatTime($time, $locale);
    }
}

if (!function_exists('i18n_datetime')) {
    function i18n_datetime($datetime, ?string $locale = null): string
    {
        return I18nService::formatDateTime($datetime, $locale);
    }
}

if (!function_exists('i18n_number')) {
    function i18n_number(float $number, ?string $locale = null): string
    {
        return I18nService::formatNumber($number, $locale);
    }
}