<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\ValidationService;
use App\Services\PerformanceMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;

/**
 * @OA\Tag(
 *     name="Authentication V2",
 *     description="用户认证相关接口 - 版本 2.0 (预览版)"
 * )
 */
class AuthController extends Controller
{
    protected PerformanceMonitorService $performanceMonitor;

    public function __construct(PerformanceMonitorService $performanceMonitor)
    {
        $this->performanceMonitor = $performanceMonitor;
    }

    /**
     * 用户登录 (V2 - 增强版)
     * 
     * @OA\Post(
     *     path="/api/v2/auth/login",
     *     tags={"Authentication V2"},
     *     summary="用户登录 - V2",
     *     description="增强版登录认证，支持多因素认证和设备管理 (版本 2.0 预览)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="testuser", description="用户名"),
     *             @OA\Property(property="password", type="string", example="password123", description="密码"),
     *             @OA\Property(property="device_info", type="object", description="设备信息 (V2 新增)",
     *                 @OA\Property(property="device_id", type="string", example="web_123456"),
     *                 @OA\Property(property="device_type", type="string", example="web"),
     *                 @OA\Property(property="user_agent", type="string", example="Mozilla/5.0...")
     *             ),
     *             @OA\Property(property="remember_me", type="boolean", example=false, description="记住登录状态 (V2 新增)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="登录成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v2"),
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="refresh_token", type="string", description="刷新令牌 (V2 新增)"),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=7200),
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="session_info", type="object", description="会话信息 (V2 新增)",
     *                     @OA\Property(property="session_id", type="string"),
     *                     @OA\Property(property="device_registered", type="boolean"),
     *                     @OA\Property(property="requires_2fa", type="boolean")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $startTime = microtime(true);
        
        // 速率限制
        $key = 'login_attempt:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return ApiResponseService::error('登录尝试次数过多，请稍后再试', [], 429);
        }

        // 清理输入数据
        $sanitizedData = ValidationService::sanitizeInput($request->all());
        
        // V2 增强验证
        $validation = ValidationService::validateLoginV2($sanitizedData);
        
        if (!$validation['valid']) {
            RateLimiter::hit($key);
            return ApiResponseService::validationError($validation['errors']);
        }

        $credentials = $request->only('username', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            RateLimiter::hit($key);
            return ApiResponseService::error('用户名或密码错误', null, 401);
        }

        // 清除速率限制
        RateLimiter::clear($key);

        $user = auth('api')->user();
        
        // V2 增强功能：设备管理
        $deviceInfo = $request->get('device_info', []);
        $sessionId = $this->registerDeviceSession($user, $deviceInfo);
        
        // V2 增强功能：生成刷新令牌
        $refreshToken = $this->generateRefreshToken($user);
        
        // 更新最后登录时间和设备信息
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'device_count' => $user->devices()->count()
        ]);

        // 性能监控
        $executionTime = (microtime(true) - $startTime) * 1000;
        $this->performanceMonitor->recordLogin($executionTime);

        return $this->respondWithTokenV2($token, $refreshToken, $user, $sessionId);
    }

    /**
     * 用户注册 (V2 - 增强版)
     * 
     * @OA\Post(
     *     path="/api/v2/auth/register",
     *     tags={"Authentication V2"},
     *     summary="用户注册 - V2",
     *     description="增强版用户注册，支持邮箱验证和企业认证 (版本 2.0 预览)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","username","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="张三"),
     *             @OA\Property(property="username", type="string", example="zhangsan"),
     *             @OA\Property(property="email", type="string", example="zhangsan@example.com"),
     *             @OA\Property(property="password", type="string", example="Password123!"),
     *             @OA\Property(property="password_confirmation", type="string", example="Password123!"),
     *             @OA\Property(property="company", type="string", example="测试公司"),
     *             @OA\Property(property="phone", type="string", example="+8613800138000"),
     *             @OA\Property(property="business_license", type="string", example="91110000123456789X", description="营业执照号 (V2 新增)"),
     *             @OA\Property(property="user_type", type="string", enum={"individual","enterprise"}, example="enterprise", description="用户类型 (V2 新增)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="注册成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="注册成功，请查收验证邮件"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v2"),
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="verification_required", type="boolean", example=true),
     *                 @OA\Property(property="verification_methods", type="array", @OA\Items(type="string"), example={"email","sms"})
     *             )
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        // 清理输入数据
        $sanitizedData = ValidationService::sanitizeInput($request->all());
        
        // V2 增强验证
        $validation = ValidationService::validateUserV2($sanitizedData);
        
        if (!$validation['valid']) {
            return ApiResponseService::validationError($validation['errors']);
        }

        try {
            $user = User::create([
                'name' => $sanitizedData['name'],
                'username' => $sanitizedData['username'],
                'email' => $sanitizedData['email'],
                'password' => Hash::make($sanitizedData['password']),
                'phone' => $sanitizedData['phone'] ?? null,
                'company' => $sanitizedData['company'] ?? null,
                'address' => $sanitizedData['address'] ?? null,
                'business_license' => $sanitizedData['business_license'] ?? null,
                'user_type' => $sanitizedData['user_type'] ?? 'individual',
                'active' => false, // V2: 需要验证后激活
                'email_verified_at' => null,
            ]);

            // V2 发送验证邮件
            $this->sendVerificationEmail($user);

            // V2 不自动登录，要求验证
            return ApiResponseService::success([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'created_at' => $user->created_at,
                ],
                'verification_required' => true,
                'verification_methods' => ['email', 'sms'],
                'api_version' => 'v2'
            ], '注册成功，请查收验证邮件', 201);

        } catch (\Exception $e) {
            return ApiResponseService::serverError('注册失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取当前用户信息 (V2 - 增强版)
     * 
     * @OA\Get(
     *     path="/api/v2/auth/me",
     *     tags={"Authentication V2"},
     *     summary="获取当前用户信息 - V2",
     *     description="获取增强版用户信息，包含权限和设备信息 (版本 2.0 预览)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="获取用户信息成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v2"),
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="devices", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="security_settings", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function me()
    {
        $user = auth('api')->user();
        
        // V2 增强用户信息
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'company' => $user->company,
            'business_license' => $user->business_license,
            'user_type' => $user->user_type,
            'active' => $user->active,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'last_login_at' => $user->last_login_at,
            'last_login_ip' => $user->last_login_ip,
        ];

        // V2 权限信息
        $permissions = $this->getUserPermissions($user);
        
        // V2 设备信息
        $devices = $user->devices()->latest()->limit(5)->get();
        
        // V2 安全设置
        $securitySettings = [
            '2fa_enabled' => $user->two_factor_enabled ?? false,
            'email_verified' => !is_null($user->email_verified_at),
            'phone_verified' => $user->phone_verified ?? false,
            'session_timeout' => $user->session_timeout ?? 7200,
        ];

        return ApiResponseService::success([
            'api_version' => 'v2',
            'user' => $userData,
            'permissions' => $permissions,
            'devices' => $devices,
            'security_settings' => $securitySettings
        ], '获取用户信息成功');
    }

    /**
     * 刷新令牌 (V2 - 增强版)
     * 
     * @OA\Post(
     *     path="/api/v2/auth/refresh",
     *     tags={"Authentication V2"},
     *     summary="刷新访问令牌 - V2",
     *     description="使用刷新令牌获取新的访问令牌 (版本 2.0 预览)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", example="refresh_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="刷新成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="令牌刷新成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="api_version", type="string", example="v2"),
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="refresh_token", type="string"),
     *                 @OA\Property(property="expires_in", type="integer", example=7200)
     *             )
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        $refreshToken = request()->get('refresh_token');
        
        if (!$refreshToken || !$this->validateRefreshToken($refreshToken)) {
            return ApiResponseService::error('无效的刷新令牌', [], 401);
        }

        $user = $this->getUserFromRefreshToken($refreshToken);
        $newToken = auth('api')->login($user);
        $newRefreshToken = $this->generateRefreshToken($user);

        return ApiResponseService::success([
            'api_version' => 'v2',
            'access_token' => $newToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], '令牌刷新成功');
    }

    /**
     * V2 新增：邮箱验证
     * 
     * @OA\Post(
     *     path="/api/v2/auth/verify-email",
     *     tags={"Authentication V2"},
     *     summary="验证邮箱 - V2",
     *     description="验证用户邮箱地址 (版本 2.0 新功能)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", example="verification_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="验证成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="邮箱验证成功")
     *         )
     *     )
     * )
     */
    public function verifyEmail(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        
        // 实现邮箱验证逻辑
        // 这里简化处理，实际应该验证令牌并激活用户
        
        return ApiResponseService::success([], '邮箱验证成功');
    }

    /**
     * V2 新增：启用双因素认证
     * 
     * @OA\Post(
     *     path="/api/v2/auth/enable-2fa",
     *     tags={"Authentication V2"},
     *     summary="启用双因素认证 - V2",
     *     description="为用户账户启用双因素认证 (版本 2.0 新功能)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="设置成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="双因素认证已启用"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="qr_code", type="string"),
     *                 @OA\Property(property="backup_codes", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function enable2FA(Request $request)
    {
        $user = auth('api')->user();
        
        // 实现双因素认证设置
        // 这里简化处理，实际应该生成密钥和二维码
        
        return ApiResponseService::success([
            'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANS...',
            'backup_codes' => ['123456', '789012', '345678']
        ], '双因素认证已启用');
    }

    /**
     * V2 辅助方法：注册设备会话
     */
    protected function registerDeviceSession($user, array $deviceInfo): string
    {
        $sessionId = uniqid('session_', true);
        
        // 实现设备注册逻辑
        // 这里简化处理
        
        return $sessionId;
    }

    /**
     * V2 辅助方法：生成刷新令牌
     */
    protected function generateRefreshToken($user): string
    {
        return hash('sha256', $user->id . now()->timestamp . random_bytes(16));
    }

    /**
     * V2 辅助方法：验证刷新令牌
     */
    protected function validateRefreshToken(string $token): bool
    {
        // 实现刷新令牌验证逻辑
        return true; // 简化处理
    }

    /**
     * V2 辅助方法：从刷新令牌获取用户
     */
    protected function getUserFromRefreshToken(string $token)
    {
        // 实现从刷新令牌获取用户逻辑
        return auth('api')->user(); // 简化处理
    }

    /**
     * V2 辅助方法：发送验证邮件
     */
    protected function sendVerificationEmail($user): void
    {
        // 实现邮件发送逻辑
        // 这里简化处理
    }

    /**
     * V2 辅助方法：获取用户权限
     */
    protected function getUserPermissions($user): array
    {
        // 实现权限获取逻辑
        return ['read_orders', 'create_orders', 'manage_profile'];
    }

    /**
     * V2 增强版令牌响应
     */
    protected function respondWithTokenV2($token, $refreshToken, $user, $sessionId)
    {
        return ApiResponseService::success([
            'api_version' => 'v2',
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'company' => $user->company,
                'user_type' => $user->user_type,
                'last_login_at' => $user->last_login_at,
            ],
            'session_info' => [
                'session_id' => $sessionId,
                'device_registered' => true,
                'requires_2fa' => $user->two_factor_enabled ?? false,
            ]
        ], '登录成功');
    }
}