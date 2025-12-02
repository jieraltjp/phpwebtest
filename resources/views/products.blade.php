<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品管理 - 雅虎B2B和风采购平台</title>
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

        /* 搜索栏 */
        .search-bar {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .search-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-orange), var(--gold-accent));
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

        /* 产品网格 */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        /* 产品卡片 */
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .product-card::before {
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

        .product-card:hover::before {
            transform: scaleX(1);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(192, 0, 0, 0.15);
        }

        .product-image-container {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--washi-white), #f8f9fa);
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary-red);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .product-content {
            padding: 20px;
        }

        .product-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--sumi-black);
            margin-bottom: 10px;
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .product-sku {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .product-stock {
            color: var(--bamboo-green);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .product-supplier {
            color: var(--text-gray);
            font-size: 0.85rem;
            margin-bottom: 15px;
        }

        .product-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .product-price {
            font-family: 'Noto Serif JP', serif;
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .product-currency {
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        /* 高级按钮 */
        .btn-japanese {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            border: none;
            color: white;
            padding: 10px 20px;
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

        .btn-outline-japanese {
            background: transparent;
            border: 2px solid var(--primary-red);
            color: var(--primary-red);
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-outline-japanese:hover {
            background: var(--primary-red);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(192, 0, 0, 0.3);
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

        /* 响应式设计 */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .search-bar {
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
                
                <div class="sidebar-item active">
                    <i class="bi bi-box me-2"></i>产品管理
                </div>
                
                <div class="sidebar-item">
                    <a href="/orders" class="text-decoration-none">
                        <i class="bi bi-cart3 me-2"></i>订单管理
                    </a>
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
                        <i class="bi bi-box me-3"></i>产品管理中心
                    </h1>
                    <p class="text-muted">探索精选产品，管理采购清单，追踪库存状态</p>
                </div>

                <!-- 统计卡片 -->
                <div class="stats-grid">
                    <div class="stat-card hover-float">
                        <div class="stat-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="stat-number" id="totalProducts">0</div>
                        <div class="stat-label">总产品数</div>
                    </div>
                    
                    <div class="stat-card hover-float">
                        <div class="stat-icon">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div class="stat-number" id="totalSuppliers">0</div>
                        <div class="stat-label">供应商</div>
                    </div>
                    
                    <div class="stat-card hover-float">
                        <div class="stat-icon">
                            <i class="bi bi-archive"></i>
                        </div>
                        <div class="stat-number" id="totalStock">0</div>
                        <div class="stat-label">总库存</div>
                    </div>
                    
                    <div class="stat-card hover-float">
                        <div class="stat-icon">
                            <i class="bi bi-star"></i>
                        </div>
                        <div class="stat-number" id="featuredProducts">0</div>
                        <div class="stat-label">精选产品</div>
                    </div>
                </div>

                <!-- 搜索栏 -->
                <div class="search-bar">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">搜索产品</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchInput" placeholder="产品名称或SKU...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">最低价格</label>
                            <input type="number" class="form-control" id="minPrice" placeholder="¥0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">最高价格</label>
                            <input type="number" class="form-control" id="maxPrice" placeholder="¥9999">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">供应商</label>
                            <select class="form-select" id="supplierFilter">
                                <option value="">所有供应商</option>
                                <option value="XX家具旗舰店">XX家具旗舰店</option>
                                <option value="数码配件专营店">数码配件专营店</option>
                                <option value="电脑配件商城">电脑配件商城</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-japanese w-100" onclick="searchProducts()">
                                <i class="bi bi-search me-2"></i>搜索
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 产品列表 -->
                <div id="productsList">
                    <div class="text-center py-5">
                        <div class="loading-spinner mx-auto"></div>
                        <p class="mt-3 text-muted">正在加载产品数据...</p>
                    </div>
                </div>

                <!-- 分页 -->
                <nav id="pagination" class="mt-4">
                    <!-- 分页将通过 JavaScript 动态生成 -->
                </nav>
            </div>
        </div>
    </div>

    <!-- 产品详情模态框 -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-box me-2"></i>产品详情
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="productModalBody">
                    <!-- 产品详情将通过 JavaScript 动态生成 -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                    <button type="button" class="btn btn-japanese" onclick="addToCart()">
                        <i class="bi bi-cart-plus me-1"></i>加入采购
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
        let currentProduct = null;

        // 页面加载时初始化
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            loadStatistics();
        });

        // 加载统计数据
        async function loadStatistics() {
            try {
                // 模拟统计数据
                const stats = {
                    total: 1250,
                    suppliers: 45,
                    stock: 8950,
                    featured: 128
                };

                // 动画更新数字
                animateNumber('totalProducts', 0, stats.total, 2000);
                animateNumber('totalSuppliers', 0, stats.suppliers, 2000);
                animateNumber('totalStock', 0, stats.stock, 2000);
                animateNumber('featuredProducts', 0, stats.featured, 2000);
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

        // 加载产品列表
        async function loadProducts(page = 1, searchParams = {}) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    limit: 12,
                    ...searchParams
                });

                const response = await fetch(`${API_BASE}/test/products?${params}`);
                const data = await response.json();

                if (response.ok) {
                    displayProducts(data.data || []);
                    updatePagination(data.current_page || 1, data.last_page || 1);
                } else {
                    throw new Error('获取产品失败');
                }
            } catch (error) {
                console.error('加载产品失败:', error);
                document.getElementById('productsList').innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h4>加载失败</h4>
                        <p>无法加载产品数据，请稍后重试</p>
                        <button class="btn btn-japanese" onclick="loadProducts()">
                            <i class="bi bi-arrow-clockwise me-2"></i>重新加载
                        </button>
                    </div>
                `;
            }
        }

        // 显示产品列表
        function displayProducts(products) {
            const container = document.getElementById('productsList');
            
            if (products.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-box"></i>
                        <h4>暂无产品</h4>
                        <p>没有找到符合条件的产品，请尝试调整搜索条件</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="products-grid">
                    ${products.map(product => `
                        <div class="product-card hover-float">
                            <div class="product-image-container">
                                <img src="${product.image_url || 'https://via.placeholder.com/300x200'}" 
                                     alt="${product.name}" 
                                     class="product-image">
                                ${product.stock > 0 ? '<span class="product-badge">有货</span>' : '<span class="product-badge" style="background: #6c757d;">缺货</span>'}
                            </div>
                            <div class="product-content">
                                <h3 class="product-title">${product.name}</h3>
                                <div class="product-meta">
                                    <span class="product-sku">SKU: ${product.sku}</span>
                                    <span class="product-stock">库存: ${product.stock || 0}</span>
                                </div>
                                <p class="product-supplier">
                                    <i class="bi bi-shop me-1"></i>${product.supplier_shop}
                                </p>
                                <div class="product-price-row">
                                    <div>
                                        <span class="product-price">¥${product.price}</span>
                                        <span class="product-currency">/${product.currency}</span>
                                    </div>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-outline-japanese btn-sm flex-fill" onclick="viewProduct('${product.sku}')">
                                        <i class="bi bi-eye me-1"></i>查看
                                    </button>
                                    <button class="btn btn-japanese btn-sm flex-fill" onclick="quickAddToCart('${product.sku}')" ${product.stock <= 0 ? 'disabled' : ''}>
                                        <i class="bi bi-cart-plus me-1"></i>采购
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
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
                    <a class="page-link" href="#" onclick="loadProducts(${currentPage - 1}); return false;">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>`;
            }

            // 页码
            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadProducts(${i}); return false;">${i}</a>
                </li>`;
            }

            // 下一页
            if (currentPage < totalPages) {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadProducts(${currentPage + 1}); return false;">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>`;
            }

            paginationHtml += '</ul>';
            pagination.innerHTML = paginationHtml;
        }

        // 搜索产品
        function searchProducts() {
            const searchParams = {
                search: document.getElementById('searchInput').value,
                min_price: document.getElementById('minPrice').value,
                max_price: document.getElementById('maxPrice').value,
                supplier: document.getElementById('supplierFilter').value
            };

            // 移除空值
            Object.keys(searchParams).forEach(key => {
                if (!searchParams[key]) {
                    delete searchParams[key];
                }
            });

            loadProducts(1, searchParams);
        }

        // 查看产品详情
        async function viewProduct(sku) {
            try {
                // 模拟获取产品详情
                const response = await fetch(`${API_BASE}/test/products`);
                const data = await response.json();
                const product = data.data.find(p => p.sku === sku);

                if (product) {
                    currentProduct = product;
                    displayProductDetails(product);
                    new bootstrap.Modal(document.getElementById('productModal')).show();
                } else {
                    throw new Error('产品不存在');
                }
            } catch (error) {
                console.error('查看产品详情失败:', error);
                alert('获取产品详情失败，请稍后重试');
            }
        }

        // 显示产品详情
        function displayProductDetails(product) {
            const modalBody = document.getElementById('productModalBody');
            
            const specsHtml = product.specs ? 
                Object.entries(product.specs).map(([key, value]) => `
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>${key}:</strong></div>
                        <div class="col-md-8">${value}</div>
                    </div>
                `).join('') : '<p class="text-muted">暂无规格信息</p>';

            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-5">
                        <img src="${product.image_url || 'https://via.placeholder.com/400x300'}" 
                             alt="${product.name}" 
                             class="img-fluid rounded">
                    </div>
                    <div class="col-md-7">
                        <h4 class="mb-3">${product.name}</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>SKU:</strong> ${product.sku}</p>
                                <p><strong>供应商:</strong> ${product.supplier_shop}</p>
                                <p><strong>库存:</strong> <span class="text-success">${product.stock || 0} 件</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>价格:</strong> 
                                    <span class="text-danger fs-4">¥${product.price}</span> ${product.currency}
                                </p>
                                <p><strong>状态:</strong> 
                                    <span class="badge bg-success">${product.active ? '在售' : '下架'}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <h5 class="mb-3">产品规格</h5>
                ${specsHtml}
                <hr>
                <h5 class="mb-3">产品描述</h5>
                <p>${product.description || '暂无描述信息'}</p>
            `;
        }

        // 快速加入采购车
        function quickAddToCart(sku) {
            // 这里可以实现快速加入采购车的功能
            alert(`产品 ${sku} 已加入采购车！`);
        }

        // 加入采购车
        function addToCart() {
            if (currentProduct) {
                alert(`产品 ${currentProduct.name} 已加入采购车！`);
                bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            }
        }

        // 回车键搜索
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
    </script>
</body>
</html>