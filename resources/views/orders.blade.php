<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单管理 - RAKUMART × 1688</title>
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

        .order-card {
            background-color: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cfe2ff; color: #084298; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-returned { background-color: #f8d7da; color: #721c24; }
        .status-cancelled { background-color: #e2e3e5; color: #383d41; }

        .filter-bar {
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

        .order-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .order-item:last-child {
            border-bottom: none;
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
                
                <div class="sidebar-item">
                    <a href="/products" class="text-decoration-none text-dark">
                        <i class="bi bi-box me-2"></i>产品管理
                    </a>
                </div>
                
                <div class="sidebar-item active">
                    <i class="bi bi-cart3 me-2"></i>订单管理
                </div>
            </div>

            <!-- 主内容区 -->
            <div class="col-md-10 main-content">
                <!-- 页面标题 -->
                <div class="page-header">
                    <h2><i class="bi bi-cart3 me-2"></i>订单管理</h2>
                    <p class="text-muted mb-0">管理您的采购订单和物流信息</p>
                </div>

                <!-- 筛选栏 -->
                <div class="filter-bar">
                    <div class="row g-3">
                        <div class="col-md-3">
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
                            <input type="text" class="form-control" id="orderIdFilter" placeholder="订单号">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="dateFilter">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary-custom w-100" onclick="filterOrders()">
                                <i class="bi bi-funnel me-1"></i>筛选
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 创建新订单按钮 -->
                <div class="mb-3">
                    <button class="btn btn-primary-custom" onclick="createNewOrder()">
                        <i class="bi bi-plus-circle me-1"></i>创建新订单
                    </button>
                </div>

                <!-- 订单列表 -->
                <div id="ordersList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">加载中...</span>
                        </div>
                        <p class="mt-2">正在加载订单...</p>
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
                    <h5 class="modal-title">订单详情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderModalBody">
                    <!-- 订单详情将通过 JavaScript 动态生成 -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                    <button type="button" class="btn btn-primary-custom" onclick="trackOrder()">物流追踪</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 创建订单模态框 -->
    <div class="modal fade" id="createOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">创建新订单</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createOrderForm">
                        <div class="mb-3">
                            <label class="form-label">配送地址</label>
                            <textarea class="form-control" id="shippingAddress" rows="3" required>日本东京都港区测试地址1-2-3</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">订单项目</label>
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
                    <button type="button" class="btn btn-primary-custom" onclick="submitOrder()">提交订单</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = 'http://localhost:8000/api';
        let currentPage = 1;
        let totalPages = 1;
        let currentOrderId = null;

        // 页面加载时获取订单列表
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
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
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        加载订单失败，请稍后重试。
                    </div>
                `;
            }
        }

        // 显示订单列表
        function displayOrders(orders) {
            const container = document.getElementById('ordersList');
            
            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-cart-x" style="font-size: 48px; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">没有找到订单</h5>
                        <p class="text-muted">您还没有任何订单，<a href="#" onclick="createNewOrder()">创建第一个订单</a></p>
                    </div>
                `;
                return;
            }

            container.innerHTML = orders.map(order => `
                <div class="order-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1">订单号: ${order.order_id}</h5>
                                    <p class="text-muted mb-2">
                                        <small>创建时间: ${new Date(order.created_at).toLocaleString()}</small>
                                    </p>
                                    <p class="mb-1"><strong>状态:</strong> 
                                        <span class="status-badge status-${order.status.toLowerCase()}">${getStatusText(order.status)}</span>
                                    </p>
                                    <p class="mb-1"><strong>总金额:</strong> ¥${order.total_amount} ${order.currency}</p>
                                    <p class="mb-0"><strong>配送地址:</strong> ${order.shipping_address || '未设置'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-sm btn-outline-primary mb-2" onclick="viewOrder('${order.order_id}')">
                                <i class="bi bi-eye me-1"></i>查看详情
                            </button>
                            <br>
                            <button class="btn btn-sm btn-outline-info mb-2" onclick="trackOrder('${order.order_id}')">
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
                    <a class="page-link" href="#" onclick="loadOrders(${currentPage - 1}); return false;">上一页</a>
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
                    <a class="page-link" href="#" onclick="loadOrders(${currentPage + 1}); return false;">下一页</a>
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
                <div class="order-item">
                    <div class="row">
                        <div class="col-md-4"><strong>SKU:</strong> ${item.sku}</div>
                        <div class="col-md-4"><strong>名称:</strong> ${item.name}</div>
                        <div class="col-md-2"><strong>数量:</strong> ${item.quantity}</div>
                        <div class="col-md-2"><strong>单价:</strong> ¥${item.unit_price}</div>
                    </div>
                </div>
            `).join('') : '<p>暂无订单项目</p>';

            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>订单信息</h6>
                        <p><strong>订单号:</strong> ${order.order_id}</p>
                        <p><strong>创建时间:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                        <p><strong>状态:</strong> <span class="status-badge status-${order.status.toLowerCase()}">${getStatusText(order.status)}</span></p>
                        <p><strong>状态说明:</strong> ${order.status_message || '无'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>配送信息</h6>
                        <p><strong>配送地址:</strong> ${order.shipping_address || '未设置'}</p>
                        <p><strong>国内追踪号:</strong> ${order.domestic_tracking_number || '无'}</p>
                        <p><strong>国际追踪号:</strong> ${order.international_tracking_number || '无'}</p>
                    </div>
                </div>
                <hr>
                <h6>订单项目</h6>
                ${itemsHtml}
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>总金额 (CNY):</strong> ¥${order.total_fee_cny}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>总金额 (JPY):</strong> ¥${order.total_fee_jpy}</p>
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