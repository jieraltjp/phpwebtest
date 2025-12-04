<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Log;

class EncryptionServiceTest extends TestCase
{
    protected EncryptionService $encryptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encryptionService = new EncryptionService();
    }

    /**
     * 测试字符串加密和解密
     */
    public function test_string_encryption_decryption(): void
    {
        $originalString = 'This is a secret message';
        $key = 'test_encryption_key';

        // 加密
        $encrypted = $this->encryptionService->encrypt($originalString, $key);
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($originalString, $encrypted);

        // 解密
        $decrypted = $this->encryptionService->decrypt($encrypted, $key);
        $this->assertEquals($originalString, $decrypted);
    }

    /**
     * 测试数组加密和解密
     */
    public function test_array_encryption_decryption(): void
    {
        $originalArray = [
            'user_id' => 123,
            'email' => 'test@example.com',
            'role' => 'admin',
            'permissions' => ['read', 'write', 'delete']
        ];
        $key = 'array_encryption_key';

        // 加密数组
        $encrypted = $this->encryptionService->encryptArray($originalArray, $key);
        $this->assertNotEmpty($encrypted);
        $this->assertIsString($encrypted);

        // 解密数组
        $decrypted = $this->encryptionService->decryptArray($encrypted, $key);
        $this->assertEquals($originalArray, $decrypted);
    }

    /**
     * 测试JSON数据加密和解密
     */
    public function test_json_encryption_decryption(): void
    {
        $jsonData = [
            'order_id' => 'ORD-2025-001',
            'customer' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            'items' => [
                ['sku' => 'PROD-001', 'quantity' => 2, 'price' => 99.99],
                ['sku' => 'PROD-002', 'quantity' => 1, 'price' => 149.99]
            ],
            'total' => 349.97
        ];
        $key = 'json_encryption_key';

        // 加密JSON
        $encrypted = $this->encryptionService->encryptJson($jsonData, $key);
        $this->assertNotEmpty($encrypted);

        // 解密JSON
        $decrypted = $this->encryptionService->decryptJson($encrypted, $key);
        $this->assertEquals($jsonData, $decrypted);
    }

    /**
     * 测试密码哈希和验证
     */
    public function test_password_hashing_and_verification(): void
    {
        $password = 'MySecurePassword123!';

        // 哈希密码
        $hashedPassword = $this->encryptionService->hashPassword($password);
        $this->assertNotEmpty($hashedPassword);
        $this->assertNotEquals($password, $hashedPassword);
        $this->assertStringStartsWith('$2y$', $hashedPassword); // bcrypt格式

        // 验证密码
        $isValid = $this->encryptionService->verifyPassword($password, $hashedPassword);
        $this->assertTrue($isValid);

        // 验证错误密码
        $isInvalid = $this->encryptionService->verifyPassword('WrongPassword', $hashedPassword);
        $this->assertFalse($isInvalid);
    }

    /**
     * 测试API密钥生成
     */
    public function test_api_key_generation(): void
    {
        $apiKey = $this->encryptionService->generateApiKey();

        $this->assertNotEmpty($apiKey);
        $this->assertEquals(32, strlen($apiKey)); // 默认长度32
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $apiKey);

        // 测试自定义长度
        $customKey = $this->encryptionService->generateApiKey(64);
        $this->assertEquals(64, strlen($customKey));
    }

    /**
     * 测试安全令牌生成
     */
    public function test_secure_token_generation(): void
    {
        $token = $this->encryptionService->generateSecureToken();

        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // 默认长度64
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9\-_]+$/', $token);
    }

    /**
     * 测试JWT令牌签名和验证
     */
    public function test_jwt_token_signing_and_verification(): void
    {
        $payload = [
            'user_id' => 123,
            'username' => 'testuser',
            'role' => 'user',
            'exp' => time() + 3600 // 1小时后过期
        ];
        $secret = 'jwt_secret_key';

        // 签名令牌
        $token = $this->encryptionService->signJwtToken($payload, $secret);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token); // JWT格式包含点

        // 验证令牌
        $verifiedPayload = $this->encryptionService->verifyJwtToken($token, $secret);
        $this->assertIsArray($verifiedPayload);
        $this->assertEquals($payload['user_id'], $verifiedPayload['user_id']);
        $this->assertEquals($payload['username'], $verifiedPayload['username']);
    }

    /**
     * 测试过期JWT令牌验证
     */
    public function test_expired_jwt_token_verification(): void
    {
        $payload = [
            'user_id' => 123,
            'exp' => time() - 3600 // 1小时前过期
        ];
        $secret = 'jwt_secret_key';

        // 签名过期令牌
        $token = $this->encryptionService->signJwtToken($payload, $secret);

        // 验证过期令牌应该返回false
        $verifiedPayload = $this->encryptionService->verifyJwtToken($token, $secret);
        $this->assertFalse($verifiedPayload);
    }

    /**
     * 测试错误密钥的JWT令牌验证
     */
    public function test_jwt_token_with_wrong_secret(): void
    {
        $payload = [
            'user_id' => 123,
            'exp' => time() + 3600
        ];
        $correctSecret = 'correct_secret';
        $wrongSecret = 'wrong_secret';

        // 使用正确密钥签名
        $token = $this->encryptionService->signJwtToken($payload, $correctSecret);

        // 使用错误密钥验证
        $verifiedPayload = $this->encryptionService->verifyJwtToken($token, $wrongSecret);
        $this->assertFalse($verifiedPayload);
    }

    /**
     * 测试数据完整性哈希
     */
    public function test_data_integrity_hash(): void
    {
        $data = 'Important data that needs integrity check';
        $hash = $this->encryptionService->hashData($data);

        $this->assertNotEmpty($hash);
        $this->assertEquals(64, strlen($hash)); // SHA-256输出64个字符

        // 验证数据完整性
        $isValid = $this->encryptionService->verifyDataIntegrity($data, $hash);
        $this->assertTrue($isValid);

        // 修改数据后验证应该失败
        $isInvalid = $this->encryptionService->verifyDataIntegrity('Modified data', $hash);
        $this->assertFalse($isInvalid);
    }

    /**
     * 测试敏感数据掩码
     */
    public function test_sensitive_data_masking(): void
    {
        $email = 'user@example.com';
        $phone = '+81-1234-5678';
        $creditCard = '1234-5678-9012-3456';

        // 掩码邮箱
        $maskedEmail = $this->encryptionService->maskEmail($email);
        $this->assertEquals('u***@example.com', $maskedEmail);

        // 掩码电话号码
        $maskedPhone = $this->encryptionService->maskPhone($phone);
        $this->assertEquals('+81-****-5678', $maskedPhone);

        // 掩码信用卡号
        $maskedCard = $this->encryptionService->maskCreditCard($creditCard);
        $this->assertEquals('****-****-****-3456', $maskedCard);
    }

    /**
     * 测试随机字符串生成
     */
    public function test_random_string_generation(): void
    {
        $randomString = $this->encryptionService->generateRandomString(16);

        $this->assertEquals(16, strlen($randomString));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $randomString);

        // 生成多个随机字符串确保唯一性
        $strings = [];
        for ($i = 0; $i < 10; $i++) {
            $strings[] = $this->encryptionService->generateRandomString(16);
        }

        $uniqueStrings = array_unique($strings);
        $this->assertEquals(10, count($uniqueStrings)); // 所有字符串应该是唯一的
    }

    /**
     * 测试加密密钥派生
     */
    public function test_key_derivation(): void
    {
        $password = 'user_password_123';
        $salt = 'random_salt_value';

        $derivedKey = $this->encryptionService->deriveKey($password, $salt);

        $this->assertNotEmpty($derivedKey);
        $this->assertEquals(32, strlen($derivedKey)); // 固定长度32字节

        // 相同密码和盐应该产生相同的密钥
        $derivedKey2 = $this->encryptionService->deriveKey($password, $salt);
        $this->assertEquals($derivedKey, $derivedKey2);

        // 不同盐应该产生不同的密钥
        $derivedKey3 = $this->encryptionService->deriveKey($password, 'different_salt');
        $this->assertNotEquals($derivedKey, $derivedKey3);
    }

    /**
     * 测试错误处理 - 无效加密数据
     */
    public function test_invalid_encrypted_data_handling(): void
    {
        $key = 'test_key';
        $invalidData = 'invalid_encrypted_string';

        // 尝试解密无效数据应该返回false或null
        $result = $this->encryptionService->decrypt($invalidData, $key);
        $this->assertFalse($result);
    }

    /**
     * 测试错误处理 - 空数据
     */
    public function test_empty_data_handling(): void
    {
        $key = 'test_key';

        // 加密空字符串
        $encrypted = $this->encryptionService->encrypt('', $key);
        $this->assertNotEmpty($encrypted);

        // 解密空字符串
        $decrypted = $this->encryptionService->decrypt($encrypted, $key);
        $this->assertEquals('', $decrypted);
    }

    /**
     * 测试错误处理 - 无效哈希密码验证
     */
    public function test_invalid_hash_password_verification(): void
    {
        $password = 'test_password';
        $invalidHash = 'invalid_hash_format';

        // 验证无效哈希格式应该返回false
        $result = $this->encryptionService->verifyPassword($password, $invalidHash);
        $this->assertFalse($result);
    }

    /**
     * 测试性能 - 大数据加密
     */
    public function test_large_data_encryption_performance(): void
    {
        $largeData = str_repeat('Large data string for performance testing. ', 1000);
        $key = 'performance_test_key';

        $startTime = microtime(true);
        
        // 加密大数据
        $encrypted = $this->encryptionService->encrypt($largeData, $key);
        $encryptTime = microtime(true) - $startTime;

        $startTime = microtime(true);
        
        // 解密大数据
        $decrypted = $this->encryptionService->decrypt($encrypted, $key);
        $decryptTime = microtime(true) - $startTime;

        $this->assertEquals($largeData, $decrypted);
        $this->assertLessThan(1.0, $encryptTime, 'Encryption should complete within 1 second');
        $this->assertLessThan(1.0, $decryptTime, 'Decryption should complete within 1 second');
    }
}