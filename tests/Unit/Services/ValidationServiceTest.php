<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ValidationService;
use Illuminate\Validation\ValidationException;

class ValidationServiceTest extends TestCase
{
    protected ValidationService $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = new ValidationService();
    }

    /**
     * 测试邮箱验证
     */
    public function test_email_validation(): void
    {
        // 有效邮箱
        $validEmails = [
            'test@example.com',
            'user.name@domain.co.jp',
            'user+tag@example.org',
            'user123@test-domain.com'
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue(
                $this->validationService->isValidEmail($email),
                "Email {$email} should be valid"
            );
        }

        // 无效邮箱
        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'user@',
            'user..name@example.com',
            'user@.com',
            'user name@example.com',
            '',
            null
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse(
                $this->validationService->isValidEmail($email),
                "Email {$email} should be invalid"
            );
        }
    }

    /**
     * 测试手机号验证
     */
    public function test_phone_validation(): void
    {
        // 有效手机号
        $validPhones = [
            '+81-90-1234-5678',
            '+86-138-0013-8000',
            '090-1234-5678',
            '13800138000',
            '+1-555-123-4567'
        ];

        foreach ($validPhones as $phone) {
            $this->assertTrue(
                $this->validationService->isValidPhone($phone),
                "Phone {$phone} should be valid"
            );
        }

        // 无效手机号
        $invalidPhones = [
            '123',
            'phone-number',
            '+123-abc-def-ghi',
            '',
            null
        ];

        foreach ($invalidPhones as $phone) {
            $this->assertFalse(
                $this->validationService->isValidPhone($phone),
                "Phone {$phone} should be invalid"
            );
        }
    }

    /**
     * 测试密码强度验证
     */
    public function test_password_strength_validation(): void
    {
        // 强密码
        $strongPasswords = [
            'MySecureP@ssw0rd!',
            'Str0ng#P@ssword',
            'C0mpl3x!P@ss',
            'Secure123!@#'
        ];

        foreach ($strongPasswords as $password) {
            $this->assertTrue(
                $this->validationService->isStrongPassword($password),
                "Password {$password} should be strong"
            );
        }

        // 弱密码
        $weakPasswords = [
            'password',
            '12345678',
            'qwerty',
            'weak',
            'short',
            '',
            null
        ];

        foreach ($weakPasswords as $password) {
            $this->assertFalse(
                $this->validationService->isStrongPassword($password),
                "Password {$password} should be weak"
            );
        }
    }

    /**
     * 测试URL验证
     */
    public function test_url_validation(): void
    {
        // 有效URL
        $validUrls = [
            'https://www.example.com',
            'http://example.com/path',
            'https://subdomain.example.co.jp',
            'https://example.com:8080/path?query=value',
            'ftp://example.com'
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(
                $this->validationService->isValidUrl($url),
                "URL {$url} should be valid"
            );
        }

        // 无效URL
        $invalidUrls = [
            'not-a-url',
            'http://',
            'example.com',
            '',
            null
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse(
                $this->validationService->isValidUrl($url),
                "URL {$url} should be invalid"
            );
        }
    }

    /**
     * 测试IP地址验证
     */
    public function test_ip_validation(): void
    {
        // 有效IP地址
        $validIps = [
            '192.168.1.1',
            '10.0.0.1',
            '172.16.0.1',
            '127.0.0.1',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            '::1'
        ];

        foreach ($validIps as $ip) {
            $this->assertTrue(
                $this->validationService->isValidIp($ip),
                "IP {$ip} should be valid"
            );
        }

        // 无效IP地址
        $invalidIps = [
            '256.256.256.256',
            '192.168.1',
            'not-an-ip',
            '',
            null
        ];

        foreach ($invalidIps as $ip) {
            $this->assertFalse(
                $this->validationService->isValidIp($ip),
                "IP {$ip} should be invalid"
            );
        }
    }

    /**
     * 测试信用卡号验证
     */
    public function test_credit_card_validation(): void
    {
        // 有效信用卡号（测试用）
        $validCards = [
            '4532015112830366', // Visa
            '5555555555554444', // MasterCard
            '378282246310005',  // American Express
            '6011111111111117'  // Discover
        ];

        foreach ($validCards as $card) {
            $this->assertTrue(
                $this->validationService->isValidCreditCard($card),
                "Card {$card} should be valid"
            );
        }

        // 无效信用卡号
        $invalidCards = [
            '1234567890123456',
            '1111111111111111',
            'invalid-card',
            '',
            null
        ];

        foreach ($invalidCards as $card) {
            $this->assertFalse(
                $this->validationService->isValidCreditCard($card),
                "Card {$card} should be invalid"
            );
        }
    }

    /**
     * 测试用户注册数据验证
     */
    public function test_user_registration_validation(): void
    {
        $validData = [
            'username' => 'testuser123',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'phone' => '+81-90-1234-5678'
        ];

        // 有效数据应该通过验证
        $result = $this->validationService->validateUserRegistration($validData);
        $this->assertTrue($result);

        // 测试各种无效数据
        $invalidCases = [
            // 缺少用户名
            [
                'email' => 'test@example.com',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!'
            ],
            // 无效邮箱
            [
                'username' => 'testuser123',
                'email' => 'invalid-email',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!'
            ],
            // 密码不匹配
            [
                'username' => 'testuser123',
                'email' => 'test@example.com',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'DifferentPass123!'
            ],
            // 弱密码
            [
                'username' => 'testuser123',
                'email' => 'test@example.com',
                'password' => 'weak',
                'password_confirmation' => 'weak'
            ]
        ];

        foreach ($invalidCases as $data) {
            $this->expectException(ValidationException::class);
            $this->validationService->validateUserRegistration($data);
        }
    }

    /**
     * 测试产品数据验证
     */
    public function test_product_validation(): void
    {
        $validProduct = [
            'sku' => 'PROD-001',
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'currency' => 'CNY',
            'stock_quantity' => 100,
            'category' => 'electronics'
        ];

        // 有效产品数据
        $result = $this->validationService->validateProduct($validProduct);
        $this->assertTrue($result);

        // 测试无效产品数据
        $invalidProducts = [
            // 缺少SKU
            [
                'name' => 'Test Product',
                'price' => 99.99
            ],
            // 负价格
            [
                'sku' => 'PROD-001',
                'name' => 'Test Product',
                'price' => -10.00
            ],
            // 无效货币
            [
                'sku' => 'PROD-001',
                'name' => 'Test Product',
                'price' => 99.99,
                'currency' => 'INVALID'
            ],
            // 负库存
            [
                'sku' => 'PROD-001',
                'name' => 'Test Product',
                'price' => 99.99,
                'stock_quantity' => -5
            ]
        ];

        foreach ($invalidProducts as $product) {
            $this->expectException(ValidationException::class);
            $this->validationService->validateProduct($product);
        }
    }

    /**
     * 测试订单数据验证
     */
    public function test_order_validation(): void
    {
        $validOrder = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'shipping_address' => '123 Test St, Tokyo, Japan',
            'items' => [
                [
                    'sku' => 'PROD-001',
                    'quantity' => 2,
                    'price' => 99.99
                ]
            ],
            'total_amount' => 199.98,
            'currency' => 'CNY'
        ];

        // 有效订单数据
        $result = $this->validationService->validateOrder($validOrder);
        $this->assertTrue($result);

        // 测试无效订单数据
        $invalidOrders = [
            // 缺少客户信息
            [
                'items' => [],
                'total_amount' => 0
            ],
            // 空订单项
            [
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'items' => [],
                'total_amount' => 0
            ],
            // 无效订单项
            [
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'items' => [
                    [
                        'sku' => '',
                        'quantity' => 0,
                        'price' => -10
                    ]
                ],
                'total_amount' => 0
            ]
        ];

        foreach ($invalidOrders as $order) {
            $this->expectException(ValidationException::class);
            $this->validationService->validateOrder($order);
        }
    }

    /**
     * 测试询价数据验证
     */
    public function test_inquiry_validation(): void
    {
        $validInquiry = [
            'contact_name' => 'Jane Smith',
            'contact_email' => 'jane@example.com',
            'contact_phone' => '+81-90-1234-5678',
            'company_name' => 'Test Company',
            'product_skus' => ['PROD-001', 'PROD-002'],
            'quantity' => 100,
            'message' => 'Interested in bulk purchase',
            'target_price' => 80.00
        ];

        // 有效询价数据
        $result = $this->validationService->validateInquiry($validInquiry);
        $this->assertTrue($result);

        // 测试无效询价数据
        $invalidInquiries = [
            // 缺少联系信息
            [
                'product_skus' => ['PROD-001'],
                'quantity' => 100
            ],
            // 无效邮箱
            [
                'contact_name' => 'Jane Smith',
                'contact_email' => 'invalid-email',
                'product_skus' => ['PROD-001'],
                'quantity' => 100
            ],
            // 空产品SKU列表
            [
                'contact_name' => 'Jane Smith',
                'contact_email' => 'jane@example.com',
                'product_skus' => [],
                'quantity' => 100
            ],
            // 负数量
            [
                'contact_name' => 'Jane Smith',
                'contact_email' => 'jane@example.com',
                'product_skus' => ['PROD-001'],
                'quantity' => -10
            ]
        ];

        foreach ($invalidInquiries as $inquiry) {
            $this->expectException(ValidationException::class);
            $this->validationService->validateInquiry($inquiry);
        }
    }

    /**
     * 测试批量采购数据验证
     */
    public function test_bulk_purchase_validation(): void
    {
        $validBulkPurchase = [
            'customer_name' => 'Bulk Buyer',
            'customer_email' => 'bulk@example.com',
            'items' => [
                ['sku' => 'PROD-001', 'quantity' => 50],
                ['sku' => 'PROD-002', 'quantity' => 30],
                ['sku' => 'PROD-003', 'quantity' => 20]
            ],
            'shipping_address' => '456 Bulk St, Tokyo, Japan',
            'expected_delivery_date' => '2025-12-25'
        ];

        // 有效批量采购数据
        $result = $this->validationService->validateBulkPurchase($validBulkPurchase);
        $this->assertTrue($result);

        // 测试无效批量采购数据
        $invalidBulkPurchases = [
            // 超过50个SKU限制
            [
                'customer_name' => 'Bulk Buyer',
                'customer_email' => 'bulk@example.com',
                'items' => array_fill(0, 51, ['sku' => 'PROD-001', 'quantity' => 1])
            ],
            // 单个SKU数量过大
            [
                'customer_name' => 'Bulk Buyer',
                'customer_email' => 'bulk@example.com',
                'items' => [
                    ['sku' => 'PROD-001', 'quantity' => 10001]
                ]
            ],
            // 无效交货日期
            [
                'customer_name' => 'Bulk Buyer',
                'customer_email' => 'bulk@example.com',
                'items' => [
                    ['sku' => 'PROD-001', 'quantity' => 50]
                ],
                'expected_delivery_date' => '2025-01-01' // 过去日期
            ]
        ];

        foreach ($invalidBulkPurchases as $bulkPurchase) {
            $this->expectException(ValidationException::class);
            $this->validationService->validateBulkPurchase($bulkPurchase);
        }
    }

    /**
     * 测试输入清理和过滤
     */
    public function test_input_sanitization(): void
    {
        $dirtyInput = '<script>alert("xss")</script>Hello <b>World</b>';
        $cleaned = $this->validationService->sanitizeString($dirtyInput);
        
        $this->assertEquals('alert("xss")Hello World', $cleaned);
        $this->assertStringNotContainsString('<script>', $cleaned);
        $this->assertStringNotContainsString('</script>', $cleaned);
    }

    /**
     * 测试SQL注入防护
     */
    public function test_sql_injection_protection(): void
    {
        $maliciousInput = "'; DROP TABLE users; --";
        $cleaned = $this->validationService->sanitizeForSql($maliciousInput);
        
        $this->assertStringNotContainsString("'", $cleaned);
        $this->assertStringNotContainsString(';', $cleaned);
        $this->assertStringNotContainsString('--', $cleaned);
    }

    /**
     * 测试文件上传验证
     */
    public function test_file_upload_validation(): void
    {
        $validFile = [
            'name' => 'document.pdf',
            'type' => 'application/pdf',
            'size' => 1024 * 1024, // 1MB
            'tmp_name' => '/tmp/test_file'
        ];

        // 有效文件
        $result = $this->validationService->validateFileUpload($validFile, ['pdf', 'doc'], 2 * 1024 * 1024);
        $this->assertTrue($result);

        // 测试无效文件
        $invalidFiles = [
            // 不允许的文件类型
            [
                'name' => 'script.js',
                'type' => 'application/javascript',
                'size' => 1024,
                'tmp_name' => '/tmp/test_file'
            ],
            // 文件过大
            [
                'name' => 'large.pdf',
                'type' => 'application/pdf',
                'size' => 3 * 1024 * 1024, // 3MB
                'tmp_name' => '/tmp/test_file'
            ]
        ];

        foreach ($invalidFiles as $file) {
            $this->expectException(ValidationException::class);
            $this->validationService->validateFileUpload($file, ['pdf', 'doc'], 2 * 1024 * 1024);
        }
    }

    /**
     * 测试自定义验证规则
     */
    public function test_custom_validation_rules(): void
    {
        // 测试日本邮政编码验证
        $validPostalCodes = ['123-4567', '100-0001', '530-0001'];
        foreach ($validPostalCodes as $code) {
            $this->assertTrue(
                $this->validationService->isValidJapanesePostalCode($code),
                "Postal code {$code} should be valid"
            );
        }

        $invalidPostalCodes = ['1234567', '12-3456', 'invalid'];
        foreach ($invalidPostalCodes as $code) {
            $this->assertFalse(
                $this->validationService->isValidJapanesePostalCode($code),
                "Postal code {$code} should be invalid"
            );
        }

        // 测试SKU格式验证
        $validSkus = ['PROD-001', 'SKU-123', 'ITEM-ABC123'];
        foreach ($validSkus as $sku) {
            $this->assertTrue(
                $this->validationService->isValidSku($sku),
                "SKU {$sku} should be valid"
            );
        }

        $invalidSkus = ['prod-001', '123', 'INVALID SKU WITH SPACES'];
        foreach ($invalidSkus as $sku) {
            $this->assertFalse(
                $this->validationService->isValidSku($sku),
                "SKU {$sku} should be invalid"
            );
        }
    }

    /**
     * 测试批量验证
     */
    public function test_batch_validation(): void
    {
        $dataList = [
            ['email' => 'test1@example.com', 'phone' => '+81-90-1234-5678'],
            ['email' => 'test2@example.com', 'phone' => '+81-90-1234-5679'],
            ['email' => 'invalid-email', 'phone' => '+81-90-1234-5680'] // 这个会失败
        ];

        $rules = [
            'email' => 'required|email',
            'phone' => 'required|string'
        ];

        $results = $this->validationService->validateBatch($dataList, $rules);

        $this->assertCount(3, $results);
        $this->assertTrue($results[0]['valid']);
        $this->assertTrue($results[1]['valid']);
        $this->assertFalse($results[2]['valid']);
        $this->assertArrayHasKey('errors', $results[2]);
    }
}