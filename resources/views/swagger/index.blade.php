<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API 文档 - 雅虎B2B和风采购平台</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="/css/japanese-effects.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #C00000;
            --primary-orange: #ff6a00;
            --sakura-pink: #FFB7C5;
            --washi-white: #FFF8F0;
            --sumi-black: #2C2C2C;
            --light-gray: #F5F5F5;
            --medium-gray: #E0E0E0;
            --dark-gray: #333333;
            --text-gray: #666666;
            --gold-accent: #D4AF37;
            --bamboo-green: #4A7C59;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--washi-white);
            margin: 0;
            padding: 0;
            color: var(--sumi-black);
            line-height: 1.6;
        }

        /* 和风背景图案 */
        .japanese-pattern {
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(192, 0, 0, 0.02) 35px, rgba(192, 0, 0, 0.02) 70px),
                repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(255, 183, 197, 0.02) 35px, rgba(255, 183, 197, 0.02) 70px);
        }

        /* 高端顶部导航栏 */
        .top-navbar {
            background: linear-gradient(135deg, var(--sumi-black) 0%, #1a1a1a 100%);
            border-bottom: 2px solid var(--primary-red);
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .logo {
            font-family: 'Noto Serif JP', serif;
            font-size: 22px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            position: relative;
        }

        .logo::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gold-accent);
            transition: width 0.3s ease;
        }

        .logo:hover::after {
            width: 100%;
        }

        .logo span {
            color: var(--gold-accent);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* Swagger UI 自定义样式 */
        .swagger-ui {
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .swagger-ui .topbar {
            background: linear-gradient(135deg, var(--sumi-black) 0%, #1a1a1a 100%);
            border-bottom: 2px solid var(--primary-red);
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .swagger-ui .topbar .download-url-wrapper .select-label {
            color: white;
            font-weight: 500;
        }

        .swagger-ui .topbar .download-url-wrapper input[type="text"] {
            border: 2px solid var(--primary-red);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .swagger-ui .topbar .download-url-wrapper .select {
            border: 2px solid var(--primary-red);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .swagger-ui .info {
            margin: 30px 0;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }

        .swagger-ui .info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
        }

        .swagger-ui .info .title {
            color: var(--primary-red);
            font-family: 'Noto Serif JP', serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .swagger-ui .info .description {
            color: var(--text-gray);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .swagger-ui .scheme-container {
            background: white;
            border: 2px solid var(--medium-gray);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .swagger-ui .opblock {
            background: white;
            border: 2px solid var(--medium-gray);
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .swagger-ui .opblock:hover {
            border-color: var(--primary-red);
            box-shadow: 0 8px 30px rgba(192, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .swagger-ui .opblock.opblock-get {
            border-color: var(--bamboo-green);
            background: linear-gradient(135deg, rgba(74, 124, 89, 0.05), transparent);
        }

        .swagger-ui .opblock.opblock-post {
            border-color: var(--primary-orange);
            background: linear-gradient(135deg, rgba(255, 106, 0, 0.05), transparent);
        }

        .swagger-ui .opblock.opblock-put {
            border-color: var(--gold-accent);
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.05), transparent);
        }

        .swagger-ui .opblock.opblock-delete {
            border-color: var(--primary-red);
            background: linear-gradient(135deg, rgba(192, 0, 0, 0.05), transparent);
        }

        .swagger-ui .opblock .opblock-summary {
            border-bottom: 1px solid var(--medium-gray);
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .swagger-ui .opblock .opblock-summary:hover {
            background: rgba(192, 0, 0, 0.05);
        }

        .swagger-ui .opblock .opblock-summary-method {
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .swagger-ui .opblock.opblock-get .opblock-summary-method {
            background: var(--bamboo-green);
            color: white;
        }

        .swagger-ui .opblock.opblock-post .opblock-summary-method {
            background: var(--primary-orange);
            color: white;
        }

        .swagger-ui .opblock.opblock-put .opblock-summary-method {
            background: var(--gold-accent);
            color: var(--sumi-black);
        }

        .swagger-ui .opblock.opblock-delete .opblock-summary-method {
            background: var(--primary-red);
            color: white;
        }

        .swagger-ui .opblock .opblock-summary-path {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--primary-red);
        }

        .swagger-ui .opblock .opblock-summary-description {
            color: var(--text-gray);
            margin-left: 10px;
        }

        .swagger-ui .opblock .opblock-body {
            padding: 20px;
        }

        .swagger-ui .parameter-col_description {
            width: 40%;
        }

        .swagger-ui .parameter-controls {
            width: 30%;
        }

        .swagger-ui .parameter-col_type {
            width: 15%;
        }

        .swagger-ui .table thead tr th, .swagger-ui .table thead tr td {
            border-bottom: 2px solid var(--primary-red);
            color: var(--sumi-black);
            font-weight: 600;
        }

        .swagger-ui .table tbody tr td {
            border-bottom: 1px solid var(--medium-gray);
            color: var(--text-gray);
        }

        .swagger-ui .btn {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .swagger-ui .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(192, 0, 0, 0.3);
        }

        .swagger-ui .highlight-code {
            background: var(--washi-white);
            border: 2px solid var(--medium-gray);
            border-radius: 10px;
            padding: 15px;
        }

        .swagger-ui .highlight-code pre {
            color: var(--sumi-black);
            font-family: 'Courier New', monospace;
        }

        .swagger-ui .model {
            background: white;
            border: 2px solid var(--medium-gray);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .swagger-ui .model .model-title {
            color: var(--primary-red);
            font-family: 'Noto Serif JP', serif;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .swagger-ui .model-box {
            background: var(--washi-white);
            border: 1px solid var(--medium-gray);
            border-radius: 10px;
            padding: 15px;
        }

        .swagger-ui .prop-type {
            color: var(--bamboo-green);
            font-weight: 600;
        }

        .swagger-ui .response-col_status {
            width: 10%;
        }

        .swagger-ui .response-col_description {
            width: 50%;
        }

        .swagger-ui .response-col_links {
            width: 40%;
        }

        /* 页面头部 */
        .page-header {
            background: linear-gradient(135deg, var(--sumi-black) 0%, #1a1a1a 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="wave" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M0 50 Q25 30 50 50 T100 50" stroke="rgba(255,255,255,0.05)" fill="none" stroke-width="2"/></pattern></defs><rect width="100" height="100" fill="url(%23wave)"/></svg>');
            opacity: 0.3;
            z-index: 1;
        }

        .page-header-content {
            position: relative;
            z-index: 2;
        }

        .page-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, white, var(--gold-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        /* 快速链接卡片 */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }

        .quick-link-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .quick-link-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .quick-link-card:hover::before {
            transform: scaleX(1);
        }

        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(192, 0, 0, 0.15);
        }

        .quick-link-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        .quick-link-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--sumi-black);
            margin-bottom: 10px;
        }

        .quick-link-description {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }
            
            .page-subtitle {
                font-size: 1.1rem;
            }
            
            .quick-links {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
</head>
<body class="japanese-pattern">
    <!-- 高端顶部导航栏 -->
    <nav class="top-navbar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="/" class="logo">
                        雅虎B2B <span>× 和风匠心</span>
                    </a>
                </div>
                <div class="col-md-9">
                    <div class="user-info justify-content-end">
                        <div class="d-flex align-items-center gap-3">
                            <a href="/dashboard" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-house-door me-2"></i>仪表板
                            </a>
                            <a href="/products" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-box me-2"></i>产品
                            </a>
                            <a href="/orders" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-cart3 me-2"></i>订单
                            </a>
                            <a href="/admin" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-gear me-2"></i>管理
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- 页面头部 -->
    <div class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1 class="page-title">
                    <i class="bi bi-file-text me-3"></i>API 文档中心
                </h1>
                <p class="page-subtitle">探索雅虎B2B采购平台的完整API接口文档</p>
                <div class="mt-4">
                    <button class="btn btn-light btn-lg me-3" onclick="scrollToSwagger()">
                        <i class="bi bi-code-slash me-2"></i>查看API文档
                    </button>
                    <button class="btn btn-outline-light btn-lg" onclick="downloadSpec()">
                        <i class="bi bi-download me-2"></i>下载规范
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <!-- 快速链接 -->
        <div class="quick-links">
            <div class="quick-link-card hover-float">
                <div class="quick-link-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="quick-link-title">认证接口</h3>
                <p class="quick-link-description">JWT令牌认证、用户登录、权限验证</p>
                <button class="btn btn-japanese btn-sm mt-3" onclick="scrollToSection('auth')">
                    查看详情
                </button>
            </div>
            
            <div class="quick-link-card hover-float">
                <div class="quick-link-icon">
                    <i class="bi bi-box"></i>
                </div>
                <h3 class="quick-link-title">产品接口</h3>
                <p class="quick-link-description">产品查询、搜索、分类管理、库存信息</p>
                <button class="btn btn-japanese btn-sm mt-3" onclick="scrollToSection('products')">
                    查看详情
                </button>
            </div>
            
            <div class="quick-link-card hover-float">
                <div class="quick-link-icon">
                    <i class="bi bi-cart3"></i>
                </div>
                <h3 class="quick-link-title">订单接口</h3>
                <p class="quick-link-description">订单创建、状态查询、物流追踪、历史记录</p>
                <button class="btn btn-japanese btn-sm mt-3" onclick="scrollToSection('orders')">
                    查看详情
                </button>
            </div>
            
            <div class="quick-link-card hover-float">
                <div class="quick-link-icon">
                    <i class="bi bi-gear"></i>
                </div>
                <h3 class="quick-link-title">管理接口</h3>
                <p class="quick-link-description">系统管理、数据统计、用户管理、报表生成</p>
                <button class="btn btn-japanese btn-sm mt-3" onclick="scrollToSection('admin')">
                    查看详情
                </button>
            </div>
        </div>

        <!-- API 使用指南 -->
        <div class="row mb-5">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4">
                            <i class="bi bi-info-circle me-2"></i>快速开始
                        </h3>
                        <div class="accordion" id="quickStartAccordion">
                            <div class="accordion-item border-0 mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                        1. 获取访问令牌
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#quickStartAccordion">
                                    <div class="accordion-body">
                                        <p>使用您的账户信息调用登录接口获取JWT访问令牌：</p>
                                        <pre class="bg-light p-3 rounded"><code>curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"your_username","password":"your_password"}'</code></pre>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                        2. 使用访问令牌
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#quickStartAccordion">
                                    <div class="accordion-body">
                                        <p>在请求头中添加Bearer令牌进行身份验证：</p>
                                        <pre class="bg-light p-3 rounded"><code>curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"</code></pre>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                        3. 调用API接口
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#quickStartAccordion">
                                    <div class="accordion-body">
                                        <p>使用正确的HTTP方法和参数调用相应的API接口：</p>
                                        <pre class="bg-light p-3 rounded"><code>curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{"items":[{"sku":"ALIBABA_SKU_A123","quantity":2}]}'</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">
                            <i class="bi bi-link-45deg me-2"></i>重要链接
                        </h4>
                        <div class="list-group list-group-flush">
                            <a href="/api/health" class="list-group-item list-group-item-action border-0">
                                <i class="bi bi-heart-pulse me-2"></i>健康检查
                            </a>
                            <a href="/api/openapi" class="list-group-item list-group-item-action border-0">
                                <i class="bi bi-file-code me-2"></i>OpenAPI规范
                            </a>
                            <a href="/dashboard" class="list-group-item list-group-item-action border-0">
                                <i class="bi bi-speedometer2 me-2"></i>仪表板
                            </a>
                            <a href="mailto:support@example.com" class="list-group-item list-group-item-action border-0">
                                <i class="bi bi-envelope me-2"></i>技术支持
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Swagger UI -->
    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-bundle.js"></script>
    <script src="/js/japanese-interactions.js"></script>
    <script>
        window.onload = function() {
            // 初始化Swagger UI
            const ui = SwaggerUIBundle({
                url: '/api/openapi',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIStandalonePreset.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                defaultModelsExpandDepth: 2,
                defaultModelExpandDepth: 2,
                tryItOutEnabled: true,
                filter: true,
                supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                onComplete: function() {
                    // Swagger UI 加载完成后的回调
                    console.log('Swagger UI loaded successfully');
                    
                    // 添加自定义样式
                    customizeSwaggerUI();
                }
            });
        };

        // 自定义Swagger UI样式
        function customizeSwaggerUI() {
            // 等待DOM完全加载
            setTimeout(() => {
                // 添加和风样式类
                const swaggerContainer = document.querySelector('.swagger-ui');
                if (swaggerContainer) {
                    swaggerContainer.classList.add('japanese-pattern');
                }

                // 为操作块添加悬浮效果
                const opblocks = document.querySelectorAll('.opblock');
                opblocks.forEach(block => {
                    block.classList.add('hover-float');
                });

                // 为模型添加动画效果
                const models = document.querySelectorAll('.model');
                models.forEach(model => {
                    model.classList.add('fade-in-up');
                });
            }, 1000);
        }

        // 滚动到Swagger UI
        function scrollToSwagger() {
            const swaggerElement = document.getElementById('swagger-ui');
            if (swaggerElement) {
                swaggerElement.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // 滚动到特定部分
        function scrollToSection(section) {
            scrollToSwagger();
            // 这里可以实现更精确的滚动到特定API部分
            console.log('Scrolling to section:', section);
        }

        // 下载API规范
        function downloadSpec() {
            window.open('/api/openapi', '_blank');
        }

        // 添加页面滚动效果
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.top-navbar');
            if (window.scrollY > 100) {
                navbar.style.boxShadow = '0 4px 30px rgba(0,0,0,0.2)';
            } else {
                navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
            }
        });
    </script>
</body>
</html>