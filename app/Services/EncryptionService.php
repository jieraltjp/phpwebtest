<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;

class EncryptionService
{
    /**
     * 加密敏感数据
     */
    public static function encrypt(string $data): string
    {
        try {
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            Log::error('Data encryption failed: ' . $e->getMessage());
            throw new \RuntimeException('数据加密失败');
        }
    }

    /**
     * 解密敏感数据
     */
    public static function decrypt(string $encryptedData): string
    {
        try {
            return Crypt::decryptString($encryptedData);
        } catch (DecryptException $e) {
            Log::error('Data decryption failed: ' . $e->getMessage());
            throw new \RuntimeException('数据解密失败');
        } catch (\Exception $e) {
            Log::error('Unexpected decryption error: ' . $e->getMessage());
            throw new \RuntimeException('解密过程中发生错误');
        }
    }

    /**
     * 批量加密数组中的敏感字段
     */
    public static function encryptFields(array &$data, array $fields): void
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = self::encrypt($data[$field]);
            }
        }
    }

    /**
     * 批量解密数组中的敏感字段
     */
    public static function decryptFields(array &$data, array $fields): void
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $data[$field] = self::decrypt($data[$field]);
                } catch (\Exception $e) {
                    // 解密失败时保持原值，但记录错误
                    Log::warning("Failed to decrypt field {$field}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * 检查数据是否已加密
     */
    public static function isEncrypted(string $data): bool
    {
        try {
            // 尝试解密，如果成功则说明已加密
            self::decrypt($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 安全地获取解密数据
     */
    public static function safeDecrypt(?string $encryptedData, $default = null): ?string
    {
        if (empty($encryptedData)) {
            return $default;
        }

        try {
            return self::decrypt($encryptedData);
        } catch (\Exception $e) {
            Log::warning('Safe decryption failed, returning default: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * 生成加密哈希（用于密码等不可逆场景）
     */
    public static function hash(string $data): string
    {
        return password_hash($data, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * 验证哈希
     */
    public static function verifyHash(string $data, string $hash): bool
    {
        return password_verify($data, $hash);
    }

    /**
     * 生成随机安全令牌
     */
    public static function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * 生成API密钥
     */
    public static function generateApiKey(): string
    {
        $prefix = 'b2b_';
        $randomPart = bin2hex(random_bytes(16));
        $timestamp = time();
        
        return $prefix . $randomPart . '_' . $timestamp;
    }

    /**
     * 掩码处理敏感数据显示
     */
    public static function maskSensitiveData(string $data, string $type = 'default'): string
    {
        $length = strlen($data);
        
        switch ($type) {
            case 'email':
                // 邮箱掩码：u***@example.com
                $parts = explode('@', $data);
                if (count($parts) === 2) {
                    $username = $parts[0];
                    $domain = $parts[1];
                    $maskedUsername = substr($username, 0, 1) . str_repeat('*', max(1, strlen($username) - 1));
                    return $maskedUsername . '@' . $domain;
                }
                break;
                
            case 'phone':
                // 电话掩码：138****5678
                if ($length >= 7) {
                    return substr($data, 0, 3) . str_repeat('*', $length - 6) . substr($data, -3);
                }
                break;
                
            case 'bank_card':
                // 银行卡掩码：**** **** **** 1234
                if ($length >= 4) {
                    return str_repeat('*', $length - 4) . substr($data, -4);
                }
                break;
                
            case 'id_card':
                // 身份证掩码：110101********1234
                if ($length >= 8) {
                    return substr($data, 0, 6) . str_repeat('*', $length - 8) . substr($data, -4);
                }
                break;
                
            default:
                // 默认掩码：显示前后2位
                if ($length <= 4) {
                    return str_repeat('*', $length);
                }
                return substr($data, 0, 2) . str_repeat('*', $length - 4) . substr($data, -2);
        }
        
        return str_repeat('*', $length);
    }

    /**
     * 获取加密配置信息
     */
    public static function getEncryptionInfo(): array
    {
        return [
            'cipher' => config('app.cipher', 'AES-256-CBC'),
            'key_length' => strlen(config('app.key')),
            'algorithm' => 'Argon2ID (for passwords)',
            'secure' => config('app.env') === 'production',
        ];
    }

    /**
     * 验证加密系统状态
     */
    public static function validateEncryptionSystem(): array
    {
        $results = [
            'status' => 'unknown',
            'tests' => [],
            'errors' => []
        ];
        
        try {
            // 测试1: 基本加密解密
            $testData = 'test_encryption_' . time();
            $encrypted = self::encrypt($testData);
            $decrypted = self::decrypt($encrypted);
            
            if ($decrypted === $testData) {
                $results['tests']['basic_encryption'] = 'passed';
            } else {
                $results['tests']['basic_encryption'] = 'failed';
                $results['errors'][] = 'Basic encryption/decryption mismatch';
            }
            
            // 测试2: 哈希验证
            $password = 'test_password_' . time();
            $hash = self::hash($password);
            
            if (self::verifyHash($password, $hash)) {
                $results['tests']['hash_verification'] = 'passed';
            } else {
                $results['tests']['hash_verification'] = 'failed';
                $results['errors'][] = 'Password hash verification failed';
            }
            
            // 测试3: 令牌生成
            $token = self::generateSecureToken(16);
            if (strlen($token) === 32 && ctype_xdigit($token)) {
                $results['tests']['token_generation'] = 'passed';
            } else {
                $results['tests']['token_generation'] = 'failed';
                $results['errors'][] = 'Secure token generation failed';
            }
            
            // 测试4: 掩码功能
            $email = 'user@example.com';
            $masked = self::maskSensitiveData($email, 'email');
            if ($masked !== $email && strpos($masked, '@') !== false) {
                $results['tests']['data_masking'] = 'passed';
            } else {
                $results['tests']['data_masking'] = 'failed';
                $results['errors'][] = 'Data masking not working properly';
            }
            
            // 综合状态
            $failedTests = array_filter($results['tests'], function ($result) {
                return $result === 'failed';
            });
            
            $results['status'] = empty($failedTests) ? 'healthy' : 'degraded';
            
        } catch (\Exception $e) {
            $results['status'] = 'error';
            $results['errors'][] = 'Encryption system validation failed: ' . $e->getMessage();
        }
        
        return $results;
    }
}