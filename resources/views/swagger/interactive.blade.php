<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>雅虎 B2B 采购门户 - API 文档</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Noto Sans JP', sans-serif;
        }
        
        .swagger-ui .topbar {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border-bottom: 4px solid #d4af37;
        }
        
        .swagger-ui .topbar .download-url-wrapper {
            display: none;
        }
        
        .swagger-ui .info {
            margin: 50px 0;
        }
        
        .swagger-ui .info .title {
            color: #1a1a1a;
            font-family: 'Noto Serif JP', serif;
        }
        
        .swagger-ui .scheme-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8f8f8 100%);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 10px;
            margin: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .swagger-ui .opblock.opblock-post {
            border-color: #d4af37;
            background: rgba(212, 175, 55, 0.05);
        }
        
        .swagger-ui .opblock.opblock-get {
            border-color: #4caf50;
            background: rgba(76, 175, 80, 0.05);
        }
        
        .swagger-ui .opblock.opblock-post .opblock-summary-method {
            background: #d4af37;
        }
        
        .swagger-ui .opblock.opblock-get .opblock-summary-method {
            background: #4caf50;
        }
        
        .swagger-ui .btn.authorize {
            background: linear-gradient(45deg, #d4af37 0%, #f4e4bc 100%);
            color: #1a1a1a;
            border: none;
        }
        
        .swagger-ui .btn.authorize:hover {
            background: linear-gradient(45deg, #ffb7c5 0%, #ffc0cb 100%);
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    
    <script src="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '/api/openapi',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                defaultModelsExpandDepth: 2,
                defaultModelExpandDepth: 2,
                tryItOutEnabled: true,
                filter: true,
                supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                onComplete: function() {
                    console.log("Swagger UI 加载完成");
                },
                requestInterceptor: function(request) {
                    // 添加认证头
                    const token = localStorage.getItem('access_token');
                    if (token) {
                        request.headers.Authorization = 'Bearer ' + token;
                    }
                    return request;
                },
                responseInterceptor: function(response) {
                    return response;
                }
            });
        }
    </script>
</body>
</html>