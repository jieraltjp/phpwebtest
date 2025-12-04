<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EncryptionService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EncryptionController extends Controller
{
    /**
     * 获取加密系统状态
     */
    public function status(): JsonResponse
    {
        try {
            $validation = EncryptionService::validateEncryptionSystem();
            $info = EncryptionService::getEncryptionInfo();
            
            return ApiResponseService::success([
                'validation' => $validation,
                'info' => $info,
                'timestamp' => now()->toISOString(),
            ], '加密系统状态获取成功');
            
        } catch (\Exception $e) {
            return ApiResponseService::serverError('获取加密系统状态失败: ' . $e->getMessage());
        }
    }

    /**
     * 测试加密解密功能
     */
    public function test(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'data' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        try {
            $originalData = $request->get('data');
            
            // 加密
            $encrypted = EncryptionService::encrypt($originalData);
            
            // 解密
            $decrypted = EncryptionService::decrypt($encrypted);
            
            // 生成掩码
            $masked = EncryptionService::maskSensitiveData($originalData);
            
            return ApiResponseService::success([
                'original' => $originalData,
                'encrypted' => $encrypted,
                'decrypted' => $decrypted,
                'masked' => $masked,
                'encryption_success' => $decrypted === $originalData,
                'timestamp' => now()->toISOString(),
            ], '加密测试完成');
            
        } catch (\Exception $e) {
            return ApiResponseService::serverError('加密测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量加密数据
     */
    public function encryptBatch(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.field' => 'required|string',
            'data.*.value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        try {
            $data = $request->get('data');
            $results = [];
            
            foreach ($data as $item) {
                $field = $item['field'];
                $value = $item['value'];
                
                try {
                    $encrypted = EncryptionService::encrypt($value);
                    $results[] = [
                        'field' => $field,
                        'original' => $value,
                        'encrypted' => $encrypted,
                        'success' => true,
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'field' => $field,
                        'original' => $value,
                        'error' => $e->getMessage(),
                        'success' => false,
                    ];
                }
            }
            
            return ApiResponseService::success([
                'results' => $results,
                'total_processed' => count($results),
                'successful' => count(array_filter($results, fn($r) => $r['success'])),
                'failed' => count(array_filter($results, fn($r) => !$r['success'])),
            ], '批量加密完成');
            
        } catch (\Exception $e) {
            return ApiResponseService::serverError('批量加密失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成安全令牌
     */
    public function generateToken(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'length' => 'integer|min:8|max:64',
            'type' => 'in:secure,api_key',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        try {
            $length = $request->get('length', 32);
            $type = $request->get('type', 'secure');
            
            if ($type === 'api_key') {
                $token = EncryptionService::generateApiKey();
            } else {
                $token = EncryptionService::generateSecureToken($length);
            }
            
            return ApiResponseService::success([
                'token' => $token,
                'type' => $type,
                'length' => strlen($token),
                'generated_at' => now()->toISOString(),
            ], '令牌生成成功');
            
        } catch (\Exception $e) {
            return ApiResponseService::serverError('令牌生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 数据掩码处理
     */
    public function maskData(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.value' => 'required|string',
            'data.*.type' => 'in:default,email,phone,bank_card,id_card',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        try {
            $data = $request->get('data');
            $results = [];
            
            foreach ($data as $item) {
                $value = $item['value'];
                $type = $item['type'] ?? 'default';
                
                $masked = EncryptionService::maskSensitiveData($value, $type);
                
                $results[] = [
                    'original' => $value,
                    'masked' => $masked,
                    'type' => $type,
                ];
            }
            
            return ApiResponseService::success([
                'results' => $results,
                'total_processed' => count($results),
            ], '数据掩码处理完成');
            
        } catch (\Exception $e) {
            return ApiResponseService::serverError('数据掩码处理失败: ' . $e->getMessage());
        }
    }

    /**
     * 验证密码哈希
     */
    public function verifyHash(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'password' => 'required|string',
            'hash' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        try {
            $password = $request->get('password');
            $hash = $request->get('hash');
            
            $isValid = EncryptionService::verifyHash($password, $hash);
            
            return ApiResponseService::success([
                'valid' => $isValid,
                'password_length' => strlen($password),
                'hash_length' => strlen($hash),
            ], '密码验证完成');
            
        } catch (\Exception $e) {
            return ApiResponseService::serverError('密码验证失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成密码哈希
     */
    public function generateHash(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'password' => 'required|string|min:6|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        try {
            $password = $request->get('password');
            $hash = EncryptionService::hash($password);
            
            return ApiResponseService::success([
                'password_length' => strlen($password),
                'hash' => $hash,
                'hash_length' => strlen($hash),
                'algorithm' => 'Argon2ID',
            ], '密码哈希生成成功');
            
        } catch (\Exception $e) {
            return ApiResponseService::serverError('密码哈希生成失败: ' . $e->getMessage());
        }
    }
}