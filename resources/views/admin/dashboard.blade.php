<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员后台 - 雅虎B2B和风管理平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="/css/japanese-effects.css" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #1a1a1a;
            --admin-secondary: #2C2C2C;
            --admin-light: #FFF8F0;
            --admin-success: #27ae60;
            --admin-warning: #f39c12;
            --admin-danger: #C00000;
            --admin-info: #3498db;
            --gold-accent: #D4AF37;
            --sakura-pink: #FFB7C5;
            --bamboo-green: #4A7C59;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--admin-light);
            margin: 0;
            padding: 0;
            color: var(--admin-secondary);
            line-height: 1.6;
        }

        /* 管理员专用和风背景 */
        .admin-pattern {
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(26, 26, 26, 0.02) 35px, rgba(26, 26, 26, 0.02) 70px),
                repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(212, 175, 55, 0.02) 35px, rgba(212, 175, 55, 0.02) 70px);
        }

        /* 高端管理员顶部导航栏 */
        .admin-navbar {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }

        .admin-navbar::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--admin-danger), var(--gold-accent), var(--admin-info));
        }

        .admin-logo {
            font-family: 'Noto Serif JP', serif;
            font-size: 22px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            position: relative;
        }

        .admin-logo::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gold-accent);
            transition: width 0.3s ease;
        }

        .admin-logo:hover::after {
            width: 100%;
        }

        .admin-logo span {
            color: var(--gold-accent);
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            color: white;
        }

        .admin-badge {
            background: var(--gold-accent);
            color: var(--admin-primary);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* 高端管理员侧边栏 */
        .admin-sidebar {
            background: linear-gradient(180deg, var(--admin-secondary) 0%, #1a1a1a 100%);
            min-height: calc(100vh - 85px);
            color: white;
            padding: 30px 0;
            position: relative;
        }

        .admin-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 2px;
            height: 100%;
            background: linear-gradient(180deg, var(--admin-danger), var(--gold-accent), var(--admin-info));
        }

        .admin-sidebar-item {
            padding: 15px 25px;
            color: #bdc3c7;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            font-weight: 500;
        }

        .admin-sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: linear-gradient(180deg, var(--admin-danger), var(--gold-accent));
            transition: width 0.4s ease;
        }

        .admin-sidebar-item:hover {
            background: rgba(192, 0, 0, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .admin-sidebar-item:hover::before {
            width: 4px;
        }

        .admin-sidebar-item.active {
            background: rgba(192, 0, 0, 0.2);
            color: white;
            border-left-color: var(--gold-accent);
        }

        .admin-sidebar-item.active::before {
            width: 4px;
        }

        .admin-sidebar-item i {
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .admin-sidebar-item:hover i {
            transform: scale(1.1) rotate(5deg);
        }

        .admin-sidebar-group {
            margin-top: 30px;
        }

        .admin-sidebar-group-title {
            font-weight: 700;
            color: var(--gold-accent);
            padding: 10px 25px 5px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }

        .admin-sidebar-group-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25px;
            right: 25px;
            height: 1px;
            background: linear-gradient(90deg, var(--admin-danger), var(--gold-accent));
        }

        .admin-main-content {
            padding: 30px;
            background: var(--admin-light);
            min-height: calc(100vh - 85px);
        }

        .admin-page-header {
            background: linear-gradient(135deg, white 0%, var(--admin-light) 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }

        .admin-page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(180deg, var(--admin-danger), var(--gold-accent), var(--admin-info));
        }

        .admin-page-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--admin-secondary);
            margin-bottom: 10px;
        }

        .admin-page-subtitle {
            color: var(--admin-secondary);
            opacity: 0.7;
            font-size: 14px;
            font-weight: 500;
        }

        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .admin-stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .admin-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--admin-info), var(--admin-danger));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .admin-stat-card:hover::before {
            transform: scaleX(1);
        }

        .admin-stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }

        .admin-stat-card.success::before {
            background: linear-gradient(90deg, var(--admin-success), var(--bamboo-green));
        }

        .admin-stat-card.warning::before {
            background: linear-gradient(90deg, var(--admin-warning), #e67e22);
        }

        .admin-stat-card.danger::before {
            background: linear-gradient(90deg, var(--admin-danger), #c0392b);
        }

        .admin-stat-card.danger {
            border-top-color: var(--admin-danger);
        }

        .admin-stat-icon {
            font-size: 32px;
            color: var(--admin-info);
            margin-bottom: 10px;
        }

        .admin-stat-card.success .admin-stat-icon {
            color: var(--admin-success);
        }

        .admin-stat-card.warning .admin-stat-icon {
            color: var(--admin-warning);
        }

        .admin-stat-card.danger .admin-stat-icon {
            color: var(--admin-danger);
        }

        .admin-stat-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--admin-primary);
        }

        .admin-stat-label {
            font-size: 14px;
            color: #7f8c8d;
            margin: 0;
        }

        .admin-data-table {
            background-color: white;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .admin-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .admin-badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .admin-badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .admin-badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .admin-btn-primary {
            background-color: var(--admin-info);
            border-color: var(--admin-info);
        }

        .admin-btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .admin-alert {
            border-left: 4px solid var(--admin-info);
        }

        .admin-chart-container {
            background-color: white;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            /* 强制固定样式，防止被外部动效影响 */
            transform: none !important;
            will-change: auto;
        }

        /* 图表Canvas容器强制样式 */
        .admin-chart-container canvas {
            transform: none !important;
            position: relative !important;
            max-height: 300px;
        }

        /* 防止任何transform影响图表 */
        .admin-chart-container *,
        .admin-chart-container *::before,
        .admin-chart-container *::after {
            transform: none !important;
        }
    </style>
</head>
<body class="admin-pattern">
    <!-- 高端管理员顶部导航栏 -->
    <nav class="admin-navbar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="/" class="admin-logo">
                        雅虎B2B <span>× 和风管理</span>
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="text-center">
                        <span class="admin-badge">管理员控制台</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-user-info justify-content-end">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--admin-danger), var(--gold-accent)); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                管
                            </div>
                            <div>
                                <div style="color: white; font-weight: 600;">系统管理员</div>
                                <div style="color: #bdc3c7; font-size: 12px;">超级权限</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- 管理员左侧导航栏 -->
            <div class="col-md-2 admin-sidebar">
                <div class="admin-sidebar-item active">
                    <i class="bi bi-speedometer2"></i>
                    <span>仪表板</span>
                </div>
                
                <div class="admin-sidebar-group">
                    <div class="admin-sidebar-group-title">用户管理</div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-people"></i>
                        <span>用户列表</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-person-plus"></i>
                        <span>用户审核</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-shield-exclamation"></i>
                        <span>权限管理</span>
                    </div>
                </div>

                <div class="admin-sidebar-group">
                    <div class="admin-sidebar-group-title">订单管理</div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-cart-check"></i>
                        <span>订单概览</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-truck"></i>
                        <span>物流管理</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>异常订单</span>
                    </div>
                </div>

                <div class="admin-sidebar-group">
                    <div class="admin-sidebar-group-title">产品管理</div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-box"></i>
                        <span>产品列表</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-tags"></i>
                        <span>分类管理</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>库存同步</span>
                    </div>
                </div>

                <div class="admin-sidebar-group">
                    <div class="admin-sidebar-group-title">财务管理</div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-currency-yen"></i>
                        <span>费用统计</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-credit-card"></i>
                        <span>支付管理</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-file-text"></i>
                        <span>发票管理</span>
                    </div>
                </div>

                <div class="admin-sidebar-group">
                    <div class="admin-sidebar-group-title">系统设置</div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-gear"></i>
                        <span>系统配置</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-clock-history"></i>
                        <span>操作日志</span>
                    </div>
                    <div class="admin-sidebar-item">
                        <i class="bi bi-graph-up"></i>
                        <span>数据统计</span>
                    </div>
                </div>
            </div>

            <!-- 管理员主内容区 -->
            <div class="col-md-10 admin-main-content">
                <!-- 页面标题 -->
                <div class="admin-page-header">
                    <h2><i class="bi bi-speedometer2 me-2"></i>管理员仪表板</h2>
                    <p class="text-muted mb-0">系统概览和关键指标监控</p>
                </div>

                <!-- 统计卡片 -->
                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="admin-stat-number" id="total-users">156</div>
                        <p class="admin-stat-label">总用户数</p>
                    </div>
                    
                    <div class="admin-stat-card success">
                        <div class="admin-stat-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="admin-stat-number" id="total-orders">1,234</div>
                        <p class="admin-stat-label">总订单数</p>
                    </div>
                    
                    <div class="admin-stat-card warning">
                        <div class="admin-stat-icon">
                            <i class="bi bi-currency-yen"></i>
                        </div>
                        <div class="admin-stat-number" id="total-revenue">¥89,456</div>
                        <p class="admin-stat-label">总收入</p>
                    </div>
                    
                    <div class="admin-stat-card danger">
                        <div class="admin-stat-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="admin-stat-number" id="pending-shipments">23</div>
                        <p class="admin-stat-label">待发货订单</p>
                    </div>
                </div>

                <!-- 图表区域 -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="admin-chart-container">
                            <h5><i class="bi bi-graph-up me-2"></i>订单趋势</h5>
                            <canvas id="orderChart" height="100"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-chart-container">
                            <h5><i class="bi bi-pie-chart me-2"></i>订单状态分布</h5>
                            <canvas id="statusChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 最新活动 -->
                <div class="admin-data-table">
                    <h5><i class="bi bi-clock-history me-2"></i>最新活动</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>时间</th>
                                    <th>用户</th>
                                    <th>操作</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="recent-activities">
                                <tr>
                                    <td>2025-12-02 12:30</td>
                                    <td>用户001</td>
                                    <td>创建订单 YO-20251202-00001</td>
                                    <td><span class="admin-badge admin-badge-success">成功</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">查看</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-12-02 12:15</td>
                                    <td>用户002</td>
                                    <td>注册账户</td>
                                    <td><span class="admin-badge admin-badge-warning">待审核</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success">审核</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-12-02 11:45</td>
                                    <td>用户003</td>
                                    <td>订单发货</td>
                                    <td><span class="admin-badge admin-badge-success">已发货</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info">追踪</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 系统状态 -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="admin-data-table">
                            <h5><i class="bi bi-cpu me-2"></i>系统状态</h5>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>API 服务:</strong> <span class="text-success">正常</span></p>
                                    <p><strong>数据库:</strong> <span class="text-success">正常</span></p>
                                    <p><strong>缓存:</strong> <span class="text-success">正常</span></p>
                                </div>
                                <div class="col-6">
                                    <p><strong>队列:</strong> <span class="text-warning">延迟 2 分钟</span></p>
                                    <p><strong>存储:</strong> <span class="text-success">68% 使用</span></p>
                                    <p><strong>内存:</strong> <span class="text-success">45% 使用</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-data-table">
                            <h5><i class="bi bi-bell me-2"></i>系统通知</h5>
                            <div class="alert alert-info admin-alert" role="alert">
                                <i class="bi bi-info-circle me-2"></i>
                                系统将于今晚 23:00 进行例行维护，预计持续 30 分钟。
                            </div>
                            <div class="alert alert-warning admin-alert" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                队列处理延迟，请检查队列配置。
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/japanese-interactions.js"></script>
    <script>
        // API 配置
        const API_BASE = 'http://localhost:8000/api';
        
        // 页面加载时获取数据
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            initCharts();
        });

        // 加载仪表板数据
        async function loadDashboardData() {
            try {
                // 模拟管理员数据
                const adminStats = {
                    totalUsers: 156,
                    totalOrders: 1234,
                    totalRevenue: 89456,
                    pendingShipments: 23
                };

                // 更新页面数据
                updateAdminStats(adminStats);
                
            } catch (error) {
                console.error('加载管理员数据失败:', error);
            }
        }

        // 更新管理员统计数据
        function updateAdminStats(stats) {
            document.getElementById('total-users').textContent = stats.totalUsers;
            document.getElementById('total-orders').textContent = stats.totalOrders.toLocaleString();
            document.getElementById('total-revenue').textContent = '¥' + stats.totalRevenue.toLocaleString();
            document.getElementById('pending-shipments').textContent = stats.pendingShipments;
        }

        // 初始化图表
        function initCharts() {
            // 延迟初始化，确保DOM完全加载
            setTimeout(() => {
                try {
                    // 订单趋势图
                    const orderCtx = document.getElementById('orderChart');
                    if (orderCtx) {
                        orderChart = new Chart(orderCtx.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
                                datasets: [{
                                    label: '订单数量',
                                    data: [65, 78, 90, 120, 156, 189, 234, 267, 298, 345, 398, 456],
                                    borderColor: '#3498db',
                                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: {
                                    duration: 1000,
                                    easing: 'easeInOutQuart'
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.05)'
                                        },
                                        ticks: {
                                            precision: 0
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    }
                                },
                                interaction: {
                                    intersect: false,
                                    mode: 'index'
                                }
                            }
                        });
                    }

                    // 订单状态分布图
                    const statusCtx = document.getElementById('statusChart');
                    if (statusCtx) {
                        statusChart = new Chart(statusCtx.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['待处理', '处理中', '已发货', '已完成', '已取消'],
                                datasets: [{
                                    data: [23, 45, 67, 89, 12],
                                    backgroundColor: [
                                        '#f39c12',
                                        '#3498db',
                                        '#2ecc71',
                                        '#27ae60',
                                        '#e74c3c'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: {
                                    animateRotate: true,
                                    animateScale: true,
                                    duration: 1000,
                                    easing: 'easeInOutQuart'
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 15,
                                            font: {
                                                size: 12
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                } catch (error) {
                    console.error('管理员后台图表初始化失败:', error);
                }
            }, 500);
        }

        // 图表保护函数 - 确保图表不被外部样式影响
        function protectAdminCharts() {
            const chartContainers = document.querySelectorAll('.admin-chart-container');
            chartContainers.forEach(container => {
                // 重置任何可能影响图表的样式
                container.style.transform = 'none';
                container.style.position = 'relative';
                
                // 保护canvas元素
                const canvas = container.querySelector('canvas');
                if (canvas) {
                    canvas.style.transform = 'none';
                    canvas.style.position = 'relative';
                    
                    // 如果图表已存在，强制更新
                    if (canvas.chart) {
                        canvas.chart.update('none');
                    }
                }
            });
        }

        // 定期保护图表
        setInterval(protectAdminCharts, 1000);

        // 页面可见性变化时保护图表
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                setTimeout(protectAdminCharts, 100);
            }
        });

        // 侧边栏点击事件
        document.querySelectorAll('.admin-sidebar-item').forEach(item => {
            item.addEventListener('click', function() {
                // 移除所有激活状态
                document.querySelectorAll('.admin-sidebar-item').forEach(i => i.classList.remove('active'));
                // 添加激活状态到当前项
                this.classList.add('active');
            });
        });

        // 定期刷新数据（每60秒）
        setInterval(loadDashboardData, 60000);
    </script>
</body>
</html>