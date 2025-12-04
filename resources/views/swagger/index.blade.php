<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API 文档 - 万方商事 B2B 采购门户</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f8fafc;
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #dc2626 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .api-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .method {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
            color: white;
            font-size: 0.875rem;
        }
        .get { background-color: #28a745; }
        .post { background-color: #007bff; }
        .put { background-color: #ffc107; color: #212529; }
        .delete { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>万方商事 B2B 采购门户 API 文档</h1>
            <p class="lead">专业的 API 接口文档，支持在线测试</p>
        </div>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>快速导航</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="#auth" class="list-group-item list-group-item-action">认证接口</a>
                            <a href="#products" class="list-group-item list-group-item-action">产品接口</a>
                            <a href="#orders" class="list-group-item list-group-item-action">订单接口</a>
                            <a href="#inquiries" class="list-group-item list-group-item-action">询价接口</a>
                            <a href="/api/openapi" class="list-group-item list-group-item-action">OpenAPI 规范</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <section id="auth">
                    <h2>认证接口</h2>
                    <div class="api-card">
                        <h4><span class="method post">POST</span> /api/auth/login</h4>
                        <p>用户登录</p>
                        <strong>请求参数:</strong>
                        <pre>{
    "username": "testuser",
    "password": "password123"
}</pre>
                    </div>
                    <div class="api-card">
                        <h4><span class="method get">GET</span> /api/auth/me</h4>
                        <p>获取当前用户信息 (需要认证)</p>
                    </div>
                </section>
                
                <section id="products">
                    <h2>产品接口</h2>
                    <div class="api-card">
                        <h4><span class="method get">GET</span> /api/products</h4>
                        <p>获取产品列表</p>
                        <strong>查询参数:</strong>
                        <ul>
                            <li>page: 页码 (默认: 1)</li>
                            <li>per_page: 每页数量 (默认: 20)</li>
                            <li>search: 搜索关键词</li>
                            <li>category: 产品分类</li>
                        </ul>
                    </div>
                </section>
                
                <section id="orders">
                    <h2>订单接口</h2>
                    <div class="api-card">
                        <h4><span class="method get">GET</span> /api/orders</h4>
                        <p>获取订单列表 (需要认证)</p>
                    </div>
                    <div class="api-card">
                        <h4><span class="method post">POST</span> /api/orders</h4>
                        <p>创建订单 (需要认证)</p>
                    </div>
                </section>
                
                <section id="inquiries">
                    <h2>询价接口</h2>
                    <div class="api-card">
                        <h4><span class="method get">GET</span> /api/inquiries</h4>
                        <p>获取询价列表 (需要认证)</p>
                    </div>
                    <div class="api-card">
                        <h4><span class="method post">POST</span> /api/inquiries</h4>
                        <p>创建询价 (需要认证)</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>