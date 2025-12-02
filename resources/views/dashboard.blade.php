<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAKUMART × 1688 - 采购管理平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #C00000;
            --light-gray: #F5F5F5;
            --medium-gray: #E0E0E0;
            --dark-gray: #333333;
            --text-gray: #999999;
            --number-red: #FF0000;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
        }

        /* 顶部导航栏 */
        .top-navbar {
            background-color: white;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }

        .logo {
            font-size: 20px;
            font-weight: bold;
            color: var(--dark-gray);
            text-decoration: none;
        }

        .logo span {
            color: #ff6a00;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 20px;
        }

        .nav-menu li {
            color: var(--dark-gray);
            cursor: pointer;
            transition: color 0.3s;
        }

        .nav-menu li:hover {
            color: var(--primary-red);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .exchange-rate {
            border: 1px solid #ced4da;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }

        /* 左侧导航栏 */
        .sidebar {
            background-color: white;
            min-height: calc(100vh - 70px);
            border-right: 1px solid #dee2e6;
            padding: 20px 0;
        }

        .sidebar-item {
            padding: 12px 20px;
            color: var(--dark-gray);
            cursor: pointer;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-item:hover {
            background-color: #f8f9fa;
        }

        .sidebar-item.active {
            background-color: var(--primary-red);
            color: white;
            border-left-color: #8B0000;
        }

        .sidebar-group {
            margin-top: 20px;
        }

        .sidebar-group-title {
            font-weight: bold;
            color: var(--dark-gray);
            padding: 10px 20px 5px;
            font-size: 14px;
            background-color: var(--medium-gray);
            margin-bottom: 5px;
        }

        .sidebar-sub-item {
            padding: 8px 20px 8px 35px;
            color: var(--dark-gray);
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .sidebar-sub-item:hover {
            background-color: #f8f9fa;
        }

        /* 主内容区 */
        .main-content {
            padding: 20px;
        }

        .section-header {
            background-color: var(--medium-gray);
            padding: 10px 15px;
            font-weight: bold;
            color: var(--dark-gray);
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 24px;
            color: var(--dark-gray);
        }

        .stat-content {
            flex: 1;
        }

        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: var(--number-red);
        }

        .stat-label {
            font-size: 14px;
            color: var(--dark-gray);
            margin: 0;
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
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <nav class="top-navbar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="#" class="logo">
                        RAKUMART <span>× 1688</span>
                    </a>
                </div>
                <div class="col-md-6">
                    <ul class="nav-menu justify-content-center">
                        <li>首次用户</li>
                        <li>国际</li>
                        <li>运送详情</li>
                        <li>手续费</li>
                        <li>可选</li>
                        <li>费用</li>
                        <li>固定会员</li>
                        <li>费</li>
                        <li>博客</li>
                        <li>联系</li>
                        <li>我们</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <div class="user-info justify-content-end">
                        <div class="exchange-rate">
                            1元=23.01日元
                            <i class="bi bi-question-circle"></i>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <img src="https://via.placeholder.com/32x32" alt="用户头像" class="rounded-circle">
                            <span>希塔卡梅 (ID: 331275)</span>
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