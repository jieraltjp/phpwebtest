<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单管理 - 雅虎B2B和风采购平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="/css/japanese-effects.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .exchange-rate {
            border: 1px solid var(--primary-red);
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 14px;
            background: rgba(192, 0, 0, 0.1);
            color: var(--primary-red);
            font-weight: 500;
        }

        /* 高端左侧导航栏 */
        .sidebar {
            background: linear-gradient(180deg, var(--sumi-black) 0%, #1a1a1a 100%);
            min-height: calc(100vh - 85px);
            border-right: 2px solid var(--primary-red);
            padding: 30px 0;
            position: relative;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
        }

        .sidebar-item {
            padding: 15px 25px;
            color: #bdc3c7;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            position: relative;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: linear-gradient(180deg, var(--primary-red), var(--primary-orange));
            transition: width 0.4s ease;
        }

        .sidebar-item:hover {
            background: rgba(192, 0, 0, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-item:hover::before {
            width: 4px;
        }

        .sidebar-item.active {
            background: rgba(192, 0, 0, 0.2);
            color: white;
            border-left-color: var(--gold-accent);
        }

        .sidebar-item.active::before {
            width: 4px;
        }

        .sidebar-item a {
            color: inherit;
            text-decoration: none;
        }

        /* 高端主内容区域 */
        .main-content {
            padding: 30px;
            background: var(--washi-white);
            min-height: calc(100vh - 85px);
        }

        .page-header {
            margin-bottom: 40px;
            position: relative;
        }

        .page-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--sumi-black);
            margin-bottom: 15px;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
        }

        /* 统计卡片 */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(192, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        .stat-number {
            font-family: 'Noto Serif JP', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--sumi-black);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-gray);
            font-weight: 500;
        }

        /* 图表容器 */
        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            /* 强制固定样式，防止被外部动效影响 */
            transform: none !important;
            will-change: auto;
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
        }

        .chart-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--sumi-black);
            margin-bottom: 20px;
        }

        /* 图表Canvas容器强制样式 */
        .chart-container canvas {
            transform: none !important;
            position: relative !important;
            max-height: 300px;
        }

        /* 防止任何transform影响图表 */
        .chart-container *,
        .chart-container *::before,
        .chart-container *::after {
            transform: none !important;
        }

        /* 筛选栏 */
        .filter-bar {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.2rem rgba(192, 0, 0, 0.25);
        }

        /* 订单卡片 */
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .order-card:hover::before {
            transform: scaleX(1);
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(192, 0, 0, 0.15);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { 
            background: linear-gradient(135deg, #fff3cd, #ffeaa7); 
            color: #856404; 
            border: 1px solid #ffeaa7;
        }
        .status-processing { 
            background: linear-gradient(135deg, #cfe2ff, #74b9ff); 
            color: #084298; 
            border: 1px solid #74b9ff;
        }
        .status-shipped { 
            background: linear-gradient(135deg, #d1ecf1, #81ecec); 
            color: #0c5460; 
            border: 1px solid #81ecec;
        }
        .status-delivered { 
            background: linear-gradient(135deg, #d4edda, #55efc4); 
            color: #155724; 
            border: 1px solid #55efc4;
        }
        .status-returned { 
            background: linear-gradient(135deg, #f8d7da, #fab1a0); 
            color: #721c24; 
            border: 1px solid #fab1a0;
        }
        .status-cancelled { 
            background: linear-gradient(135deg, #e2e3e5, #dfe6e9); 
            color: #383d41; 
            border: 1px solid #dfe6e9;
        }

        /* 高级按钮 */
        .btn-japanese {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-japanese::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-japanese:hover::before {
            left: 100%;
        }

        .btn-japanese:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(192, 0, 0, 0.3);
        }

        /* 模态框 */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--sumi-black) 0%, #1a1a1a 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        /* 分页 */
        .pagination .page-link {
            border: none;
            color: var(--primary-red);
            margin: 0 3px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: var(--primary-red);
            color: white;
            transform: translateY(-2px);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            border: none;
        }

        /* 加载动画 */
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid var(--washi-white);
            border-top: 4px solid var(--primary-red);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 空状态 */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-gray);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--medium-gray);
            margin-bottom: 20px;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .order-card {
                padding: 20px;
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
                        <div class="exchange-rate">
                            1元=23.01日元
                            <i class="bi bi-question-circle"></i>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-red), var(--primary-orange)); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                用
                            </div>
                            <div>
                                <div style="color: white; font-weight: 500;">尊敬的用户</div>
                                <div style="color: #bdc3c7; font-size: 12px;">ID: 331275</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- 高端左侧导航栏 -->
            <div class="col-md-2 sidebar">
                <div class="sidebar-item">
                    <a href="/dashboard" class="text-decoration-none">
                        <i class="bi bi-house-door me-2"></i>仪表板
                    </a>
                </div>
                
                <div class="sidebar-item">
                    <a href="/products" class="text-decoration-none">
                        <i class="bi bi-box me-2"></i>产品管理
                    </a>
                </div>
                
                <div class="sidebar-item active">
                    <i class="bi bi-cart3 me-2"></i>订单管理
                </div>

                <div class="sidebar-item">
                    <a href="/admin" class="text-decoration-none">
                        <i class="bi bi-gear me-2"></i>管理后台
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="/docs" class="text-decoration-none">
                        <i class="bi bi-file-text me-2"></i>API文档
                    </a>
                </div>
            </div>

            <!-- 主内容区 -->
            <div class="col-md-10 main-content">
                <!-- 页面标题 -->
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="bi bi-cart3 me-3"></i>订单管理中心
                    </h1>
                    <p class="text-muted">管理您的采购订单、追踪物流状态、查看数据分析</p>
                </div>

                <!-- 统计卡片 -->
                <div class="stats-grid">
                    <div class="stat-card hover-float">
                        <div class="stat-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="stat-number" id="totalOrders">0</div>
                        <div class="stat-label">总订单数</div>
                    </div>
                    
                    <div class="stat-card hover-float">
                        <div class="stat-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-number" id="pendingOrders">0</div>
                        <div class="stat-label">待处理</div>
                    </div>
                    
                    <div class="stat-card hover-float">
                        <div class="stat-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="stat-number" id="shippedOrders">0</div>
                        <div class="stat-label">已发货</div>
                    </div>
                    
                    <div class="stat-card hover-float">
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-number" id="completedOrders">0</div>
                        <div class="stat-label">已完成</div>
                    </div>
                </div>

                <!-- 图表区域 -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="chart-container">
                            <h3 class="chart-title">
                                <i class="bi bi-graph-up me-2"></i>订单趋势分析
                            </h3>
                            <canvas id="orderChart" width="400" height="150"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-container">
                            <h3 class="chart-title">
                                <i class="bi bi-pie-chart me-2"></i>订单状态分布
                            </h3>
                            <canvas id="statusChart" width="200" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 筛选栏 -->
                <div class="filter-bar">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">订单状态</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">所有状态</option>
                                <option value="PENDING">待处理</option>
                                <option value="PROCESSING">处理中</option>
                                <option value="SHIPPED">已发货</option>
                                <option value="DELIVERED">已送达</option>
                                <option value="RETURNED">已退回</option>
                                <option value="CANCELLED">已取消</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">订单号</label>
                            <input type="text" class="form-control" id="orderIdFilter" placeholder="输入订单号">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">日期范围</label>
                            <input type="date" class="form-control" id="dateFilter">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-japanese w-100" onclick="filterOrders()">
                                <i class="bi bi-funnel me-2"></i>筛选订单
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 创建新订单按钮 -->
                <div class="mb-4">
                    <button class="btn btn-japanese" onclick="createNewOrder()">
                        <i class="bi bi-plus-circle me-2"></i>创建新订单
                    </button>
                </div>

                <!-- 订单列表 -->
                <div id="ordersList">
                    <div class="text-center py-5">
                        <div class="loading-spinner mx-auto"></div>
                        <p class="mt-3 text-muted">正在加载订单数据...</p>
                    </div>
                </div>

                <!-- 分页 -->
                <nav id="pagination" class="mt-4">
                    <!-- 分页将通过 JavaScript 动态生成 -->
                </nav>
            </div>
        </div>
    </div>

    <!-- 订单详情模态框 -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-text me-2"></i>订单详情
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderModalBody">
                    <!-- 订单详情将通过 JavaScript 动态生成 -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                    <button type="button" class="btn btn-japanese" onclick="trackOrder()">
                        <i class="bi bi-truck me-1"></i>物流追踪
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 创建订单模态框 -->
    <div class="modal fade" id="createOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>创建新订单
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createOrderForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">配送地址</label>
                            <textarea class="form-control" id="shippingAddress" rows="3" required>日本东京都港区测试地址1-2-3</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">订单项目</label>
                            <div id="orderItems">
                                <div class="row mb-2 order-item-row">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="产品SKU" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" placeholder="数量" min="1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="产品名称（自动填充）" readonly>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeOrderItem(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOrderItem()">
                                <i class="bi bi-plus me-1"></i>添加项目
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-japanese" onclick="submitOrder()">
                        <i class="bi bi-check-circle me-1"></i>提交订单
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/japanese-interactions.js"></script>
    <script>
        const API_BASE = 'http://localhost:8000/api';
        let currentPage = 1;
        let totalPages = 1;
        let currentOrderId = null;
        let orderChart = null;
        let statusChart = null;

        // 页面加载时初始化
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
            initCharts();
            loadStatistics();
        });

        // 初始化图表
        function initCharts() {
            // 延迟初始化，确保DOM完全加载
            setTimeout(() => {
                try {
                    // 订单趋势图表
                    const orderCtx = document.getElementById('orderChart');
                    if (orderCtx) {
                        orderChart = new Chart(orderCtx.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: ['1月', '2月', '3月', '4月', '5月', '6月'],
                                datasets: [{
                                    label: '订单数量',
                                    data: [12, 19, 8, 25, 22, 30],
                                    borderColor: '#C00000',
                                    backgroundColor: 'rgba(192, 0, 0, 0.1)',
                                    borderWidth: 3,
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

                    // 订单状态分布图表
                    const statusCtx = document.getElementById('statusChart');
                    if (statusCtx) {
                        statusChart = new Chart(statusCtx.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['待处理', '处理中', '已发货', '已送达'],
                                datasets: [{
                                    data: [30, 25, 20, 25],
                                    backgroundColor: [
                                        '#fff3cd',
                                        '#cfe2ff',
                                        '#d1ecf1',
                                        '#d4edda'
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#fff'
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
                    console.error('图表初始化失败:', error);
                }
            }, 500);
        }

        // 加载统计数据
        async function loadStatistics() {
            try {
                // 模拟统计数据
                const stats = {
                    total: 156,
                    pending: 23,
                    shipped: 45,
                    completed: 88
                };

                // 动画更新数字
                animateNumber('totalOrders', 0, stats.total, 2000);
                animateNumber('pendingOrders', 0, stats.pending, 2000);
                animateNumber('shippedOrders', 0, stats.shipped, 2000);
                animateNumber('completedOrders', 0, stats.completed, 2000);
            } catch (error) {
                console.error('加载统计数据失败:', error);
            }
        }

        // 数字动画效果
        function animateNumber(elementId, start, end, duration) {
            const element = document.getElementById(elementId);
            const increment = (end - start) / (duration / 16);
            let current = start;

            const timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    current = end;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        }

        // 图表保护函数 - 确保图表不被外部样式影响
        function protectCharts() {
            const chartContainers = document.querySelectorAll('.chart-container');
            chartContainers.forEach(container => {
                // 重置任何可能影响图表的样式
                container.style.transform = 'none';
                container.style.position = 'relative';
                
                // 保护 canvas元素
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
        setInterval(protectCharts, 1000);

        // 页面可见性变化时保护图表
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                setTimeout(protectCharts, 100);
            }
        });

        // 加载订单列表
        async function loadOrders(page = 1, filters = {}) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    limit: 10,
                    ...filters
                });

                const response = await fetch(`${API_BASE}/test/orders?${params}`);
                const data = await response.json();

                if (response.ok) {
                    displayOrders(data.data || []);
                    updatePagination(data.current_page || 1, data.last_page || 1);
                } else {
                    throw new Error('获取订单失败');
                }
            } catch (error) {
                console.error('加载订单失败:', error);
                document.getElementById('ordersList').innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h4>加载失败</h4>
                        <p>无法加载订单数据，请稍后重试</p>
                        <button class="btn btn-japanese" onclick="loadOrders()">
                            <i class="bi bi-arrow-clockwise me-2"></i>重新加载
                        </button>
                    </div>
                `;
            }
        }

        // 显示订单列表
        function displayOrders(orders) {
            const container = document.getElementById('ordersList');
            
            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-cart-x"></i>
                        <h4>暂无订单</h4>
                        <p>您还没有任何订单，立即创建您的第一个订单吧！</p>
                        <button class="btn btn-japanese" onclick="createNewOrder()">
                            <i class="bi bi-plus-circle me-2"></i>创建订单
                        </button>
                    </div>
                `;
                return;
            }

            container.innerHTML = orders.map(order => `
                <div class="order-card hover-float">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-2">
                                        <i class="bi bi-receipt me-2"></i>订单号: ${order.order_id}
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-calendar me-1"></i>
                                        创建时间: ${new Date(order.created_at).toLocaleString()}
                                    </p>
                                </div>
                                <span class="status-badge status-${order.status.toLowerCase()}">
                                    ${getStatusText(order.status)}
                                </span>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="bi bi-currency-yen me-1"></i>
                                        <strong>总金额:</strong> ¥${order.total_amount} ${order.currency}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <strong>配送地址:</strong> ${order.shipping_address || '未设置'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-outline-primary btn-sm mb-2 me-2" onclick="viewOrder('${order.order_id}')">
                                <i class="bi bi-eye me-1"></i>查看详情
                            </button>
                            <button class="btn btn-outline-info btn-sm mb-2" onclick="trackOrder('${order.order_id}')">
                                <i class="bi bi-truck me-1"></i>物流追踪
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // 获取状态文本
        function getStatusText(status) {
            const statusMap = {
                'PENDING': '待处理',
                'PROCESSING': '处理中',
                'SHIPPED': '已发货',
                'DELIVERED': '已送达',
                'RETURNED': '已退回',
                'CANCELLED': '已取消'
            };
            return statusMap[status] || status;
        }

        // 更新分页
        function updatePagination(current, last) {
            currentPage = current;
            totalPages = last;
            
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let paginationHtml = '<ul class="pagination justify-content-center">';
            
            // 上一页
            if (currentPage > 1) {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadOrders(${currentPage - 1}); return false;">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>`;
            }

            // 页码
            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadOrders(${i}); return false;">${i}</a>
                </li>`;
            }

            // 下一页
            if (currentPage < totalPages) {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadOrders(${currentPage + 1}); return false;">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>`;
            }

            paginationHtml += '</ul>';
            pagination.innerHTML = paginationHtml;
        }

        // 筛选订单
        function filterOrders() {
            const filters = {
                status: document.getElementById('statusFilter').value,
                order_id: document.getElementById('orderIdFilter').value,
                date: document.getElementById('dateFilter').value
            };

            // 移除空值
            Object.keys(filters).forEach(key => {
                if (!filters[key]) {
                    delete filters[key];
                }
            });

            loadOrders(1, filters);
        }

        // 查看订单详情
        async function viewOrder(orderId) {
            try {
                const response = await fetch(`${API_BASE}/test/orders/${orderId}`);
                const order = await response.json();

                if (response.ok) {
                    currentOrderId = orderId;
                    displayOrderDetails(order);
                    new bootstrap.Modal(document.getElementById('orderModal')).show();
                } else {
                    throw new Error('获取订单详情失败');
                }
            } catch (error) {
                console.error('查看订单详情失败:', error);
                alert('获取订单详情失败，请稍后重试');
            }
        }

        // 显示订单详情
        function displayOrderDetails(order) {
            const modalBody = document.getElementById('orderModalBody');
            
            const itemsHtml = order.items ? order.items.map(item => `
                <div class="order-item p-3 border rounded mb-2">
                    <div class="row">
                        <div class="col-md-3"><strong>SKU:</strong> ${item.sku}</div>
                        <div class="col-md-4"><strong>名称:</strong> ${item.name}</div>
                        <div class="col-md-2"><strong>数量:</strong> ${item.quantity}</div>
                        <div class="col-md-3"><strong>单价:</strong> ¥${item.unit_price}</div>
                    </div>
                </div>
            `).join('') : '<p class="text-muted">暂无订单项目</p>';

            modalBody.innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-info-circle me-2"></i>订单信息
                        </h6>
                        <p><strong>订单号:</strong> ${order.order_id}</p>
                        <p><strong>创建时间:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                        <p><strong>状态:</strong> 
                            <span class="status-badge status-${order.status.toLowerCase()}">${getStatusText(order.status)}</span>
                        </p>
                        <p><strong>状态说明:</strong> ${order.status_message || '无'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-info mb-3">
                            <i class="bi bi-truck me-2"></i>配送信息
                        </h6>
                        <p><strong>配送地址:</strong> ${order.shipping_address || '未设置'}</p>
                        <p><strong>国内追踪号:</strong> ${order.domestic_tracking_number || '无'}</p>
                        <p><strong>国际追踪号:</strong> ${order.international_tracking_number || '无'}</p>
                    </div>
                </div>
                <hr>
                <h6 class="text-success mb-3">
                    <i class="bi bi-box-seam me-2"></i>订单项目
                </h6>
                ${itemsHtml}
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p class="fs-5"><strong>总金额 (CNY):</strong> 
                            <span class="text-danger">¥${order.total_fee_cny}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="fs-5"><strong>总金额 (JPY):</strong> 
                            <span class="text-danger">¥${order.total_fee_jpy}</span>
                        </p>
                    </div>
                </div>
            `;
        }

        // 物流追踪
        function trackOrder(orderId) {
            if (orderId) {
                currentOrderId = orderId;
            }
            
            // 这里可以实现物流追踪功能
            alert(`物流追踪功能正在开发中...\n订单号: ${currentOrderId}`);
        }

        // 创建新订单
        function createNewOrder() {
            document.getElementById('createOrderForm').reset();
            // 重置订单项目
            document.getElementById('orderItems').innerHTML = `
                <div class="row mb-2 order-item-row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="产品SKU" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" placeholder="数量" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="产品名称（自动填充）" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeOrderItem(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('createOrderModal')).show();
        }

        // 添加订单项目
        function addOrderItem() {
            const orderItems = document.getElementById('orderItems');
            const newItem = document.createElement('div');
            newItem.className = 'row mb-2 order-item-row';
            newItem.innerHTML = `
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="产品SKU" required>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" placeholder="数量" min="1" required>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="产品名称（自动填充）" readonly>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeOrderItem(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            orderItems.appendChild(newItem);
        }

        // 移除订单项目
        function removeOrderItem(button) {
            const orderItems = document.getElementById('orderItems');
            if (orderItems.children.length > 1) {
                button.closest('.row').remove();
            } else {
                alert('至少需要一个订单项目');
            }
        }

        // 提交订单
        async function submitOrder() {
            try {
                const shippingAddress = document.getElementById('shippingAddress').value;
                const orderItems = [];
                
                // 收集订单项目
                const itemRows = document.querySelectorAll('.order-item-row');
                itemRows.forEach(row => {
                    const sku = row.querySelector('input[placeholder="产品SKU"]').value;
                    const quantity = row.querySelector('input[placeholder="数量"]').value;
                    const name = row.querySelector('input[placeholder="产品名称（自动填充）"]').value;
                    
                    if (sku && quantity) {
                        orderItems.push({ sku, quantity: parseInt(quantity) });
                    }
                });

                if (orderItems.length === 0) {
                    alert('请添加至少一个订单项目');
                    return;
                }

                const orderData = {
                    items: orderItems,
                    shipping_address: shippingAddress
                };

                const response = await fetch(`${API_BASE}/test/orders`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (response.ok) {
                    alert(`订单创建成功！\n订单号: ${result.order_id}`);
                    bootstrap.Modal.getInstance(document.getElementById('createOrderModal')).hide();
                    loadOrders(); // 重新加载订单列表
                    loadStatistics(); // 更新统计数据
                } else {
                    throw new Error(result.message || '创建订单失败');
                }
            } catch (error) {
                console.error('创建订单失败:', error);
                alert('创建订单失败: ' + error.message);
            }
        }
    </script>
</body>
</html>