<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width,initial-scale=1" name="viewport">
    <title>RAKUMART × 1688 - 日本向け中国輸入代行サービス</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #C00000;
            --primary-orange: #ff6a00;
            --light-gray: #F5F5F5;
            --dark-gray: #333333;
            --text-gray: #666666;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* 英雄区域 */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-red) 0%, #8B0000 100%);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.2s;
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
            opacity: 0.3;
            z-index: 1;
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

        /* 服务特色 */
        .features-section {
            padding: 80px 0;
            background-color: var(--light-gray);
        }

        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            height: 100%;
            transition: all 0.3s ease;
            border-bottom: 3px solid var(--primary-red);
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-red);
            margin-bottom: 20px;
        }

        /* 统计数据 */
        .stats-section {
            padding: 60px 0;
            background-color: white;
        }

        .stat-card {
            text-align: center;
            padding: 30px 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-red);
        }

        .stat-label {
            font-size: 1.1rem;
            color: var(--text-gray);
            margin-top: 10px;
        }

        /* 流程步骤 */
        .process-section {
            padding: 80px 0;
            background-color: var(--light-gray);
        }

        .process-step {
            text-align: center;
            padding: 0 20px;
        }

        .process-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
            position: relative;
        }

        .process-line {
            height: 2px;
            background: var(--primary-orange);
            position: absolute;
            top: 40px;
            left: 50%;
            width: 100%;
            z-index: 1;
        }

        /* CTA 区域 */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-red) 0%, #8B0000 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        /* 页脚 */
        footer {
            background-color: var(--dark-gray);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .btn-primary-custom {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background-color: #8B0000;
            border-color: #8B0000;
            transform: translateY(-2px);
        }

        .btn-outline-custom {
            background-color: transparent;
            border-color: white;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-outline-custom:hover {
            background-color: white;
            color: var(--primary-red);
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
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--primary-red);">
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

    <!-- 英雄区域 -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        中国輸入代行サービス<br>
                        <span style="color: var(--primary-orange);">RAKUMART × 1688</span>
                    </h1>
                    <p class="hero-subtitle">
                        日本の事業者様向けの中国ECサイト輸入代行サービス。<br>
                        安心・簡単・低コストで中国製品を日本にお届けします。
                    </p>
                    <div class="hero-buttons">
                        <a href="/register" class="btn btn-primary-custom me-3">無料登録</a>
                        <a href="/products" class="btn btn-outline-custom">製品を探す</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://via.placeholder.com/600x400" alt="中国製品イメージ" class="img-fluid rounded">
        </div>
    </section>

    <!-- 服务特色 -->
    <section class="features-section">
        <div class="container">
            <h2 class="text-center mb-5">サービス特色</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>安心の代行</h4>
                        <p class="text-muted">経験豊富な専門スタッフが、商品検査から配送まで一責対応。</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-cash-yen"></i>
                        </div>
                        <h4>透明な料金</h4>
                        <p class="text-muted">代行手数料を明確に表示、追加費用なしの安心価格。</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h4>迅速配送</h4>
                        <p class="text-muted">日本への配送スピードを最適化、短期間でお届けします。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 统计数据 -->
    <section class="stats-section">
        <div class="container">
            <h2 class="text-center mb-5">実績</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">15,000+</div>
                        <div class="stat-label">取引先数</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">50,000+</div>
                        <div class="stat-label">取扱商品数</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">99.8%</div>
                        <div class="stat-label">満足度</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">24時間</div>
                        <div class="stat-label">平均配送時間</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 流程步骤 -->
    <section class="process-section">
        <div class="container">
            <h2 class="text-center mb-5">ご利用までの流れ</h2>
            <div class="row">
                <div class="col-md-3 process-step">
                    <div class="process-icon">
                        <i class="bi bi-search"></i>
                        <div class="process-line"></div>
                    </div>
                    <h5>商品検索</h5>
                    <p class="text-muted">1688等のECサイトで商品を検索</p>
                </div>
                <div class="col-md-3 process-step">
                    <div class="process-icon">
                        <i class="bi bi-cart-plus"></i>
                        <div class="process-line"></div>
                    </div>
                    <h5>注文依頼</h5>
                    <p class="text-muted">簡単なフォームで注文を依頼</p>
                </div>
                <div class="col-md-3 process-step">
                    <div class="process-icon">
                        <i class="bi bi-credit-card"></i>
                        <div class="process-line"></div>
                    </div>
                    <h5>お支払い</h5>
                    <p class="text-text">安全な決済システムで支払い</p>
                </div>
                <div class="col-md-3 process-step">
                    <div class="process-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h5>日本配送</h5>
                    <p class="text-muted">日本の住所まで安全配送</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA 区域 -->
    <section class="cta-section">
        <div class="container">
            <h2 class="mb-4">今すぐ始めましょう</h2>
            <p class="mb-4">登録は無料で、すぐに中国製品の検索が可能です。</p>
            <a href="/register" class="btn btn-primary-custom me-3">無料で登録する</a>
            <a href="/docs" class="btn btn-outline-custom">API ド当を確認</a>
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