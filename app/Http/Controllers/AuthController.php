<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * 显示认证页面
     */
    public function showAuthPage()
    {
        return view('auth');
    }

    /**
     * 显示登录页面
     */
    public function showLoginPage()
    {
        return view('auth');
    }

    /**
     * 显示注册页面
     */
    public function showRegisterPage()
    {
        return view('auth');
    }

    /**
     * 处理用户退出
     */
    public function logout(Request $request)
    {
        try {
            // 清除本地存储的令牌
            $request->session()->forget('access_token');
            $request->session()->forget('user');
            
            return ApiResponseService::success(null, '退出登录成功');
        } catch (\Exception $e) {
            return ApiResponseService::serverError('退出登录失败');
        }
    }
}