<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员后台 - RAKUMART × 1688</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #2c3e50;
            --admin-secondary: #34495e;
            --admin-light: #ecf0f1;
            --admin-success: #27ae60;
            --admin-warning: #f39c12;
            --admin-danger: #e74c3c;
            --admin-info: #3498db;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--admin-light);
            margin: 0;
            padding: 0;
        }

        /* 管理员顶部导航栏 */
        .admin-navbar {
            background-color: var(--admin-primary);
            color: white;
            padding: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .admin-logo {
            font-size: 20px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .admin-logo span {
            color: #ff6a00;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
        }

        /* 管理员侧边栏 */
        .admin-sidebar {
            background-color: var(--admin-secondary);
            min-height: calc(100vh - 70px);
            color: white;
            padding: 20px 0;
        }

        .admin-sidebar-item {
            padding: 12px 20px;
            color: #bdc3c7;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-sidebar-item:hover {
            background-color: var(--admin-primary);
            color: white;
        }

        .admin-sidebar-item.active {
            background-color: var(--admin-primary);
            color: white;
            border-left-color: var(--admin-info);
        }

        .admin-sidebar-group {
            margin-top: 20px;
        }

        .admin-sidebar-group-title {
            font-weight: bold;
            color: #95a5a6;
            padding: 10px 20px 5px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .admin-main-content {
            padding: 20px;
        }

        .admin-page-header {
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid var(--admin-info);
        }

        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .admin-stat-card {
            background-color: white;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border-top: 3px solid var(--admin-info);
        }

        .admin-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .admin-stat-card.success {
            border-top-color: var(--admin-success);
        }

        .admin-stat-card.warning {
            border-top-color: var(--admin-warning);
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
        }
    </style>
</head>
<body>
    <!-- 管理员顶部导航栏 -->
    <nav class="admin-navbar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="/admin" class="admin-logo">
                        <i class="bi bi-shield-check me-2"></i>RAKUMART <span>× 1688</span> 管理后台
                    </a>
                </div>
                <div class="col-md-9">
                    <div class="admin-user-info justify-content-end">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-bell"></i>
                            <span>通知</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <img src="https://via.placeholder.com/32x32" alt="管理员头像" class="rounded-circle">
                            <span>管理员 (ID: ADMIN001)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="/" class="text-decoration-none text-white">
                                <i class="bi bi-house-door me-1"></i>返回前台
                            </a>
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
            // 订单趋势图
            const orderCtx = document.getElementById('orderChart').getContext('2d');
            new Chart(orderCtx, {
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
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // 订单状态分布图
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
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
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

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