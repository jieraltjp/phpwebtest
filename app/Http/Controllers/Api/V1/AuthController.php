<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\AuthController as BaseAuthController;
use App\Services\ApiResponseService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Authentication V1",
 *     description="用户认证相关接口 - 版本 1.0"
 * )
 */
class AuthController extends BaseAuthController
{
    /**
     * 用户登录 (V1)
     * 
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Authentication V1"},
     *     summary="用户登录 - V1",
     *     description="使用用户名和密码进行登录认证 (版本 1.0)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="testuser", description="用户名"),
     *             @OA\Property(property="password", type="string", example="password123", description="密码")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="登录成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="测试用户"),
     *                     @OA\Property(property="username", type="string", example="testuser"),
     *                     @OA\Property(property="email", type="string", example="test@example.com"),
     *                     @OA\Property(property="company", type="string", example="测试公司")
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        // 调用父类方法，保持 V1 兼容性
        $response = parent::login($request);
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $data['data']['deprecated'] = false;
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 用户注册 (V1)
     * 
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"Authentication V1"},
     *     summary="用户注册 - V1",
     *     description="创建新用户账户 (版本 1.0)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","username","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="张三", description="用户姓名"),
     *             @OA\Property(property="username", type="string", example="zhangsan", description="用户名"),
     *             @OA\Property(property="email", type="string", example="zhangsan@example.com", description="邮箱地址"),
     *             @OA\Property(property="password", type="string", example="Password123!", description="密码"),
     *             @OA\Property(property="password_confirmation", type="string", example="Password123!", description="确认密码"),
     *             @OA\Property(property="company", type="string", example="测试公司", description="公司名称（可选）")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="注册成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="注册成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="token_type", type="string"),
     *                 @OA\Property(property="expires_in", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $response = parent::register($request);
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 201) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $data['data']['migration_notes'] = 'V1 API is stable and fully supported';
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 获取当前用户信息 (V1)
     * 
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     tags={"Authentication V1"},
     *     summary="获取当前用户信息 - V1",
     *     description="获取当前登录用户的详细信息 (版本 1.0)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="获取用户信息成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="测试用户"),
     *                 @OA\Property(property="username", type="string", example="testuser"),
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="company", type="string", example="测试公司")
     *             )
     *         )
     *     )
     * )
     */
    public function me()
    {
        $response = parent::me();
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $data['data']['version_info'] = [
                'stable' => true,
                'deprecated' => false,
                'sunset_date' => null,
                'recommended_migration' => null
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 退出登录 (V1)
     * 
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Authentication V1"},
     *     summary="退出登录 - V1",
     *     description="退出当前用户的登录状态 (版本 1.0)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="退出成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="退出登录成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1")
     *             )
     *         )
     *     )
     * )
     */
    public function logout()
    {
        $response = parent::logout();
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data'] = [
                'api_version' => 'v1',
                'message' => 'V1 logout successful'
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 刷新令牌 (V1)
     * 
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     tags={"Authentication V1"},
     *     summary="刷新访问令牌 - V1",
     *     description="使用当前令牌获取新的访问令牌 (版本 1.0)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="刷新成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="登录成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="token_type", type="string"),
     *                 @OA\Property(property="expires_in", type="integer"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        $response = parent::refresh();
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $data['data']['token_info'] = [
                'format' => 'JWT',
                'algorithm' => 'HS256',
                'version' => 'v1'
            ];
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 检查用户名是否可用 (V1)
     * 
     * @OA\Post(
     *     path="/api/v1/auth/check-username",
     *     tags={"Authentication V1"},
     *     summary="检查用户名可用性 - V1",
     *     description="检查用户名是否已被注册 (版本 1.0)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username"},
     *             @OA\Property(property="username", type="string", example="testuser", description="要检查的用户名")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="检查成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="available", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="用户名可用")
     *             )
     *         )
     *     )
     * )
     */
    public function checkUsername(Request $request)
    {
        $response = parent::checkUsername($request);
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }

    /**
     * 检查邮箱是否可用 (V1)
     * 
     * @OA\Post(
     *     path="/api/v1/auth/check-email",
     *     tags={"Authentication V1"},
     *     summary="检查邮箱可用性 - V1",
     *     description="检查邮箱是否已被注册 (版本 1.0)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="test@example.com", description="要检查的邮箱地址")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="检查成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="available", type="boolean", example=false),
     *                 @OA\Property(property="message", type="string", example="邮箱已被注册")
     *             )
     *         )
     *     )
     * )
     */
    public function checkEmail(Request $request)
    {
        $response = parent::checkEmail($request);
        
        // 添加 V1 特定信息
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $data['data']['api_version'] = 'v1';
            $response->setContent(json_encode($data));
        }
        
        return $response;
    }
}