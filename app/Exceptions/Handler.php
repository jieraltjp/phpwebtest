<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;
use App\Services\ApiResponseService;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception): Response|JsonResponse
    {
        // API请求的异常处理
        if ($request->expectsJson()) {
            return $this->handleApiException($exception);
        }

        // Web请求的异常处理
        return parent::render($request, $exception);
    }

    /**
     * 处理API异常
     */
    protected function handleApiException(Throwable $exception): JsonResponse
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof ValidationException) {
            return ApiResponseService::validationError($exception->errors());
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponseService::unauthorized('认证失败，请重新登录');
        }

        if ($exception instanceof AuthorizationException) {
            return ApiResponseService::forbidden('权限不足，无法访问此资源');
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return ApiResponseService::notFound('请求的资源不存在');
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return ApiResponseService::error('请求方法不被允许', null, 405);
        }

        if ($exception instanceof HttpException) {
            return ApiResponseService::error($exception->getMessage(), null, $exception->getStatusCode());
        }

        // 记录未知异常
        logger()->error('API异常: ' . $exception->getMessage(), [
            'exception' => $exception,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_id' => auth()->id(),
        ]);

        return ApiResponseService::serverError('服务器内部错误，请稍后重试');
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|Response
    {
        return $request->expectsJson()
            ? ApiResponseService::unauthorized('认证失败，请重新登录')
            : redirect()->guest(route('login'));
    }
}