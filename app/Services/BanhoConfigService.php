<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class BanhoConfigService
{
    /**
     * 万方商事配置常量
     */
    const COMPANY_NAME = '万方商事株式会社';
    const COMPANY_NAME_EN = 'BANHO TRADING CO., LTD.';
    const COMPANY_WEBSITE = 'https://manpou.jp/';
    
    /**
     * 品牌色彩配置
     */
    const BRAND_COLORS = [
        'primary' => '#1a365d',
        'primary_light' => '#2c5282',
        'primary_dark' => '#1a2744',
        'secondary' => '#d4af37',
        'secondary_light' => '#f4e4bc',
        'accent' => '#dc2626',
    ];
    
    /**
     * 获取公司信息
     */
    public static function getCompanyInfo(): array
    {
        return [
            'name' => self::COMPANY_NAME,
            'name_en' => self::COMPANY_NAME_EN,
            'website' => self::COMPANY_WEBSITE,
            'address' => '〒100-0001 東京都千代田区',
            'phone' => '03-1234-5678',
            'email' => 'info@manpou.jp',
            'founded' => '2010年',
            'employees' => '500+',
            'business' => 'B2B貿易・仕入れサービス',
        ];
    }
    
    /**
     * 获取品牌配置
     */
    public static function getBrandConfig(): array
    {
        return Cache::remember('banho_brand_config', 86400, function () {
            return [
                'colors' => self::BRAND_COLORS,
                'fonts' => [
                    'primary' => 'Noto Sans JP',
                    'heading' => 'Noto Serif JP',
                ],
                'logo' => [
                    'url' => '/assets/images/banho-logo.png',
                    'width' => 200,
                    'height' => 60,
                ],
                'theme' => [
                    'mode' => 'professional',
                    'style' => 'japanese-corporate',
                ],
            ];
        });
    }
    
    /**
     * 获取多语言配置
     */
    public static function getLanguageConfig(): array
    {
        return [
            'default' => 'ja',
            'supported' => ['ja', 'en', 'zh'],
            'fallback' => 'ja',
            'translations' => [
                'ja' => [
                    'dashboard' => 'ダッシュボード',
                    'products' => '商品',
                    'orders' => '注文',
                    'inquiries' => '見積もり',
                    'settings' => '設定',
                ],
                'en' => [
                    'dashboard' => 'Dashboard',
                    'products' => 'Products',
                    'orders' => 'Orders',
                    'inquiries' => 'Inquiries',
                    'settings' => 'Settings',
                ],
                'zh' => [
                    'dashboard' => '仪表板',
                    'products' => '产品',
                    'orders' => '订单',
                    'inquiries' => '询价',
                    'settings' => '设置',
                ],
            ],
        ];
    }
    
    /**
     * 获取业务配置
     */
    public static function getBusinessConfig(): array
    {
        return [
            'currency' => [
                'default' => 'JPY',
                'supported' => ['JPY', 'CNY', 'USD'],
                'exchange_rates' => [
                    'JPY_CNY' => 0.048,
                    'JPY_USD' => 0.0067,
                    'CNY_JPY' => 20.83,
                    'USD_JPY' => 149.25,
                ],
            ],
            'shipping' => [
                'methods' => ['air', 'sea', 'express'],
                'regions' => ['japan', 'china', 'global'],
                'default_days' => [
                    'air' => 7,
                    'sea' => 30,
                    'express' => 3,
                ],
            ],
            'payment' => [
                'methods' => ['bank_transfer', 'credit_card', 'alipay'],
                'terms' => [
                    'net_30' => '30日払い',
                    'net_60' => '60日払い',
                    'prepaid' => '前払い',
                ],
            ],
        ];
    }
    
    /**
     * 获取客户支持配置
     */
    public static function getSupportConfig(): array
    {
        return [
            'business_hours' => [
                'weekdays' => '9:00-18:00',
                'saturday' => '9:00-12:00',
                'sunday' => '定休',
            ],
            'contact' => [
                'phone' => [
                    'japan' => '+81-3-1234-5678',
                    'china' => '+86-21-1234-5678',
                    'global' => '+1-212-1234-5678',
                ],
                'email' => [
                    'general' => 'info@manpou.jp',
                    'support' => 'support@manpou.jp',
                    'sales' => 'sales@manpou.jp',
                ],
            ],
            'languages' => ['日本語', '中文', 'English'],
            'response_time' => [
                'email' => '24時間以内',
                'phone' => '即時対応',
                'chat' => '営業時間内',
            ],
        ];
    }
    
    /**
     * 清除配置缓存
     */
    public static function clearConfigCache(): void
    {
        Cache::forget('banho_brand_config');
        Cache::forget('banho_business_config');
    }
    
    /**
     * 获取完整的配置数据
     */
    public static function getFullConfig(): array
    {
        return [
            'company' => self::getCompanyInfo(),
            'brand' => self::getBrandConfig(),
            'language' => self::getLanguageConfig(),
            'business' => self::getBusinessConfig(),
            'support' => self::getSupportConfig(),
        ];
    }
}