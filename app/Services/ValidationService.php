<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ValidationService
{
    /**
     * 验证规则定义
     */
    protected static $rules = [
        'user' => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'phone' => 'nullable|string|regex:/^[\+]?[1-9][\d]{0,15}$/',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
        ],
        'product' => [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'currency' => 'required|string|in:CNY,JPY,USD',
            'stock' => 'required|integer|min:0|max:999999',
            'image_url' => 'nullable|url|max:500',
            'supplier_shop' => 'required|string|max:255',
            'specs' => 'nullable|array',
            'category' => 'nullable|string|max:100',
        ],
        'order' => [
            'items' => 'required|array|min:1|max:100',
            'items.*.sku' => 'required|string|max:100',
            'items.*.quantity' => 'required|integer|min:1|max:10000',
            'shipping_address' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:2000',
            'contact_info' => 'required|array',
            'contact_info.email' => 'required|email',
            'contact_info.phone' => 'nullable|string|regex:/^[\+]?[1-9][\d]{0,15}$/',
            'contact_info.contact_person' => 'required|string|max:100',
            'contact_info.company' => 'nullable|string|max:255',
        ],
        'inquiry' => [
            'product_sku' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1|max:10000',
            'message' => 'nullable|string|max:1000',
            'contact_info' => 'required|array',
            'contact_info.email' => 'required|email',
            'contact_info.phone' => 'nullable|string|regex:/^[\+]?[1-9][\d]{0,15}$/',
            'contact_info.company' => 'nullable|string|max:255',
            'contact_info.contact_person' => 'required|string|max:100',
        ],
        'bulk_purchase' => [
            'items' => 'required|array|min:1|max:50',
            'items.*.sku' => 'required|string|max:100',
            'items.*.quantity' => 'required|integer|min:1|max:10000',
            'shipping_address' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:2000',
            'contact_info' => 'required|array',
            'contact_info.email' => 'required|email',
            'contact_info.phone' => 'nullable|string|regex:/^[\+]?[1-9][\d]{0,15}$/',
            'contact_info.contact_person' => 'required|string|max:100',
            'contact_info.company' => 'nullable|string|max:255',
        ],
    ];

    /**
     * 自定义错误消息
     */
    protected static $messages = [
        'required' => ':attribute 是必填字段',
        'string' => ':attribute 必须是字符串',
        'email' => ':attribute 必须是有效的邮箱地址',
        'unique' => ':attribute 已存在',
        'min' => ':attribute 最少需要 :min 个字符',
        'max' => ':attribute 不能超过 :max 个字符',
        'numeric' => ':attribute 必须是数字',
        'integer' => ':attribute 必须是整数',
        'regex' => ':attribute 格式不正确',
        'url' => ':attribute 必须是有效的URL',
        'array' => ':attribute 必须是数组',
        'in' => ':attribute 的值无效',
        'exists' => ':attribute 不存在',
    ];

    /**
     * 字段名称映射
     */
    protected static $attributes = [
        'name' => '姓名',
        'email' => '邮箱',
        'password' => '密码',
        'phone' => '电话',
        'company' => '公司名称',
        'address' => '地址',
        'sku' => '产品SKU',
        'price' => '价格',
        'currency' => '货币类型',
        'stock' => '库存',
        'quantity' => '数量',
        'shipping_address' => '收货地址',
        'notes' => '备注',
        'contact_person' => '联系人',
        'message' => '留言',
    ];

    /**
     * 验证用户数据
     */
    public static function validateUser(array $data, $isUpdate = false)
    {
        $rules = self::$rules['user'];
        
        if ($isUpdate) {
            // 更新时邮箱唯一性排除当前用户
            $userId = $data['id'] ?? null;
            if ($userId && isset($rules['email'])) {
                $rules['email'] = [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($userId)
                ];
            }
            
            // 更新时密码不是必填的
            if (!isset($data['password'])) {
                unset($rules['password']);
            }
        }

        return self::validate($data, $rules);
    }

    /**
     * 验证产品数据
     */
    public static function validateProduct(array $data, $isUpdate = false)
    {
        $rules = self::$rules['product'];
        
        if ($isUpdate) {
            $productId = $data['id'] ?? null;
            if ($productId && isset($rules['sku'])) {
                $rules['sku'] = [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('products', 'sku')->ignore($productId)
                ];
            }
        }

        return self::validate($data, $rules);
    }

    /**
     * 验证订单数据
     */
    public static function validateOrder(array $data)
    {
        $rules = self::$rules['order'];
        
        // 添加自定义验证规则
        $validator = Validator::make($data, $rules, self::$messages, self::$attributes);
        
        // 自定义验证：检查订单项数量限制
        $validator->after(function ($validator) use ($data) {
            if (isset($data['items'])) {
                $totalQuantity = array_sum(array_column($data['items'], 'quantity'));
                if ($totalQuantity > 100000) {
                    $validator->errors()->add('items', '订单总数量不能超过100,000');
                }
            }
        });

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }

        return ['valid' => true];
    }

    /**
     * 验证询价数据
     */
    public static function validateInquiry(array $data)
    {
        return self::validate($data, self::$rules['inquiry']);
    }

    /**
     * 验证批量采购数据
     */
    public static function validateBulkPurchase(array $data)
    {
        $rules = self::$rules['bulk_purchase'];
        
        $validator = Validator::make($data, $rules, self::$messages, self::$attributes);
        
        // 自定义验证
        $validator->after(function ($validator) use ($data) {
            if (isset($data['items'])) {
                // 检查SKU重复
                $skus = array_column($data['items'], 'sku');
                if (count($skus) !== count(array_unique($skus))) {
                    $validator->errors()->add('items', '产品SKU不能重复');
                }
                
                // 检查总数量限制
                $totalQuantity = array_sum(array_column($data['items'], 'quantity'));
                if ($totalQuantity > 500000) {
                    $validator->errors()->add('items', '批量采购总数量不能超过500,000');
                }
                
                // 检查单个项目数量限制
                foreach ($data['items'] as $index => $item) {
                    if ($item['quantity'] > 100000) {
                        $validator->errors()->add("items.{$index}.quantity", '单个产品数量不能超过100,000');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }

        return ['valid' => true];
    }

    /**
     * 验证搜索参数
     */
    public static function validateSearch(array $data)
    {
        $rules = [
            'query' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:50',
            'min_price' => 'nullable|numeric|min:0|max:999999.99',
            'max_price' => 'nullable|numeric|min:0|max:999999.99',
            'supplier' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:price_asc,price_desc,name_asc,name_desc,created_asc,created_desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];

        $validator = Validator::make($data, $rules, self::$messages, [
            'query' => '搜索关键词',
            'category' => '分类',
            'min_price' => '最低价格',
            'max_price' => '最高价格',
            'supplier' => '供应商',
            'sort' => '排序方式',
            'page' => '页码',
            'per_page' => '每页数量',
        ]);

        // 自定义验证
        $validator->after(function ($validator) use ($data) {
            // 检查价格范围
            if (isset($data['min_price']) && isset($data['max_price'])) {
                if ($data['min_price'] > $data['max_price']) {
                    $validator->errors()->add('min_price', '最低价格不能高于最高价格');
                }
            }
        });

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }

        return ['valid' => true];
    }

    /**
     * 验证文件上传
     */
    public static function validateFileUpload($file, array $allowedTypes = [], int $maxSize = 10240)
    {
        $rules = [
            'file' => 'required|file|max:' . $maxSize,
        ];

        if (!empty($allowedTypes)) {
            $rules['file'] .= '|mimes:' . implode(',', $allowedTypes);
        }

        $validator = Validator::make(['file' => $file], $rules, [
            'file.required' => '请选择要上传的文件',
            'file.max' => '文件大小不能超过 ' . ($maxSize / 1024) . ' MB',
            'file.mimes' => '文件类型不支持，允许的类型：' . implode(', ', $allowedTypes),
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }

        return ['valid' => true];
    }

    /**
     * 清理和过滤输入数据
     */
    public static function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // 移除HTML标签
                $value = strip_tags($value);
                
                // 转义特殊字符
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                
                // 移除多余空白
                $value = trim($value);
                
                // 限制长度
                if (strlen($value) > 10000) {
                    $value = substr($value, 0, 10000);
                }
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }

    /**
     * 验证IP地址
     */
    public static function validateIP($ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证URL
     */
    public static function validateURL($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 验证日期格式
     */
    public static function validateDate($date, $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * 验证JSON格式
     */
    public static function validateJSON($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 通用验证方法
     */
    protected static function validate(array $data, array $rules)
    {
        $validator = Validator::make($data, $rules, self::$messages, self::$attributes);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }

        return ['valid' => true];
    }

    /**
     * 获取验证规则
     */
    public static function getRules($type): array
    {
        return self::$rules[$type] ?? [];
    }

    /**
     * 添加自定义验证规则
     */
    public static function addCustomRule($name, $rule, $message = null)
    {
        Validator::extend($name, $rule, $message);
    }

    /**
     * 验证银行卡号
     */
    public static function validateBankCard($cardNumber): bool
    {
        // 移除空格和非数字字符
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        // 检查长度
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }
        
        // Luhn算法验证
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $digit = (int)$cardNumber[$i];
            
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $alternate = !$alternate;
        }
        
        return $sum % 10 === 0;
    }

    /**
     * 验证身份证号（简化版）
     */
    public static function validateIdCard($idCard): bool
    {
        // 简单的身份证验证规则
        return preg_match('/^\d{17}[\dXx]$/', $idCard) === 1;
    }
}