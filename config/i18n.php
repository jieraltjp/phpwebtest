<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 多语言配置
    |--------------------------------------------------------------------------
    |
    | 支持的语言列表和默认语言设置
    |
    */

    'supported_locales' => [
        'zh' => [
            'name' => '中文',
            'native_name' => '中文',
            'flag' => '🇨🇳',
            'code' => 'zh-CN',
        ],
        'ja' => [
            'name' => 'Japanese',
            'native_name' => '日本語',
            'flag' => '🇯🇵',
            'code' => 'ja-JP',
        ],
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'flag' => '🇺🇸',
            'code' => 'en-US',
        ],
    ],

    'default_locale' => 'zh',

    'fallback_locale' => 'zh',

    'locale_session_key' => 'locale',

    'locale_cookie_key' => 'locale',

    'auto_detect' => true,

    'url_prefix' => false,

    'hide_default_locale' => true,

    'translation' => [
        'load_from' => 'files', // 'files' or 'database'
        'file_path' => resource_path('lang'),
        'database_table' => 'translations',
        'cache_enabled' => true,
        'cache_ttl' => 3600, // 1 hour
    ],

    'currency' => [
        'zh' => 'CNY',
        'ja' => 'JPY',
        'en' => 'USD',
    ],

    'date_format' => [
        'zh' => 'Y-m-d',
        'ja' => 'Y/m/d',
        'en' => 'm/d/Y',
    ],

    'time_format' => [
        'zh' => 'H:i:s',
        'ja' => 'H:i:s',
        'en' => 'h:i:s A',
    ],

    'datetime_format' => [
        'zh' => 'Y-m-d H:i:s',
        'ja' => 'Y/m/d H:i:s',
        'en' => 'm/d/Y h:i:s A',
    ],

    'number_format' => [
        'zh' => [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'decimals' => 2,
        ],
        'ja' => [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'decimals' => 0,
        ],
        'en' => [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'decimals' => 2,
        ],
    ],
];