<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>万方商事数字门户 | BANHO TRADING Digital Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&family=Noto+Serif+JP:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --banho-primary: #1a365d;
            --banho-secondary: #d4af37;
            --banho-accent: #dc2626;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-800: #1e293b;
        }

        body {
            font-family: 'Noto Sans JP', sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, #e2e8f0 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .portal-header {
            background: linear-gradient(135deg, var(--banho-primary) 0%, #0f172a 100%);
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .portal-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .portal-subtitle {
            text-align: center;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .portal-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1rem;
        }

        .sites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .site-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .site-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--banho-primary) 0%, var(--banho-secondary) 100%);
        }

        .site-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--banho-primary);
        }

        .site-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--banho-primary) 0%, var(--banho-secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .site-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--banho-primary);
            margin-bottom: 1rem;
            text-align: center;
        }

        .site-description {
            color: var(--gray-800);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .site-features {
            list-style: none;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .site-features li {
            padding: 0.25rem 0;
            color: #64748b;
            font-size: 0.9rem;
            text-align: center;
        }

        .site-features li::before {
            content: '✓';
            color: var(--banho-secondary);
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .btn-portal {
            display: block;
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--banho-primary) 0%, var(--banho-secondary) 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-portal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 54, 93, 0.3);
            color: white;
        }

        .new-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--banho-accent);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .portal-title {
                font-size: 2rem;
            }
            
            .sites-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="portal-header">
        <div class="container">
            <h1 class="portal-title">万方商事数字门户</h1>
            <p class="portal-subtitle">BANHO TRADING Digital Portal - 选择您要访问的系统</p>
        </div>
    </header>

    <div class="portal-container">
        <div class="sites-grid">
            <!-- 原有和风网站 -->
            <div class="site-card">
                <div class="site-icon">🌸</div>
                <h3 class="site-title">和风采购门户</h3>
                <p class="site-description">
                    原有的雅虎B2B采购系统，采用传统和风设计美学
                </p>
                <ul class="site-features">
                    <li>和风设计美学</li>
                    <li>产品采购管理</li>
                    <li>订单跟踪系统</li>
                    <li>用户仪表板</li>
                </ul>
                <a href="/" class="btn-portal">进入和风门户</a>
            </div>

            <!-- 万方商事企业网站 -->
            <div class="site-card">
                <span class="new-badge">NEW</span>
                <div class="site-icon">🏢</div>
                <h3 class="site-title">万方商事企业门户</h3>
                <p class="site-description">
                    全新企业级B2B采购平台，专业商务形象展示
                </p>
                <ul class="site-features">
                    <li>企业级设计</li>
                    <li>专业商务风格</li>
                    <li>品牌形象展示</li>
                    <li>多语言支持</li>
                </ul>
                <a href="/banho" class="btn-portal">进入企业门户</a>
            </div>

            <!-- 管理后台 -->
            <div class="site-card">
                <div class="site-icon">⚙️</div>
                <h3 class="site-title">管理后台</h3>
                <p class="site-description">
                    系统管理和数据统计后台，管理员专用入口
                </p>
                <ul class="site-features">
                    <li>系统监控</li>
                    <li>用户管理</li>
                    <li>数据统计</li>
                    <li>权限控制</li>
                </ul>
                <a href="/admin" class="btn-portal">进入管理后台</a>
            </div>

            <!-- API文档 -->
            <div class="site-card">
                <div class="site-icon">📚</div>
                <h3 class="site-title">API文档中心</h3>
                <p class="site-description">
                    完整的API接口文档，支持在线测试和调试
                </p>
                <ul class="site-features">
                    <li>交互式文档</li>
                    <li>在线测试</li>
                    <li>OpenAPI规范</li>
                    <li>代码示例</li>
                </ul>
                <a href="/docs" class="btn-portal">查看API文档</a>
            </div>
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">
                <small>
                    💡 提示：您可以收藏常用页面，或直接访问对应URL<br>
                    🏢 万方商事株式会社 | 
                    <a href="https://manpou.jp/" target="_blank" style="color: var(--banho-primary); text-decoration: none;">manpou.jp</a>
                </small>
            </p>
        </div>
    </div>

    <script>
        // 添加页面加载动画
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.site-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>