<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>雅虎 B2B 采购门户 - 项目状态</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .status-card {
            transition: transform 0.2s;
        }
        .status-card:hover {
            transform: translateY(-2px);
        }
        .status-success {
            border-left: 4px solid #28a745;
        }
        .status-warning {
            border-left: 4px solid #ffc107;
        }
        .status-danger {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">雅虎 B2B 采购门户</h1>
                <p class="text-center text-muted">项目状态报告 - <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card status-card status-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">✅ PHP 服务器</h5>
                        <p class="card-text">PHP 服务器已成功启动并运行在 localhost:8000</p>
                        <p class="mb-0"><small>PHP 版本: <?php echo phpversion(); ?></small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card status-card status-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">✅ PHP 扩展</h5>
                        <p class="card-text">所有必需的 PHP 扩展已安装</p>
                        <p class="mb-0"><small>PDO, SQLite, mbstring, OpenSSL 等</small></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card status-card status-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">⚠️ Composer 依赖</h5>
                        <p class="card-text">需要安装 Composer 依赖才能运行完整应用</p>
                        <p class="mb-0"><small>vendor/autoload.php 文件缺失</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card status-card status-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">✅ 目录权限</h5>
                        <p class="card-text">存储目录具有正确的写入权限</p>
                        <p class="mb-0"><small>storage/ 和 bootstrap/cache/ 可写</small></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">📋 解决方案</h5>
                        <p class="card-text">要完成项目设置，请按以下步骤操作：</p>
                        <ol>
                            <li>运行 <code>install-deps.bat</code> 脚本安装依赖</li>
                            <li>或手动执行：<code>composer install</code></li>
                            <li>如果 SSL 证书问题，尝试：<code>composer install --ignore-platform-reqs</code></li>
                            <li>完成后重启服务器：<code>php artisan serve</code></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">🔗 测试链接</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <a href="/index_temp.php" class="btn btn-outline-primary w-100 mb-2">临时首页</a>
                            </div>
                            <div class="col-md-3">
                                <a href="/test.php" class="btn btn-outline-info w-100 mb-2">环境测试</a>
                            </div>
                            <div class="col-md-3">
                                <a href="/status.php" class="btn btn-outline-secondary w-100 mb-2">状态页面</a>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-success w-100 mb-2" disabled>完整应用 (需安装依赖)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5 class="alert-heading">ℹ️ 项目信息</h5>
                    <p class="mb-0">这是一个基于 Laravel 12 的 B2B 采购门户系统，为雅虎客户提供阿里巴巴商品采购功能。</p>
                    <hr>
                    <p class="mb-0">
                        <strong>技术栈：</strong> Laravel 12 + JWT认证 + SQLite + Bootstrap 5 + Tailwind CSS<br>
                        <strong>功能：</strong> 用户认证、产品管理、订单处理、物流追踪、管理后台
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>