<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width,initial-scale=1" name="viewport">
    <title>RAKUMART × 1688 - 日本向け中国輸入代行サービス</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/japanese-effects.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&family=Noto+Sans+JP:wght@300;400;500;700&display=swap');
        
        :root {
            --primary-red: #C00000;
            --primary-orange: #ff6a00;
            --sakura-pink: #FFB7C5;
            --washi-white: #FFF8F0;
            --sumi-black: #2C2C2C;
            --light-gray: #F5F5F5;
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
            margin: 0;
            padding: 0;
            background-color: var(--washi-white);
            color: var(--sumi-black);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* 日式传统图案背景 */
        .japanese-pattern {
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(192, 0, 0, 0.03) 35px, rgba(192, 0, 0, 0.03) 70px),
                repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(255, 183, 197, 0.03) 35px, rgba(255, 183, 197, 0.03) 70px);
        }

        /* 樱花飘落动画 */
        @keyframes sakuraFall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .sakura {
            position: fixed;
            background: linear-gradient(120deg, var(--sakura-pink) 0%, #FFC0CB 100%);
            border-radius: 150% 0 150% 0;
            animation: sakuraFall 15s linear infinite;
            pointer-events: none;
            z-index: 1;
        }

        .sakura:nth-child(1) { left: 10%; animation-delay: 0s; width: 10px; height: 10px; }
        .sakura:nth-child(2) { left: 20%; animation-delay: 2s; width: 12px; height: 12px; }
        .sakura:nth-child(3) { left: 30%; animation-delay: 4s; width: 8px; height: 8px; }
        .sakura:nth-child(4) { left: 40%; animation-delay: 6s; width: 15px; height: 15px; }
        .sakura:nth-child(5) { left: 50%; animation-delay: 8s; width: 10px; height: 10px; }
        .sakura:nth-child(6) { left: 60%; animation-delay: 10s; width: 12px; height: 12px; }
        .sakura:nth-child(7) { left: 70%; animation-delay: 12s; width: 8px; height: 8px; }
        .sakura:nth-child(8) { left: 80%; animation-delay: 14s; width: 15px; height: 15px; }
        .sakura:nth-child(9) { left: 90%; animation-delay: 16s; width: 10px; height: 10px; }

        /* 高级滚动动画 */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* 日式装饰线 */
        .japanese-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, var(--primary-red) 20%, var(--primary-orange) 50%, var(--primary-red) 80%, transparent 100%);
            margin: 40px auto;
            position: relative;
        }

        .japanese-divider::before {
            content: '❦';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--washi-white);
            padding: 0 20px;
            color: var(--primary-red);
            font-size: 20px;
        }

        /* 英雄区域 - 和风高端设计 */
        .hero-section {
            background: 
                radial-gradient(circle at 20% 80%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(192, 0, 0, 0.1) 0%, transparent 50%),
                linear-gradient(135deg, var(--sumi-black) 0%, var(--primary-red) 50%, #8B0000 100%);
            color: white;
            padding: 120px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
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

        .hero-content {
            position: relative;
            z-index: 3;
        }

        .hero-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 4.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            line-height: 1.2;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
            animation: heroTitleGlow 3s ease-in-out infinite alternate;
        }

        @keyframes heroTitleGlow {
            from {
                text-shadow: 0 2px 20px rgba(0,0,0,0.3), 0 0 30px rgba(212, 175, 55, 0.2);
            }
            to {
                text-shadow: 0 2px 20px rgba(0,0,0,0.3), 0 0 50px rgba(212, 175, 55, 0.4);
            }
        }

        .hero-subtitle {
            font-size: 1.8rem;
            margin-bottom: 40px;
            opacity: 0.95;
            font-weight: 300;
            line-height: 1.6;
        }

        .hero-buttons {
            animation: fadeInUp 1s ease-out 0.4s;
        }

        .hero-image {
            position: absolute;
            right: -100px;
            top: 50%;
            transform: translateY(-50%);
            width: 600px;
            height: 400px;
            opacity: 0.15;
            z-index: 2;
        }

        /* 日式印章效果 */
        .japanese-seal {
            position: absolute;
            top: 40px;
            right: 40px;
            width: 80px;
            height: 80px;
            background: var(--primary-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(15deg);
            font-family: 'Noto Serif JP', serif;
            font-size: 24px;
            color: white;
            z-index: 3;
            box-shadow: 0 4px 15px rgba(192, 0, 0, 0.4);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 服务特色 - 和风设计 */
        .features-section {
            padding: 100px 0;
            background-color: var(--washi-white);
            position: relative;
        }

        .features-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(135deg, transparent 0%, rgba(192, 0, 0, 0.05) 50%, transparent 100%);
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(192, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3.5rem;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card h4 {
            font-family: 'Noto Serif JP', serif;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--sumi-black);
        }

        .feature-card p {
            color: var(--text-gray);
            line-height: 1.8;
            font-weight: 300;
        }

        /* 统计数据 - 和风设计 */
        .stats-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--washi-white) 0%, white 100%);
            position: relative;
        }

        .stats-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent), var(--primary-orange), var(--primary-red));
        }

        .stat-card {
            text-align: center;
            padding: 40px 20px;
            position: relative;
            transition: all 0.3s ease;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 30px;
            background: linear-gradient(180deg, var(--primary-red), var(--primary-orange));
        }

        .stat-number {
            font-family: 'Noto Serif JP', serif;
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
            display: inline-block;
        }

        .stat-label {
            font-size: 1.2rem;
            color: var(--text-gray);
            margin-top: 10px;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .stat-card:hover .stat-number {
            transform: scale(1.1);
        }

        /* 流程步骤 - 和风设计 */
        .process-section {
            padding: 100px 0;
            background: var(--washi-white);
            position: relative;
        }

        .process-step {
            text-align: center;
            padding: 0 20px;
            position: relative;
        }

        .process-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2.5rem;
            color: white;
            position: relative;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(192, 0, 0, 0.2);
        }

        .process-icon::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border: 2px solid var(--gold-accent);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .process-step:hover .process-icon::before {
            opacity: 1;
        }

        .process-step:hover .process-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 40px rgba(192, 0, 0, 0.3);
        }

        .process-line {
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
            position: absolute;
            top: 50px;
            left: 50%;
            width: 100%;
            z-index: 1;
        }

        .process-step h5 {
            font-family: 'Noto Serif JP', serif;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--sumi-black);
        }

        .process-step p {
            color: var(--text-gray);
            line-height: 1.8;
            font-weight: 300;
        }

        /* CTA 区域 - 和风设计 */
        .cta-section {
            background: 
                radial-gradient(circle at 20% 80%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 183, 197, 0.1) 0%, transparent 50%),
                linear-gradient(135deg, var(--sumi-black) 0%, var(--primary-red) 50%, #8B0000 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="circles" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="2" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="60" height="60" fill="url(%23circles)"/></svg>');
            z-index: 1;
        }

        .cta-content {
            position: relative;
            z-index: 2;
        }

        /* 页脚 - 和风设计 */
        footer {
            background: linear-gradient(135deg, var(--sumi-black) 0%, #1a1a1a 100%);
            color: white;
            padding: 60px 0 40px;
            text-align: center;
            position: relative;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent), var(--primary-orange), var(--primary-red));
        }

        /* 高级按钮设计 */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 50px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(192, 0, 0, 0.3);
        }

        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary-custom:hover::before {
            left: 100%;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(192, 0, 0, 0.4);
        }

        .btn-outline-custom {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 13px 40px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 50px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-outline-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: white;
            transition: width 0.4s ease;
            z-index: -1;
        }

        .btn-outline-custom:hover::before {
            width: 100%;
        }

        .btn-outline-custom:hover {
            color: var(--primary-red);
            transform: translateY(-3px);
        }

        /* 标题装饰 */
        .section-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 3rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 60px;
            position: relative;
            color: var(--sumi-black);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
        }

        /* 响应式 */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-image {
                display: none;
            }
            
            .feature-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body class="japanese-pattern">
    <!-- 樱花飘落效果 -->
    <div class="sakura"></div>
    <div class="sakura"></div>
    <div class="sakura"></div>
    <div class="sakura"></div>
    <div class="sakura"></div>
    <div class="sakura"></div>
    <div class="sakura"></div>
    <div class="sakura"></div>
    <div class="sakura"></div>

    <!-- 高端导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--sumi-black); box-shadow: 0 2px 20px rgba(0,0,0,0.1);">
        <div class="container">
            <a class="navbar-brand" href="/">
                <span style="color: white; font-weight: bold;">RAKUMART</span>
                <span style="color: var(--primary-orange);"> × 1688</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">ダッシュボード</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products">製品検索</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/orders">注文管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin">管理者画面</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/docs">API ド�当</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 英雄区域 - 和风高端设计 -->
    <section class="hero-section">
        <div class="japanese-seal">信</div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        雅虎B2B采购门户<br>
                        <span style="color: var(--gold-accent);">和风匠心 × 品质保证</span>
                    </h1>
                    <p class="hero-subtitle">
                        为日本企业精心打造的中国采购服务平台。<br>
                        融合传统和风美学与现代科技，提供卓越的跨境采购体验。
                    </p>
                    <div class="hero-buttons">
                        <a href="/dashboard" class="btn btn-primary-custom me-3">开始体验</a>
                        <a href="/products" class="btn btn-outline-custom">探索产品</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://via.placeholder.com/600x400" alt="高品质中国产品" class="img-fluid rounded">
        </div>
    </section>

    <!-- 服务特色 - 和风设计 -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title fade-in-up">核心服务优势</h2>
            <div class="japanese-divider"></div>
            <div class="row">
                <div class="col-md-4 fade-in-up">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>匠心品质保证</h4>
                        <p>经验丰富的专业团队，从产品检验到物流配送全程把控，确保每一个细节都符合最高标准。</p>
                    </div>
                </div>
                <div class="col-md-4 fade-in-up">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-cash-yen"></i>
                        </div>
                        <h4>透明价格体系</h4>
                        <p>明码标价，无隐藏费用，提供最具竞争力的采购成本，让您的每一分投入都物超所值。</p>
                    </div>
                </div>
                <div class="col-md-4 fade-in-up">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h4>极速物流体验</h4>
                        <p>优化的国际物流网络，配合先进的仓储管理系统，确保产品以最快速度安全送达。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 统计数据 - 和风设计 -->
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title fade-in-up">平台实力数据</h2>
            <div class="japanese-divider"></div>
            <div class="row">
                <div class="col-md-3 fade-in-up">
                    <div class="stat-card">
                        <div class="stat-number">15,000+</div>
                        <div class="stat-label">合作企业</div>
                    </div>
                </div>
                <div class="col-md-3 fade-in-up">
                    <div class="stat-card">
                        <div class="stat-number">50,000+</div>
                        <div class="stat-label">优质商品</div>
                    </div>
                </div>
                <div class="col-md-3 fade-in-up">
                    <div class="stat-card">
                        <div class="stat-number">99.8%</div>
                        <div class="stat-label">客户满意度</div>
                    </div>
                </div>
                <div class="col-md-3 fade-in-up">
                    <div class="stat-card">
                        <div class="stat-number">24小时</div>
                        <div class="stat-label">平均响应时间</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 流程步骤 - 和风设计 -->
    <section class="process-section">
        <div class="container">
            <h2 class="section-title fade-in-up">优雅采购流程</h2>
            <div class="japanese-divider"></div>
            <div class="row">
                <div class="col-md-3 process-step fade-in-up">
                    <div class="process-icon">
                        <i class="bi bi-search"></i>
                        <div class="process-line"></div>
                    </div>
                    <h5>产品探索</h5>
                    <p>智能搜索系统，帮您快速找到理想的优质产品</p>
                </div>
                <div class="col-md-3 process-step fade-in-up">
                    <div class="process-icon">
                        <i class="bi bi-cart-plus"></i>
                        <div class="process-line"></div>
                    </div>
                    <h5>便捷下单</h5>
                    <p>简洁直观的下单流程，一键完成采购申请</p>
                </div>
                <div class="col-md-3 process-step fade-in-up">
                    <div class="process-icon">
                        <i class="bi bi-credit-card"></i>
                        <div class="process-line"></div>
                    </div>
                    <h5>安全支付</h5>
                    <p>多重加密支付系统，保障您的资金安全</p>
                </div>
                <div class="col-md-3 process-step fade-in-up">
                    <div class="process-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h5>极速配送</h5>
                    <p>专业物流团队，确保产品安全快速送达</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA 区域 - 和风设计 -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="mb-4" style="font-family: 'Noto Serif JP', serif; font-size: 3rem; font-weight: 700;">开启您的采购之旅</h2>
                <p class="mb-5" style="font-size: 1.3rem; opacity: 0.9;">立即注册，体验融合和风美学的现代化采购平台</p>
                <a href="/dashboard" class="btn btn-primary-custom me-3">立即开始</a>
                <a href="/docs" class="btn btn-outline-custom">查看文档</a>
            </div>
        </div>
    </section>

    <!-- 页脚 -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>RAKUMART × 1688</h5>
                    <p>© 2025 RAKUMART. All rights reserved.</p>
                    <p>
                        <a href="#" class="text-white text-decoration-none me-2">利用規約</a>
                        <a href="#" class="text-white text-decoration-none me-2">プライバシー</a>
                        <a href="#" class="text-white text-decoration-none">お問い合わせ</a>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p>
                        <a href="#" class="text-white text-decoration-none me-2">
                            <i class="bi bi-facebook"></i> Facebook
                        </a>
                        <a href="#" class="text-white text-decoration-none me-2">
                            <i class="bi bi-twitter"></i> Twitter
                        </a>
                        <a href="#" class="text-white text-decoration-none me-2">
                            <i class="bi bi-youtube"></i> YouTube
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/japanese-interactions.js"></script>
    
    <!-- 高级滚动动画 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 滚动显示动画
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            // 观察所有需要动画的元素
            document.querySelectorAll('.fade-in-up').forEach(el => {
                observer.observe(el);
            });

            // 平滑滚动
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // 鼠标跟随效果
            let mouseX = 0;
            let mouseY = 0;
            let currentX = 0;
            let currentY = 0;

            document.addEventListener('mousemove', (e) => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });

            function animateMouseFollow() {
                currentX += (mouseX - currentX) * 0.05;
                currentY += (mouseY - currentY) * 0.05;

                const heroSection = document.querySelector('.hero-section');
                if (heroSection) {
                    const translateX = (currentX - window.innerWidth / 2) * 0.01;
                    const translateY = (currentY - window.innerHeight / 2) * 0.01;
                    heroSection.style.transform = `perspective(1000px) rotateY(${translateX}deg) rotateX(${-translateY}deg)`;
                }

                requestAnimationFrame(animateMouseFollow);
            }
            animateMouseFollow();
        });
    </script>
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Z2F259TTBB"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-Z2F259TTBB');
    </script>

    <!-- Yahoo Japan Tracking -->
    <script async src="https://s.yimg.jp/images/listing/tool/cv/ytag.js"></script>
    <script>
        window.yjDataLayer = window.yjDataLayer || [];
        function ytag() { yjDataLayer.push(arguments); }
        ytag({"type": "ycl_cookie", "config": {"ycl_use_non_cookie_storage": true}});
    </script>

    <!-- Google Ads -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-10850698368"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'AW-10850698368', {'allow_enhanced_conversions': true});
    </script>

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-221103314-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'UA-221103314-1');
    </script>

    <!-- 登录转换追踪 -->
    <script>
        window.addEventListener('load', function (event) {
            document.querySelectorAll("button").forEach(function (e) {
                e.addEventListener('click', function () {
                    // 登録ボタンのクリックを追跡
                    if (e.textContent.includes('無料登録') || e.textContent.includes('登録する')) {
                        gtag('event', 'conversion', {'send_to': 'AW-10850698368/RAHzCKfb474YEICJgrYo'});
                    }
                });
            });
        });
    </script>

    <!-- 阿里云监控 -->
    <script>
        let userId = '';
        if (sessionStorage.getItem("vuex") != null) {
            userId = JSON.parse(sessionStorage.getItem("vuex")).userInfo != null 
                ? JSON.parse(sessionStorage.getItem("vuex")).userInfo.operation_id 
                : '';
        }
        if (location.host.indexOf('.co.jp') !== -1 || location.host.indexOf('.com') !== -1) {
            !(function (c, b, d, a) {
                c[a] || (c[a] = {});
                c[a].config = {
                    pid: "goq8k48ox9@c03c35e52b50c5b",
                    appType: "web",
                    imgUrl: "https://arms-retcode.alicdn.com/r.png?",
                    behavior: false,
                    disableHook: true,
                    uid: userId == '' ? '' : userId
                };
                with (b) with (body) with (insertBefore(createElement("script"), firstChild)) setAttribute("crossorigin", "", src = d)
            })(window, document, "https://retcode.alicdn.com/retcode/bl.js", "__bl");
        }
    </script>
</body>
</html>