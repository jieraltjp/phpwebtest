<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtMiddleware::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
            'api.throttle' => \App\Http\Middleware\ApiThrottle::class,
            'api.version' => \App\Http\Middleware\ApiVersionMiddleware::class,
        ]);
        
        // 配置 API 路由的中间件
        $middleware->group('api', [
            \App\Http\Middleware\CorsMiddleware::class,
            \App\Http\Middleware\ApiThrottle::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
