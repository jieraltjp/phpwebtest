<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\ValidationService;
use App\Services\EventService;
use App\Events\User\UserLoggedInEvent;
use App\Events\User\UserRegisteredEvent;
use App\Events\User\UserUpdatedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="用户认证相关接口"
 * )
 */

class AuthController extends Controller
{
    /**
     * 用户登录
     * 
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Authentication"},
     *     summary="用户登录",
     *     description="使用用户名和密码进行登录认证",
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
     *                     @OA\Property(property="company", type="string", example="测试公司"),
     *                     @OA\Property(property="created_at", type="string", example="2025-12-03T13:50:34.000000Z"),
     *                     @OA\Property(property="last_login_at", type="string", example="2025-12-04T10:30:00.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="认证失败",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="用户名或密码错误"),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="验证失败",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"username": {"用户名是必填字段"}}),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        // 清理输入数据
        $sanitizedData = ValidationService::sanitizeInput($request->all());
        
        // 验证数据
        $validation = ValidationService::validateLogin($sanitizedData);
        
        if (!$validation['valid']) {
            return ApiResponseService::validationError($validation['errors']);
        }

        $credentials = $request->only('username', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return ApiResponseService::error('用户名或密码错误', null, 401);
        }

        // 更新最后登录时间
        $user = auth('api')->user();
        $user->last_login_at = now();
        $user->save();

        // 触发用户登录事件
        try {
            EventService::dispatch(new UserLoggedInEvent($user));
        } catch (\Exception $e) {
            // 事件失败不影响登录流程
            \Log::warning('UserLoggedInEvent failed to dispatch', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return $this->respondWithToken($token, $user);
    }

    /**
     * 用户注册
     * 
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Authentication"},
     *     summary="用户注册",
     *     description="创建新用户账户",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","username","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="张三", description="用户姓名"),
     *             @OA\Property(property="username", type="string", example="zhangsan", description="用户名"),
     *             @OA\Property(property="email", type="string", example="zhangsan@example.com", description="邮箱地址"),
     *             @OA\Property(property="password", type="string", example="Password123!", description="密码"),
     *             @OA\Property(property="password_confirmation", type="string", example="Password123!", description="确认密码"),
     *             @OA\Property(property="company", type="string", example="测试公司", description="公司名称（可选）"),
     *             @OA\Property(property="phone", type="string", example="+8613800138000", description="电话号码（可选）"),
     *             @OA\Property(property="address", type="string", example="北京市朝阳区", description="地址（可选）")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="注册成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="注册成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="张三"),
     *                     @OA\Property(property="username", type="string", example="zhangsan"),
     *                     @OA\Property(property="email", type="string", example="zhangsan@example.com"),
     *                     @OA\Property(property="company", type="string", example="测试公司"),
     *                     @OA\Property(property="created_at", type="string", example="2025-12-04T10:30:00.000000Z"),
     *                     @OA\Property(property="last_login_at", type="string", example="2025-12-04T10:30:00.000000Z")
     *                 ),
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="验证失败",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"email": {"邮箱已被使用"}}),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        // 清理输入数据
        $sanitizedData = ValidationService::sanitizeInput($request->all());
        
        // 验证数据
        $validation = ValidationService::validateUser($sanitizedData);
        
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
                'active' => true,
            ]);

            // 触发用户注册事件
            try {
                EventService::dispatch(new UserRegisteredEvent($user));
            } catch (\Exception $e) {
                // 事件失败不影响注册流程
                \Log::warning('UserRegisteredEvent failed to dispatch', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            // 自动登录并返回令牌
            $token = auth('api')->login($user);

            return ApiResponseService::success([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ], '注册成功', 201);

        } catch (\Exception $e) {
            return ApiResponseService::serverError('注册失败: ' . $e->getMessage());
        }
    }

    /**
     * 检查用户名是否可用
     * 
     * @OA\Post(
     *     path="/api/auth/check-username",
     *     tags={"Authentication"},
     *     summary="检查用户名可用性",
     *     description="检查用户名是否已被注册",
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
     *                 @OA\Property(property="available", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="用户名可用")
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     )
     * )
     */
    public function checkUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:50'
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        $exists = User::where('username', $request->username)->exists();
        
        return ApiResponseService::success([
            'available' => !$exists,
            'message' => $exists ? '用户名已被使用' : '用户名可用'
        ]);
    }

    /**
     * 检查邮箱是否可用
     * 
     * @OA\Post(
     *     path="/api/auth/check-email",
     *     tags={"Authentication"},
     *     summary="检查邮箱可用性",
     *     description="检查邮箱是否已被注册",
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
     *                 @OA\Property(property="available", type="boolean", example=false),
     *                 @OA\Property(property="message", type="string", example="邮箱已被注册")
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     )
     * )
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return ApiResponseService::validationError($validator->errors());
        }

        $exists = User::where('email', $request->email)->exists();
        
        return ApiResponseService::success([
            'available' => !$exists,
            'message' => $exists ? '邮箱已被注册' : '邮箱可用'
        ]);
    }

    /**
     * 获取当前用户信息
     * 
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Authentication"},
     *     summary="获取当前用户信息",
     *     description="获取当前登录用户的详细信息",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="获取用户信息成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="测试用户"),
     *                 @OA\Property(property="username", type="string", example="testuser"),
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="company", type="string", example="测试公司"),
     *                 @OA\Property(property="created_at", type="string", example="2025-12-03T13:50:34.000000Z"),
     *                 @OA\Property(property="last_login_at", type="string", example="2025-12-04T10:30:00.000000Z")
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="未认证",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="认证失败，请重新登录"),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     )
     * )
     */
    public function me()
    {
        $user = auth('api')->user();
        return ApiResponseService::success($user, '获取用户信息成功');
    }

    /**
     * 退出登录
     * 
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Authentication"},
     *     summary="退出登录",
     *     description="退出当前用户的登录状态",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="退出成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="退出登录成功"),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        auth('api')->logout();
        return ApiResponseService::success(null, '退出登录成功');
    }

    /**
     * 刷新令牌
     * 
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     tags={"Authentication"},
     *     summary="刷新访问令牌",
     *     description="使用当前令牌获取新的访问令牌",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="刷新成功",
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
     *                     @OA\Property(property="company", type="string", example="测试公司"),
     *                     @OA\Property(property="created_at", type="string", example="2025-12-03T13:50:34.000000Z"),
     *                     @OA\Property(property="last_login_at", type="string", example="2025-12-04T10:30:00.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-12-04T10:30:00.000000Z")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        $token = auth('api')->refresh();
        $user = auth('api')->user();
        return $this->respondWithToken($token, $user);
    }

    /**
     * @OA\SecurityScheme(
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     securityScheme="bearerAuth"
     * )
     */

    /**
     * 返回令牌信息
     */
    protected function respondWithToken($token, $user = null)
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
        
        if ($user) {
            $data['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'company' => $user->company,
                'created_at' => $user->created_at,
                'last_login_at' => $user->last_login_at,
            ];
        }
        
        return ApiResponseService::success($data, '登录成功');
    }
}