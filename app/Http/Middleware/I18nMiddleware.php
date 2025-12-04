<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\I18nService;
use Illuminate\Support\Facades\App;

class I18nMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 从URL获取语言（如果启用了URL前缀）
        $locale = $this->getLocaleFromUrl($request);
        
        // 如果URL中没有语言，则使用其他方式获取
        if (!$locale) {
            $locale = I18nService::getCurrentLocale();
        }
        
        // 设置应用语言
        App::setLocale($locale);
        
        // 共享语言变量到所有视图
        view()->share('currentLocale', $locale);
        view()->share('localeInfo', I18nService::getCurrentLocaleInfo());
        view()->share('supportedLocales', I18nService::getSupportedLocales());
        view()->share('isRtl', I18nService::isRtl($locale));
        view()->share('textDirection', I18nService::getTextDirection($locale));
        
        return $next($request);
    }
    
    /**
     * 从URL获取语言
     */
    protected function getLocaleFromUrl(Request $request): ?string
    {
        if (!config('i18n.url_prefix', false)) {
            return null;
        }
        
        $path = $request->path();
        $segments = explode('/', $path);
        
        if (empty($segments[0])) {
            return null;
        }
        
        $locale = $segments[0];
        
        // 检查是否为支持的语言
        if (I18nService::isSupported($locale)) {
            // 如果是默认语言且启用了隐藏默认语言，则重定向到无前缀URL
            if ($locale === config('i18n.default_locale') && config('i18n.hide_default_locale', true)) {
                $newPath = implode('/', array_slice($segments, 1));
                $newUrl = $newPath ? url($newPath) : url('/');
                
                return redirect($newUrl, 301)->send();
            }
            
            return $locale;
        }
        
        return null;
    }
}