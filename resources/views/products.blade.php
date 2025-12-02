<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品管理 - RAKUMART × 1688</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #C00000;
            --light-gray: #F5F5F5;
            --medium-gray: #E0E0E0;
            --dark-gray: #333333;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
        }

        /* 复用仪表板的样式 */
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

        .main-content {
            padding: 20px;
        }

        .page-header {
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product-card {
            background-color: white;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .product-price {
            color: var(--primary-red);
            font-weight: bold;
            font-size: 18px;
        }

        .search-bar {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-primary-custom {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
        }

        .btn-primary-custom:hover {
            background-color: #8B0000;
            border-color: #8B0000;
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <nav class="top-navbar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="/" class="logo">
                        RAKUMART <span>× 1688</span>
                    </a>
                </div>
                <div class="col-md-9">
                    <div class="d-flex justify-content-end align-items-center gap-3">
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
                <div class="sidebar-item">
                    <a href="/dashboard" class="text-decoration-none text-dark">
                        <i class="bi bi-house-door me-2"></i>我的页面
                    </a>
                </div>
                
                <div class="sidebar-item active">
                    <i class="bi bi-box me-2"></i>产品管理
                </div>
                
                <div class="sidebar-item">
                    <a href="/orders" class="text-decoration-none text-dark">
                        <i class="bi bi-cart3 me-2"></i>订单管理
                    </a>
                </div>
            </div>

            <!-- 主内容区 -->
            <div class="col-md-10 main-content">
                <!-- 页面标题 -->
                <div class="page-header">
                    <h2><i class="bi bi-box me-2"></i>产品管理</h2>
                    <p class="text-muted mb-0">浏览和管理 1688 产品目录</p>
                </div>

                <!-- 搜索栏 -->
                <div class="search-bar">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchInput" placeholder="搜索产品名称或SKU...">
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control" id="minPrice" placeholder="最低价格">
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control" id="maxPrice" placeholder="最高价格">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="supplierFilter">
                                <option value="">所有供应商</option>
                                <option value="XX家具旗舰店">XX家具旗舰店</option>
                                <option value="数码配件专营店">数码配件专营店</option>
                                <option value="电脑配件商城">电脑配件商城</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary-custom w-100" onclick="searchProducts()">
                                <i class="bi bi-search me-1"></i>搜索
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 产品列表 -->
                <div id="productsList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">加载中...</span>
                        </div>
                        <p class="mt-2">正在加载产品...</p>
                    </div>
                </div>

                <!-- 分页 -->
                <nav id="pagination" class="mt-4">
                    <!-- 分页将通过 JavaScript 动态生成 -->
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = 'http://localhost:8000/api';
        let currentPage = 1;
        let totalPages = 1;

        // 页面加载时获取产品列表
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
        });

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
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        加载产品失败，请稍后重试。
                    </div>
                `;
            }
        }

        // 显示产品列表
        function displayProducts(products) {
            const container = document.getElementById('productsList');
            
            if (products.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-box" style="font-size: 48px; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">没有找到产品</h5>
                        <p class="text-muted">请尝试调整搜索条件</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = products.map(product => `
                <div class="product-card">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="${product.image_url || 'https://via.placeholder.com/80x80'}" 
                                 alt="${product.name}" 
                                 class="product-image">
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-2">${product.name}</h5>
                            <p class="text-muted mb-1"><small>SKU: ${product.sku}</small></p>
                            <p class="text-muted mb-1"><small>供应商: ${product.supplier_shop}</small></p>
                            <p class="mb-0"><small>库存: ${product.stock || 0} 件</small></p>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="product-price">¥${product.price}</div>
                            <small class="text-muted">${product.currency}</small>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-sm btn-outline-primary mb-2" onclick="viewProduct('${product.sku}')">
                                <i class="bi bi-eye me-1"></i>查看详情
                            </button>
                            <button class="btn btn-sm btn-primary-custom" onclick="addToCart('${product.sku}')">
                                <i class="bi bi-cart-plus me-1"></i>加入采购
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
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
                    <a class="page-link" href="#" onclick="loadProducts(${currentPage - 1}); return false;">上一页</a>
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
                    <a class="page-link" href="#" onclick="loadProducts(${currentPage + 1}); return false;">下一页</a>
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
        function viewProduct(sku) {
            // 这里可以打开模态框显示产品详情
            alert(`查看产品详情: ${sku}`);
        }

        // 加入采购车
        function addToCart(sku) {
            // 这里可以实现加入采购车的功能
            alert(`加入采购车: ${sku}`);
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