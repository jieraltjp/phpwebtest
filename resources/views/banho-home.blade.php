<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>万方商事 B2B 采购门户 | プロフェッショナル調達プラットフォーム</title>
    <meta name="description" content="万方商事が提供するプロフェッショナルB2B調達プラットフォーム。 Alibaba商品の仕入れから注文管理まで、ワンストップで対応します。">
    <meta name="keywords" content="B2B,調達,仕入れ,Alibaba,万方商事,プロフェッショナル">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&family=Noto+Serif+JP:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/temp-banho.css') }}" rel="stylesheet">
</head>
<body>
    <!-- 企业级导航栏 -->
    <nav class="banho-navbar">
        <div class="banho-navbar-container">
            <a href="/" class="banho-navbar-brand">
                <span>万方商事</span>
                <span style="color: var(--banho-secondary);">BANHO</span>
            </a>
            <div class="banho-navbar-nav">
                <a href="#features" class="banho-navbar-link">サービス</a>
                <a href="#stats" class="banho-navbar-link">実績</a>
                <a href="/docs" class="banho-navbar-link">API</a>
                <a href="/auth" class="banho-btn-primary">ログイン</a>
            </div>
        </div>
    </nav>

    <!-- 企业级Hero区域 -->
    <section class="banho-hero">
        <div class="banho-hero-container">
            <div class="banho-hero-content">
                <div class="banho-hero-text">
                    <h1 class="banho-hero-title">
                        プロフェッショナル<br>
                        B2B調達プラットフォーム
                    </h1>
                    <p class="banho-hero-subtitle">
                        万方商事が提供する包括的なB2B調達ソリューション。<br>
                        Alibaba商品の仕入れから注文管理、物流追跡まで、<br>
                        ワンストップでビジネスを効率化します。
                    </p>
                    <div class="banho-hero-actions">
                        <a href="/auth" class="banho-btn-hero banho-btn-hero-primary">
                            無料で始める
                            <span>→</span>
                        </a>
                        <a href="/docs" class="banho-btn-hero banho-btn-hero-secondary">
                            APIドキュメント
                            <span>→</span>
                        </a>
                    </div>
                </div>
                <div class="banho-hero-card">
                    <h3 class="banho-hero-card-title">クイック登録</h3>
                    <form style="text-align: left;">
                        <div style="margin-bottom: var(--space-md);">
                            <label style="display: block; margin-bottom: var(--space-sm); font-weight: 600; color: var(--gray-700);">会社名</label>
                            <input type="text" placeholder="株式会社〇〇" style="width: 100%; padding: var(--space-sm); border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 14px;">
                        </div>
                        <div style="margin-bottom: var(--space-md);">
                            <label style="display: block; margin-bottom: var(--space-sm); font-weight: 600; color: var(--gray-700);">担当者名</label>
                            <input type="text" placeholder="山田 太郎" style="width: 100%; padding: var(--space-sm); border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 14px;">
                        </div>
                        <div style="margin-bottom: var(--space-md);">
                            <label style="display: block; margin-bottom: var(--space-sm); font-weight: 600; color: var(--gray-700);">メールアドレス</label>
                            <input type="email" placeholder="example@company.jp" style="width: 100%; padding: var(--space-sm); border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 14px;">
                        </div>
                        <button type="submit" class="banho-btn-primary" style="width: 100%; padding: var(--space-md); font-size: 16px;">
                            アカウント作成
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- 企业级特性区域 -->
    <section class="banho-features" id="features">
        <div class="banho-features-container">
            <div class="banho-section-header">
                <h2 class="banho-section-title">包括的なサービス</h2>
                <p class="banho-section-subtitle">
                    B2B調達のあらゆるニーズに対応する、包括的なソリューションを提供します
                </p>
            </div>
            <div class="banho-features-grid">
                <div class="banho-feature-card">
                    <div class="banho-feature-icon">🛒</div>
                    <h3 class="banho-feature-title">商品仕入れ</h3>
                    <p class="banho-feature-description">
                        Alibabaをはじめとする主要サプライヤーから、
                        優良商品を直接仕入れ。価格交渉から品質管理まで、
                        専門チームがサポートします。
                    </p>
                </div>
                <div class="banho-feature-card">
                    <div class="banho-feature-icon">📦</div>
                    <h3 class="banho-feature-title">注文管理</h3>
                    <p class="banho-feature-description">
                        リアルタイム注文追跡、在庫管理、
                        自動発注システムで、
                        調達業務を大幅に効率化。
                    </p>
                </div>
                <div class="banho-feature-card">
                    <div class="banho-feature-icon">🚢</div>
                    <h3 class="banho-feature-title">物流追跡</h3>
                    <p class="banho-feature-description">
                        海外物流を完全可視化。
                        輸送状況をリアルタイムで把握し、
                        納期管理を最適化します。
                    </p>
                </div>
                <div class="banho-feature-card">
                    <div class="banho-feature-icon">💰</div>
                    <h3 class="banho-feature-title">決済システム</h3>
                    <p class="banho-feature-description">
                        多様な決済方法に対応。
                        為替リスク管理、
                        資金繰り最適化をサポート。
                    </p>
                </div>
                <div class="banho-feature-card">
                    <div class="banho-feature-icon">📊</div>
                    <h3 class="banho-feature-title">データ分析</h3>
                    <p class="banho-feature-description">
                        調達データを分析・可視化。
                        コスト削減機会を特定し、
                        戦略的意思決定を支援。
                    </p>
                </div>
                <div class="banho-feature-card">
                    <div class="banho-feature-icon">🎯</div>
                    <h3 class="banho-feature-title">コンサルティング</h3>
                    <p class="banho-feature-description">
                        調達専門家によるコンサルティング。
                        サプライヤー選定から契約交渉まで、
                        全プロセスを支援します。
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 企业级统计数据区域 -->
    <section class="banho-stats" id="stats">
        <div class="banho-stats-container">
            <div class="banho-section-header">
                <h2 class="banho-section-title" style="color: white;">実績紹介</h2>
                <p class="banho-section-subtitle" style="color: rgba(255, 255, 255, 0.9);">
                    信頼の実績で、お客様のビジネス成長を支援
                </p>
            </div>
            <div class="banho-stats-grid">
                <div class="banho-stat-item">
                    <span class="banho-stat-number">10,000+</span>
                    <span class="banho-stat-label">取引企業数</span>
                </div>
                <div class="banho-stat-item">
                    <span class="banho-stat-number">50,000+</span>
                    <span class="banho-stat-label">取扱商品数</span>
                </div>
                <div class="banho-stat-item">
                    <span class="banho-stat-number">¥100億+</span>
                    <span class="banho-stat-label">年間取引高</span>
                </div>
                <div class="banho-stat-item">
                    <span class="banho-stat-number">99.9%</span>
                    <span class="banho-stat-label">顧客満足度</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 企业级CTA区域 -->
    <section class="banho-cta">
        <div class="banho-cta-container">
            <h2 class="banho-cta-title">さあ、始めましょう</h2>
            <p class="banho-cta-subtitle">
                プロフェッショナルB2B調達プラットフォームで、<br>
                あなたのビジネスを次のレベルへ。今すぐ無料で登録し、<br>
                調達業務の効率化を実現しましょう。
            </p>
            <div style="display: flex; gap: var(--space-lg); justify-content: center; flex-wrap: wrap;">
                <a href="/auth" class="banho-btn-hero banho-btn-hero-primary">
                    無料で始める
                    <span>→</span>
                </a>
                <a href="/docs" class="banho-btn-hero banho-btn-hero-secondary">
                    デモをリクエスト
                    <span>→</span>
                </a>
            </div>
        </div>
    </section>

    <script>
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

        // 导航栏滚动效果
        let lastScrollTop = 0;
        const navbar = document.querySelector('.banho-navbar');
        
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // 向下滚动
                navbar.style.transform = 'translateY(-100%)';
            } else {
                // 向上滚动
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });

        // 数字动画效果
        const animateNumbers = () => {
            const numbers = document.querySelectorAll('.banho-stat-number');
            
            numbers.forEach(number => {
                const finalText = number.textContent;
                const finalValue = parseFloat(finalText.replace(/[^0-9.]/g, ''));
                const suffix = finalText.replace(/[0-9.]/g, '');
                
                let currentValue = 0;
                const increment = finalValue / 50;
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    
                    if (suffix === '億') {
                        number.textContent = `¥${Math.floor(currentValue)}${suffix}`;
                    } else if (suffix === '%') {
                        number.textContent = `${currentValue.toFixed(1)}${suffix}`;
                    } else {
                        number.textContent = `${Math.floor(currentValue).toLocaleString()}${suffix}`;
                    }
                }, 30);
            });
        };

        // 统计区域进入视口时触发动画
        const statsSection = document.querySelector('.banho-stats');
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateNumbers();
                    statsObserver.unobserve(entry.target);
                }
            });
        });

        statsObserver.observe(statsSection);
    </script>
</body>
</html>