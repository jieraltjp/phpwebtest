<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>雅虎B2B采购门户 - 和风管理平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="/css/japanese-effects.css" rel="stylesheet">
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
            --number-red: #FF0000;
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
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange));
            transition: width 0.3s ease;
        }

        .logo:hover::after {
            width: 100%;
        }

        .logo span {
            color: var(--gold-accent);
        }

        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 30px;
        }

        .nav-menu li {
            color: #bdc3c7;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
        }

        .nav-menu li::before {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-red);
            transition: width 0.3s ease;
        }

        .nav-menu li:hover {
            color: white;
        }

        .nav-menu li:hover::before {
            width: 100%;
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

        .sidebar-item i {
            font-size: 1.2rem;
        }

        .sidebar-group {
            margin-top: 30px;
        }

        .sidebar-group-title {
            font-weight: 700;
            color: var(--gold-accent);
            padding: 10px 25px 5px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            position: relative;
        }

        .sidebar-group-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25px;
            right: 25px;
            height: 1px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange));
        }

        .sidebar-sub-item {
            padding: 10px 20px 10px 40px;
            color: #95a5a6;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
        }

        .sidebar-sub-item::before {
            content: '•';
            position: absolute;
            left: 25px;
            color: var(--primary-orange);
            transition: all 0.3s ease;
        }

        .sidebar-sub-item:hover {
            background: rgba(192, 0, 0, 0.05);
            color: white;
            transform: translateX(3px);
        }

        .sidebar-sub-item:hover::before {
            color: var(--gold-accent);
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

        .breadcrumb {
            background-color: transparent;
            padding: 10px 0;
            margin-bottom: 0;
        }

        .breadcrumb-item {
            color: var(--text-gray);
            font-weight: 500;
        }

        .breadcrumb-item.active {
            color: var(--primary-red);
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '›';
            color: var(--primary-orange);
        }

        .section-header {
            background: linear-gradient(135deg, var(--sumi-black) 0%, #1a1a1a 100%);
            padding: 15px 20px;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        .section-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
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
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 28px;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-content {
            flex: 1;
        }

        .stat-number {
            font-family: 'Noto Serif JP', serif;
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-gray);
            margin: 0;
            font-weight: 500;
        }

        /* 高级卡片设计 */
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .content-card:hover::before {
            transform: scaleX(1);
        }

        .content-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(192, 0, 0, 0.15);
        }

        /* 和风按钮设计 */
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

        /* 响应式设计 */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .nav-menu {
                flex-wrap: wrap;
                gap: 10px;
            }

            .page-title {
                font-size: 24px;
            }

            .main-content {
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
                <div class="col-md-6">
                    <ul class="nav-menu justify-content-center">
                        <li>首页</li>
                        <li>产品采购</li>
                        <li>国际物流</li>
                        <li>费用查询</li>
                        <li>会员中心</li>
                        <li>帮助中心</li>
                        <li>关于我们</li>
                    </ul>
                </div>
                <div class="col-md-3">
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
            <!-- 左侧导航栏 -->
            <div class="col-md-2 sidebar">
                <div class="sidebar-item active">
                    <i class="bi bi-house-door me-2"></i>我的页面
                </div>
                
                <div class="sidebar-group">
                    <div class="sidebar-group-title">订单管理</div>
                    <div class="sidebar-sub-item">Excel排序</div>
                    <div class="sidebar-sub-item">所有采购订单(<span id="all-orders-count">0</span>)</div>
                    <div class="sidebar-sub-item">问题产品(<span id="problem-products-count">0</span>)</div>
                    <div class="sidebar-sub-item">中国国内物流状况(<span id="logistics-count">0</span>)</div>
                    <div class="sidebar-sub-item">仓库(<span id="warehouse-count">0</span>)</div>
                    <div class="sidebar-sub-item">送货申请表(<span id="delivery-count">0</span>)</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">亚马逊库存管理</div>
                    <div class="sidebar-sub-item">商店API连接</div>
                    <div class="sidebar-sub-item">亚马逊库存</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">首次用户</div>
                    <div class="sidebar-sub-item">用户注册</div>
                    <div class="sidebar-sub-item">新手指南</div>
                    <div class="sidebar-sub-item">常见问题</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">国际</div>
                    <div class="sidebar-sub-item">国际运费</div>
                    <div class="sidebar-sub-item">关税计算</div>
                    <div class="sidebar-sub-item">国际物流</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">运送详情</div>
                    <div class="sidebar-sub-item">运费查询</div>
                    <div class="sidebar-sub-item">运送时间</div>
                    <div class="sidebar-sub-item">包装服务</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">手续费</div>
                    <div class="sidebar-sub-item">服务费用</div>
                    <div class="sidebar-sub-item">支付方式</div>
                    <div class="sidebar-sub-item">发票管理</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">可选</div>
                    <div class="sidebar-sub-item">增值服务</div>
                    <div class="sidebar-sub-item">定制包装</div>
                    <div class="sidebar-sub-item">保险服务</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">费用</div>
                    <div class="sidebar-sub-item">费用明细</div>
                    <div class="sidebar-sub-item">费用统计</div>
                    <div class="sidebar-sub-item">费用报告</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">固定会员</div>
                    <div class="sidebar-sub-item">会员等级</div>
                    <div class="sidebar-sub-item">会员权益</div>
                    <div class="sidebar-sub-item">续费管理</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">博客</div>
                    <div class="sidebar-sub-item">最新资讯</div>
                    <div class="sidebar-sub-item">使用技巧</div>
                    <div class="sidebar-sub-item">行业动态</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">联系</div>
                    <div class="sidebar-sub-item">在线客服</div>
                    <div class="sidebar-sub-item">客服中心</div>
                    <div class="sidebar-sub-item">意见反馈</div>
                </div>

                <div class="sidebar-group">
                    <div class="sidebar-group-title">我们</div>
                    <div class="sidebar-sub-item">关于我们</div>
                    <div class="sidebar-sub-item">公司介绍</div>
                    <div class="sidebar-sub-item">合作伙伴</div>
                </div>
            </div>

            <!-- 主内容区 -->
            <div class="col-md-10 main-content">
                <!-- 采购订单模块 -->
                <div class="section mb-4">
                    <div class="section-header">
                        <i class="bi bi-cart3 me-2"></i>采购订单
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-file-earmark-arrow-down"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="temp-storage">0</div>
                                <p class="stat-label">临时存储（未提交）</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="all-orders">1</div>
                                <p class="stat-label">所有采购订单</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-file-earmark-yen"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="quoting">1</div>
                                <p class="stat-label">报价中</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-file-earmark-clock"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="awaiting-payment">0</div>
                                <p class="stat-label">等待付款</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-bag"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="purchasing">0</div>
                                <p class="stat-label">购买</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="purchase-completed">0</div>
                                <p class="stat-label">购买已完成</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="shipment-completed">0</div>
                                <p class="stat-label">发货完成</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-file-earmark-question"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="problem-products">0</div>
                                <p class="stat-label">问题产品</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 中国国内形势模块 -->
                <div class="section">
                    <div class="section-header">
                        <i class="bi bi-geo-alt me-2"></i>中国国内形势
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-bag-clock"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="awaiting-purchase">0</div>
                                <p class="stat-label">等待购买</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="in-delivery">0</div>
                                <p class="stat-label">交付中</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-box-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="awaiting-arrival">0</div>
                                <p class="stat-label">等待到达</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-box-arrow-up"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="arrival-prep">0</div>
                                <p class="stat-label">抵达准备工作进行中</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // API 配置
        const API_BASE = 'http://localhost:8000/api';
        let authToken = localStorage.getItem('authToken') || null;

        // 页面加载时获取数据
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });

        // 加载仪表板数据
        async function loadDashboardData() {
            try {
                // 如果没有 token，先登录
                if (!authToken) {
                    await login();
                }
                
                // 获取订单数据
                await fetchOrderData();
                
            } catch (error) {
                console.error('加载数据失败:', error);
            }
        }

        // 登录获取 token
        async function login() {
            try {
                const response = await fetch(`${API_BASE}/test/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username: 'testuser',
                        password: 'password123'
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    authToken = data.token;
                    localStorage.setItem('authToken', authToken);
                } else {
                    throw new Error('登录失败');
                }
            } catch (error) {
                console.error('登录错误:', error);
            }
        }

        // 获取订单数据
        async function fetchOrderData() {
            try {
                // 模拟订单状态数据（实际应该从 API 获取）
                const orderStats = {
                    tempStorage: 0,
                    allOrders: 1,
                    quoting: 1,
                    awaitingPayment: 0,
                    purchasing: 0,
                    purchaseCompleted: 0,
                    shipmentCompleted: 0,
                    problemProducts: 0,
                    awaitingPurchase: 0,
                    inDelivery: 0,
                    awaitingArrival: 0,
                    arrivalPrep: 0
                };

                // 更新页面数据
                updateDashboardStats(orderStats);
                
            } catch (error) {
                console.error('获取订单数据失败:', error);
            }
        }

        // 更新仪表板统计数据
        function updateDashboardStats(stats) {
            document.getElementById('temp-storage').textContent = stats.tempStorage;
            document.getElementById('all-orders').textContent = stats.allOrders;
            document.getElementById('quoting').textContent = stats.quoting;
            document.getElementById('awaiting-payment').textContent = stats.awaitingPayment;
            document.getElementById('purchasing').textContent = stats.purchasing;
            document.getElementById('purchase-completed').textContent = stats.purchaseCompleted;
            document.getElementById('shipment-completed').textContent = stats.shipmentCompleted;
            document.getElementById('problem-products').textContent = stats.problemProducts;
            document.getElementById('awaiting-purchase').textContent = stats.awaitingPurchase;
            document.getElementById('in-delivery').textContent = stats.inDelivery;
            document.getElementById('awaiting-arrival').textContent = stats.awaitingArrival;
            document.getElementById('arrival-prep').textContent = stats.arrivalPrep;
        }

        // 侧边栏点击事件
        document.querySelectorAll('.sidebar-item, .sidebar-sub-item').forEach(item => {
            item.addEventListener('click', function() {
                // 移除所有激活状态
                document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
                // 添加激活状态到当前项
                this.classList.add('active');
            });
        });

        // 定期刷新数据（每30秒）
        setInterval(loadDashboardData, 30000);
    </script>
</body>
</html>