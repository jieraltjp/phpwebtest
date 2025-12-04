<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード | 万方商事 B2B 采购门户</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&family=Noto+Serif+JP:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/banho-theme.css', 'resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            background: var(--gray-50);
            overflow-y: auto;
        }
        
        .dashboard-header {
            background: white;
            padding: var(--space-lg) var(--space-xl);
            border-bottom: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }
        
        .dashboard-stats {
            padding: var(--space-xl);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-xl);
            margin-bottom: var(--space-2xl);
        }
        
        .chart-container {
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--space-xl);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--space-xl);
        }
        
        .chart-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: var(--space-lg);
        }
        
        .chart-title {
            font-family: var(--font-family-heading);
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--banho-primary);
        }
        
        .quick-actions {
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--space-xl);
            box-shadow: var(--shadow-md);
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-lg);
            margin-top: var(--space-lg);
        }
        
        .action-card {
            text-align: center;
            padding: var(--space-lg);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            transition: all var(--transition-base);
            cursor: pointer;
        }
        
        .action-card:hover {
            border-color: var(--banho-primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto var(--space-md);
            background: linear-gradient(135deg, var(--banho-primary) 0%, var(--banho-primary-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--banho-secondary) 0%, var(--banho-secondary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--banho-primary);
            font-weight: 600;
            cursor: pointer;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--error);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .dashboard-layout {
                flex-direction: column;
            }
            
            .banho-sidebar {
                min-height: auto;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- サイドバー -->
        <aside class="banho-sidebar">
            <div class="banho-sidebar-header">
                <div class="flex items-center gap-3">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="2"/>
                        <path d="M16 8 L16 16 L24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span class="font-bold text-lg">万方商事</span>
                </div>
            </div>
            
            <nav class="banho-sidebar-nav">
                <a href="/dashboard" class="banho-sidebar-nav-item active">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    ダッシュボード
                </a>
                
                <a href="/products" class="banho-sidebar-nav-item">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    商品検索
                </a>
                
                <a href="/orders" class="banho-sidebar-nav-item">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    注文管理
                </a>
                
                <a href="/inquiries" class="banho-sidebar-nav-item">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    見積もり
                </a>
                
                <a href="/bulk-purchase" class="banho-sidebar-nav-item">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707L17 14h6"/>
                    </svg>
                    バルク購入
                </a>
                
                <a href="/analytics" class="banho-sidebar-nav-item">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    分析レポート
                </a>
            </nav>
        </aside>

        <!-- メインコンテンツ -->
        <main class="main-content">
            <!-- ヘッダー -->
            <header class="dashboard-header">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-banho-primary">ダッシュボード</h1>
                        <p class="text-gray-600 mt-1">ようこそ、田中様</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- 通知 -->
                        <div class="relative">
                            <button class="p-2 text-gray-600 hover:text-banho-primary transition">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 10v4.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </button>
                            <span class="notification-badge">3</span>
                        </div>
                        
                        <!-- ユーザーメニュー -->
                        <div class="user-menu">
                            <div class="user-avatar">
                                田中
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- 統計カード -->
            <section class="dashboard-stats">
                <div class="dashboard-grid">
                    <div class="banho-stat-card">
                        <div class="banho-stat-value">¥2,450,000</div>
                        <div class="banho-stat-label">今月の総取引額</div>
                        <div class="mt-2 text-sm text-green-600">↑ 12.5% 先月比</div>
                    </div>
                    
                    <div class="banho-stat-card">
                        <div class="banho-stat-value">45</div>
                        <div class="banho-stat-label">アクティブ注文</div>
                        <div class="mt-2 text-sm text-blue-600">15件 処待中</div>
                    </div>
                    
                    <div class="banho-stat-card">
                        <div class="banho-stat-value">128</div>
                        <div class="banho-stat-label">取扱い商品</div>
                        <div class="mt-2 text-sm text-purple-600">23件 在庫切れ</div>
                    </div>
                    
                    <div class="banho-stat-card">
                        <div class="banho-stat-value">98.5%</div>
                        <div class="banho-stat-label">納期遵守率</div>
                        <div class="mt-2 text-sm text-green-600">↑ 2.1% 前四半期比</div>
                    </div>
                </div>

                <!-- チャート -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">取引トレンド</h3>
                        <select class="banho-form-control w-auto">
                            <option>過去6ヶ月</option>
                            <option>過去3ヶ月</option>
                            <option>過去1ヶ月</option>
                        </select>
                    </div>
                    <canvas id="salesChart" height="100"></canvas>
                </div>

                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">商品カテゴリー別売上</h3>
                        <button class="btn-banho btn-banho-outline btn-sm">エクスポート</button>
                    </div>
                    <canvas id="categoryChart" height="100"></canvas>
                </div>

                <!-- クィックアクション -->
                <div class="quick-actions">
                    <h3 class="text-xl font-semibold mb-4 text-banho-primary">クイックアクション</h3>
                    <div class="action-grid">
                        <div class="action-card">
                            <div class="action-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold">新規注文</h4>
                            <p class="text-sm text-gray-600 mt-1">迅速な注文作成</p>
                        </div>
                        
                        <div class="action-card">
                            <div class="action-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold">商品検索</h4>
                            <p class="text-sm text-gray-600 mt-1">10万+商品</p>
                        </div>
                        
                        <div class="action-card">
                            <div class="action-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold">見積もり</h4>
                            <p class="text-sm text-gray-600 mt-1">価格交渉</p>
                        </div>
                        
                        <div class="action-card">
                            <div class="action-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h4 class="font-semibold">レポート</h4>
                            <p class="text-sm text-gray-600 mt-1">詳細分析</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // 売上トレンドチャート
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['7月', '8月', '9月', '10月', '11月', '12月'],
                datasets: [{
                    label: '売上高 (万円)',
                    data: [185, 210, 195, 220, 245, 280],
                    borderColor: '#1a365d',
                    backgroundColor: 'rgba(26, 54, 93, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '¥' + value + '万';
                            }
                        }
                    }
                }
            }
        });

        // カテゴリ別チャート
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['電子製品', '衣類', '家庭用品', '工業部品', 'その他'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        '#1a365d',
                        '#2c5282',
                        '#d4af37',
                        '#f4e4bc',
                        '#94a3b8'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // インタラクティブ機能
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('h4').textContent;
                console.log('Action clicked:', title);
                // ここに各アクションの処理を追加
            });
        });

        // リアルタイムデータ更新（シミュレーション）
        setInterval(() => {
            // 注文数の更新
            const orderCount = document.querySelector('.banho-stat-card:nth-child(2) .banho-stat-value');
            const currentCount = parseInt(orderCount.textContent);
            const change = Math.floor(Math.random() * 5) - 2;
            orderCount.textContent = Math.max(0, currentCount + change);
        }, 5000);
    </script>
</body>
</html>