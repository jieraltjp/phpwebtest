/**
 * 高级分析中心 JavaScript
 * 提供仪表板交互、数据可视化和实时更新功能
 */

// 全局变量
let charts = {};
let currentDashboard = 'executive';
let refreshInterval = null;
let apiBaseUrl = '/api/analytics';

// 初始化分析中心
function initializeAnalytics() {
    console.log('初始化高级分析中心...');
    
    // 设置事件监听器
    setupEventListeners();
    
    // 加载默认仪表板
    loadExecutiveDashboard();
    
    // 启动自动刷新
    startAutoRefresh();
    
    // 初始化工具提示
    initializeTooltips();
}

// 设置事件监听器
function setupEventListeners() {
    // 仪表板切换
    document.querySelectorAll('.nav-link[data-dashboard]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const dashboard = this.dataset.dashboard;
            switchDashboard(dashboard);
        });
    });
    
    // 周期选择器
    document.querySelectorAll('.form-select').forEach(select => {
        select.addEventListener('change', function() {
            handlePeriodChange(this);
        });
    });
    
    // 自定义报表表单
    const customReportForm = document.getElementById('customReportForm');
    if (customReportForm) {
        customReportForm.addEventListener('submit', handleCustomReportSubmit);
    }
    
    // 键盘快捷键
    document.addEventListener('keydown', handleKeyboardShortcuts);
}

// 切换仪表板
function switchDashboard(dashboardName) {
    // 更新当前仪表板
    currentDashboard = dashboardName;
    
    // 隐藏所有仪表板
    document.querySelectorAll('.dashboard-content').forEach(dashboard => {
        dashboard.classList.remove('active');
    });
    
    // 移除导航活跃状态
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // 显示选中的仪表板
    const targetDashboard = document.getElementById(dashboardName + 'Dashboard') || 
                          document.getElementById(dashboardName + 'Center');
    if (targetDashboard) {
        targetDashboard.classList.add('active');
    }
    
    // 设置导航活跃状态
    const activeNavLink = document.querySelector(`[data-dashboard="${dashboardName}"]`);
    if (activeNavLink) {
        activeNavLink.classList.add('active');
    }
    
    // 加载仪表板数据
    loadDashboardData(dashboardName);
}

// 加载仪表板数据
async function loadDashboardData(dashboardName) {
    showLoading();
    
    try {
        switch(dashboardName) {
            case 'executive':
                await loadExecutiveDashboard();
                break;
            case 'sales':
                await loadSalesDashboard();
                break;
            case 'customer':
                await loadCustomerDashboard();
                break;
            case 'inventory':
                await loadInventoryDashboard();
                break;
            case 'reports':
                await loadReportsCenter();
                break;
            case 'predictions':
                await loadPredictionsCenter();
                break;
        }
        
        updateLastUpdateTime();
    } catch (error) {
        console.error('加载仪表板数据失败:', error);
        showError('加载数据失败: ' + error.message);
    } finally {
        hideLoading();
    }
}

// 加载高管仪表板
async function loadExecutiveDashboard() {
    try {
        const response = await fetch(`${apiBaseUrl}/dashboards/executive`);
        const data = await response.json();
        
        if (data.status === 'success') {
            renderExecutiveDashboard(data.data);
        } else {
            throw new Error(data.message || '加载失败');
        }
    } catch (error) {
        // 如果API调用失败，使用模拟数据
        renderExecutiveDashboard(getMockExecutiveData());
    }
}

// 渲染高管仪表板
function renderExecutiveDashboard(data) {
    // 渲染KPI卡片
    renderKPICards(data.kpi_cards || []);
    
    // 渲染图表
    renderRevenueChart(data.charts?.revenue_chart || {});
    renderCustomerSegmentChart(data.charts?.customer_chart || {});
    renderTopProductsChart(data.charts?.top_products_chart || {});
    renderRegionalChart(data.charts?.regional_chart || {});
    
    // 渲染警报
    renderAlerts(data.alerts || []);
}

// 渲染KPI卡片
function renderKPICards(kpiData) {
    const container = document.getElementById('kpiCards');
    if (!container) return;
    
    const kpiCards = kpiData.map(kpi => `
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon ${kpi.color || 'blue'}">
                    <i class="fas fa-${kpi.icon || 'chart-line'}"></i>
                </div>
                <div class="kpi-value">${kpi.value || '0'}</div>
                <div class="kpi-label">${kpi.title || '指标'}</div>
                <div class="kpi-change ${kpi.trend || 'up'}">
                    <i class="fas fa-arrow-${kpi.trend || 'up'} me-1"></i>
                    ${kpi.change || '0%'}
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = kpiCards;
}

// 渲染收入趋势图
function renderRevenueChart(chartData) {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    
    // 销毁现有图表
    if (charts.revenue) {
        charts.revenue.destroy();
    }
    
    charts.revenue = new Chart(ctx, {
        type: 'line',
        data: chartData.data || getDefaultLineChartData(),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '¥' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// 渲染客户细分图
function renderCustomerSegmentChart(chartData) {
    const ctx = document.getElementById('customerSegmentChart');
    if (!ctx) return;
    
    if (charts.customerSegment) {
        charts.customerSegment.destroy();
    }
    
    charts.customerSegment = new Chart(ctx, {
        type: 'doughnut',
        data: chartData.data || getDefaultDoughnutChartData(),
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

// 渲染热销产品图
function renderTopProductsChart(chartData) {
    const ctx = document.getElementById('topProductsChart');
    if (!ctx) return;
    
    if (charts.topProducts) {
        charts.topProducts.destroy();
    }
    
    charts.topProducts = new Chart(ctx, {
        type: 'bar',
        data: chartData.data || getDefaultBarChartData(),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

// 渲染地区表现图
function renderRegionalChart(chartData) {
    const ctx = document.getElementById('regionalChart');
    if (!ctx) return;
    
    if (charts.regional) {
        charts.regional.destroy();
    }
    
    charts.regional = new Chart(ctx, {
        type: 'polarArea',
        data: chartData.data || getDefaultPolarAreaData(),
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
}

// 渲染警报
function renderAlerts(alerts) {
    const container = document.getElementById('alertsList');
    if (!container) return;
    
    if (alerts.length === 0) {
        container.innerHTML = '<p class="text-muted">暂无警报</p>';
        return;
    }
    
    const alertItems = alerts.map(alert => `
        <div class="alert-item ${alert.type || 'info'}">
            <div class="alert-icon">
                <i class="fas fa-${getAlertIcon(alert.type)}"></i>
            </div>
            <div class="alert-content">
                <div class="alert-title">${alert.title || '警报'}</div>
                <div class="alert-message">${alert.message || ''}</div>
            </div>
            <div class="alert-time">${formatTime(alert.timestamp)}</div>
        </div>
    `).join('');
    
    container.innerHTML = alertItems;
}

// 加载销售仪表板
async function loadSalesDashboard() {
    try {
        const response = await fetch(`${apiBaseUrl}/dashboards/sales`);
        const data = await response.json();
        
        if (data.status === 'success') {
            renderSalesDashboard(data.data);
        } else {
            renderSalesDashboard(getMockSalesData());
        }
    } catch (error) {
        renderSalesDashboard(getMockSalesData());
    }
}

// 渲染销售仪表板
function renderSalesDashboard(data) {
    // 渲染销售指标
    renderSalesMetrics(data.widgets?.sales_summary?.data || []);
    
    // 渲染销售图表
    renderSalesTrendChart(data.charts?.daily_sales || {});
    renderCategorySalesChart(data.charts?.category_comparison || {});
    
    // 渲染表格
    renderTopProductsTable(data.tables?.top_products?.data || []);
    renderRecentOrdersTable(data.tables?.recent_orders?.data || []);
}

// 渲染销售指标
function renderSalesMetrics(metrics) {
    const container = document.getElementById('salesMetrics');
    if (!container) return;
    
    const metricsHTML = metrics.map(metric => `
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon ${metric.color || 'green'}">
                    <i class="fas fa-${metric.icon || 'dollar-sign'}"></i>
                </div>
                <div class="kpi-value">${metric.value || '0'}</div>
                <div class="kpi-label">${metric.label || '指标'}</div>
                <div class="kpi-change ${metric.trend || 'up'}">
                    <i class="fas fa-arrow-${metric.trend || 'up'} me-1"></i>
                    ${metric.change || '0%'}
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = metricsHTML;
}

// 渲染销售趋势图
function renderSalesTrendChart(chartData) {
    const ctx = document.getElementById('salesTrendChart');
    if (!ctx) return;
    
    if (charts.salesTrend) {
        charts.salesTrend.destroy();
    }
    
    charts.salesTrend = new Chart(ctx, {
        type: 'line',
        data: chartData.data || getDefaultMultiLineChartData(),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
}

// 渲染品类销售图
function renderCategorySalesChart(chartData) {
    const ctx = document.getElementById('categorySalesChart');
    if (!ctx) return;
    
    if (charts.categorySales) {
        charts.categorySales.destroy();
    }
    
    charts.categorySales = new Chart(ctx, {
        type: 'treemap',
        data: chartData.data || getDefaultTreemapData(),
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// 渲染热销产品表格
function renderTopProductsTable(products) {
    const tbody = document.querySelector('#topProductsTable tbody');
    if (!tbody) return;
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">暂无数据</td></tr>';
        return;
    }
    
    const rows = products.map((product, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${product.name || '产品'}</td>
            <td>${product.sales || 0}</td>
            <td>¥${(product.revenue || 0).toLocaleString()}</td>
            <td>
                <span class="kpi-change ${product.growth > 0 ? 'up' : 'down'}">
                    <i class="fas fa-arrow-${product.growth > 0 ? 'up' : 'down'} me-1"></i>
                    ${Math.abs(product.growth || 0)}%
                </span>
            </td>
        </tr>
    `).join('');
    
    tbody.innerHTML = rows;
}

// 渲染最近订单表格
function renderRecentOrdersTable(orders) {
    const tbody = document.querySelector('#recentOrdersTable tbody');
    if (!tbody) return;
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">暂无数据</td></tr>';
        return;
    }
    
    const rows = orders.map(order => `
        <tr>
            <td>${order.order_number || 'ORD-001'}</td>
            <td>${order.customer || '客户'}</td>
            <td>¥${(order.amount || 0).toLocaleString()}</td>
            <td>
                <span class="status-badge ${getStatusClass(order.status)}">
                    ${order.status || 'pending'}
                </span>
            </td>
            <td>${formatTime(order.created_at)}</td>
        </tr>
    `).join('');
    
    tbody.innerHTML = rows;
}

// 加载客户仪表板
async function loadCustomerDashboard() {
    try {
        const response = await fetch(`${apiBaseUrl}/dashboards/customer`);
        const data = await response.json();
        
        if (data.status === 'success') {
            renderCustomerDashboard(data.data);
        } else {
            renderCustomerDashboard(getMockCustomerData());
        }
    } catch (error) {
        renderCustomerDashboard(getMockCustomerData());
    }
}

// 渲染客户仪表板
function renderCustomerDashboard(data) {
    // 渲染客户概览
    renderCustomerOverview(data.widgets?.customer_overview?.data || []);
    
    // 渲染RFM分析
    renderRFMChart(data.charts?.rfm_analysis || {});
    renderLifecycleChart(data.charts?.customer_lifecycle || {});
    
    // 渲染同期群分析
    renderCohortChart(data.cohort_analysis?.cohort_chart || {});
    
    // 渲染流失预测表格
    renderChurnRiskTable(data.widgets?.churn_prediction?.data || []);
    renderChurnDistributionChart(data.charts?.churn_distribution || {});
}

// 加载库存仪表板
async function loadInventoryDashboard() {
    try {
        const response = await fetch(`${apiBaseUrl}/dashboards/inventory`);
        const data = await response.json();
        
        if (data.status === 'success') {
            renderInventoryDashboard(data.data);
        } else {
            renderInventoryDashboard(getMockInventoryData());
        }
    } catch (error) {
        renderInventoryDashboard(getMockInventoryData());
    }
}

// 渲染库存仪表板
function renderInventoryDashboard(data) {
    // 渲染库存概览
    renderInventoryOverview(data.widgets?.inventory_overview?.data || []);
    
    // 渲染库存图表
    renderStockLevelChart(data.charts?.stock_trend || {});
    renderInventoryValueChart(data.charts?.category_performance || {});
    
    // 渲染库存警报
    renderInventoryAlerts(data.alerts || []);
    
    // 渲染补货建议表格
    renderReorderTable(data.widgets?.reorder_recommendations?.data || []);
}

// 加载报表中心
async function loadReportsCenter() {
    try {
        // 加载报表模板
        const templatesResponse = await fetch(`${apiBaseUrl}/reports/templates`);
        const templatesData = await templatesResponse.json();
        
        if (templatesData.status === 'success') {
            renderReportTemplates(templatesData.data);
        }
        
        // 加载报表列表
        renderReportsList([]);
    } catch (error) {
        renderReportTemplates(getMockReportTemplates());
        renderReportsList([]);
    }
}

// 渲染报表模板
function renderReportTemplates(templates) {
    const container = document.getElementById('reportTemplates');
    if (!container) return;
    
    const templateCards = Object.entries(templates).map(([key, template]) => `
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="report-template-card" onclick="generateReportFromTemplate('${key}')">
                <div class="report-template-icon">
                    <i class="fas fa-${getTemplateIcon(template.type)}"></i>
                </div>
                <div class="report-template-title">${template.name}</div>
                <div class="report-template-description">${template.description}</div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = templateCards;
}

// 加载预测中心
async function loadPredictionsCenter() {
    try {
        // 加载销售预测
        const salesForecastResponse = await fetch(`${apiBaseUrl}/forecast/sales`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + getAuthToken()
            },
            body: JSON.stringify({
                period: '30d',
                forecast_periods: 30
            })
        });
        
        const salesForecastData = await salesForecastResponse.json();
        
        if (salesForecastData.status === 'success') {
            renderPredictionsCenter(salesForecastData.data);
        } else {
            renderPredictionsCenter(getMockPredictionsData());
        }
    } catch (error) {
        renderPredictionsCenter(getMockPredictionsData());
    }
}

// 渲染预测中心
function renderPredictionsCenter(data) {
    // 渲染模型状态
    renderModelsStatus(data.model_info || {});
    
    // 渲染销售预测图
    renderSalesForecastChart(data.forecast || {});
    
    // 渲染预测指标
    renderPredictionMetrics(data.accuracy_metrics || {});
    
    // 渲染其他预测图表
    renderChurnPredictionChart({});
    renderInventoryForecastChart({});
    renderMarketTrendsChart({});
}

// 处理周期变化
function handlePeriodChange(select) {
    const period = select.value;
    
    // 根据当前仪表板和控件ID处理变化
    if (select.id === 'revenuePeriod') {
        refreshRevenueChart(period);
    } else if (select.id === 'salesGranularity') {
        refreshSalesChart(period);
    } else if (select.id === 'forecastPeriod') {
        refreshForecastChart(period);
    }
}

// 处理自定义报表提交
async function handleCustomReportSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const reportConfig = {
        type: formData.get('reportType'),
        time_range: formData.get('reportTimeRange'),
        export_format: formData.get('exportFormat')
    };
    
    try {
        showLoading();
        
        const response = await fetch(`${apiBaseUrl}/reports/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + getAuthToken()
            },
            body: JSON.stringify(reportConfig)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('报表生成成功！');
            // 刷新报表列表
            loadReportsCenter();
        } else {
            showError('报表生成失败: ' + result.message);
        }
    } catch (error) {
        showError('报表生成失败: ' + error.message);
    } finally {
        hideLoading();
    }
}

// 从模板生成报表
async function generateReportFromTemplate(templateKey) {
    try {
        showLoading();
        
        // 根据模板类型设置报表配置
        const templateConfigs = {
            'sales_performance': { type: 'sales', time_range: '30d' },
            'customer_analysis': { type: 'customer', time_range: '90d' },
            'inventory_status': { type: 'inventory', time_range: 'current' },
            'financial_summary': { type: 'financial', time_range: '12m' },
            'product_performance': { type: 'product', time_range: '30d' }
        };
        
        const config = templateConfigs[templateKey] || { type: 'sales', time_range: '30d' };
        
        const response = await fetch(`${apiBaseUrl}/reports/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + getAuthToken()
            },
            body: JSON.stringify(config)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showSuccess('报表生成成功！');
            loadReportsCenter();
        } else {
            showError('报表生成失败: ' + result.message);
        }
    } catch (error) {
        showError('报表生成失败: ' + error.message);
    } finally {
        hideLoading();
    }
}

// 工具函数
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

function showSuccess(message) {
    // 实现成功提示
    console.log('Success:', message);
}

function showError(message) {
    // 实现错误提示
    console.error('Error:', message);
}

function updateLastUpdateTime() {
    const element = document.getElementById('lastUpdate');
    if (element) {
        element.textContent = new Date().toLocaleString('zh-CN');
    }
}

function formatTime(timestamp) {
    if (!timestamp) return '--';
    return new Date(timestamp).toLocaleString('zh-CN');
}

function getAlertIcon(type) {
    const icons = {
        'critical': 'exclamation-triangle',
        'warning': 'exclamation-circle',
        'info': 'info-circle',
        'success': 'check-circle'
    };
    return icons[type] || 'info-circle';
}

function getStatusClass(status) {
    const classes = {
        'completed': 'success',
        'pending': 'warning',
        'cancelled': 'error',
        'processing': 'info'
    };
    return classes[status] || 'info';
}

function getTemplateIcon(type) {
    const icons = {
        'sales': 'shopping-cart',
        'customer': 'users',
        'inventory': 'warehouse',
        'financial': 'chart-line',
        'product': 'box'
    };
    return icons[type] || 'file-alt';
}

function getAuthToken() {
    // 从localStorage或cookie获取认证令牌
    return localStorage.getItem('auth_token') || '';
}

// 键盘快捷键
function handleKeyboardShortcuts(e) {
    // Ctrl+R: 刷新数据
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        refreshData();
    }
    
    // Ctrl+E: 导出仪表板
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        exportDashboard();
    }
    
    // 数字键1-6: 切换仪表板
    if (e.key >= '1' && e.key <= '6') {
        const dashboards = ['executive', 'sales', 'customer', 'inventory', 'reports', 'predictions'];
        const index = parseInt(e.key) - 1;
        if (dashboards[index]) {
            switchDashboard(dashboards[index]);
        }
    }
}

// 自动刷新
function startAutoRefresh() {
    // 每5分钟自动刷新一次
    refreshInterval = setInterval(() => {
        if (document.visibilityState === 'visible') {
            refreshData();
        }
    }, 5 * 60 * 1000);
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
}

// 刷新数据
function refreshData() {
    loadDashboardData(currentDashboard);
}

// 导出仪表板
function exportDashboard() {
    // 实现仪表板导出功能
    showSuccess('导出功能开发中...');
}

// 初始化工具提示
function initializeTooltips() {
    // 初始化Bootstrap工具提示
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// 默认图表数据（用于API调用失败时的备用数据）
function getDefaultLineChartData() {
    return {
        labels: ['1月', '2月', '3月', '4月', '5月', '6月'],
        datasets: [{
            label: '收入',
            data: [120000, 150000, 180000, 200000, 170000, 220000],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4
        }]
    };
}

function getDefaultDoughnutChartData() {
    return {
        labels: ['VIP客户', '忠实客户', '新客户', '流失风险'],
        datasets: [{
            data: [30, 40, 20, 10],
            backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c']
        }]
    };
}

function getDefaultBarChartData() {
    return {
        labels: ['产品A', '产品B', '产品C', '产品D', '产品E'],
        datasets: [{
            label: '销量',
            data: [120, 90, 150, 80, 110],
            backgroundColor: '#667eea'
        }]
    };
}

function getDefaultPolarAreaData() {
    return {
        labels: ['华北', '华东', '华南', '华西', '华中'],
        datasets: [{
            data: [65, 59, 90, 81, 56],
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(118, 75, 162, 0.8)',
                'rgba(240, 147, 251, 0.8)',
                'rgba(245, 87, 108, 0.8)',
                'rgba(255, 195, 113, 0.8)'
            ]
        }]
    };
}

function getDefaultMultiLineChartData() {
    return {
        labels: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
        datasets: [
            {
                label: '销售额',
                data: [12000, 15000, 18000, 14000, 20000, 16000, 19000],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)'
            },
            {
                label: '订单数',
                data: [120, 150, 180, 140, 200, 160, 190],
                borderColor: '#764ba2',
                backgroundColor: 'rgba(118, 75, 162, 0.1)'
            }
        ]
    };
}

function getDefaultTreemapData() {
    return {
        labels: ['电子产品', '服装', '食品', '家居', '运动'],
        datasets: [{
            data: [300, 250, 180, 220, 150],
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(118, 75, 162, 0.8)',
                'rgba(240, 147, 251, 0.8)',
                'rgba(245, 87, 108, 0.8)',
                'rgba(255, 195, 113, 0.8)'
            ]
        }]
    };
}

// 模拟数据函数
function getMockExecutiveData() {
    return {
        kpi_cards: [
            { title: '总收入', value: '¥1,234,567', change: '+12.5%', trend: 'up', color: 'green', icon: 'dollar-sign' },
            { title: '订单数', value: '1,234', change: '+8.2%', trend: 'up', color: 'blue', icon: 'shopping-cart' },
            { title: '客户数', value: '567', change: '+5.1%', trend: 'up', color: 'purple', icon: 'users' },
            { title: '转化率', value: '3.2%', change: '-0.3%', trend: 'down', color: 'orange', icon: 'chart-line' }
        ],
        charts: {
            revenue_chart: { data: getDefaultLineChartData() },
            customer_chart: { data: getDefaultDoughnutChartData() },
            top_products_chart: { data: getDefaultBarChartData() },
            regional_chart: { data: getDefaultPolarAreaData() }
        },
        alerts: [
            { type: 'warning', title: '库存不足', message: '产品SKU-001库存低于安全水平', timestamp: new Date().toISOString() },
            { type: 'success', title: '销售目标达成', message: '本月销售目标已达成102%', timestamp: new Date().toISOString() }
        ]
    };
}

function getMockSalesData() {
    return {
        widgets: {
            sales_summary: {
                data: [
                    { label: '今日销售', value: '¥45,678', change: '+5.2%', trend: 'up', color: 'green', icon: 'calendar-day' },
                    { label: '本周销售', value: '¥234,567', change: '+8.7%', trend: 'up', color: 'blue', icon: 'calendar-week' },
                    { label: '本月销售', value: '¥1,234,567', change: '+12.5%', trend: 'up', color: 'purple', icon: 'calendar' },
                    { label: '平均订单价值', value: '¥1,234', change: '+3.8%', trend: 'up', color: 'orange', icon: 'receipt' }
                ]
            }
        },
        charts: {
            daily_sales: { data: getDefaultMultiLineChartData() },
            category_comparison: { data: getDefaultTreemapData() }
        },
        tables: {
            top_products: {
                data: [
                    { name: '无线蓝牙耳机', sales: 156, revenue: 234000, growth: 15.2 },
                    { name: '智能手表', sales: 98, revenue: 294000, growth: 8.7 },
                    { name: '笔记本电脑支架', sales: 234, revenue: 46800, growth: -2.3 }
                ]
            },
            recent_orders: {
                data: [
                    { order_number: 'ORD-2024-001', customer: '张三', amount: 15600, status: 'completed', created_at: new Date().toISOString() },
                    { order_number: 'ORD-2024-002', customer: '李四', amount: 8900, status: 'processing', created_at: new Date().toISOString() }
                ]
            }
        }
    };
}

function getMockCustomerData() {
    return {
        widgets: {
            customer_overview: {
                data: [
                    { label: '总客户数', value: '5,678', change: '+8.2%', trend: 'up', color: 'blue', icon: 'users' },
                    { label: '活跃客户', value: '3,456', change: '+12.1%', trend: 'up', color: 'green', icon: 'user-check' },
                    { label: '新客户', value: '234', change: '+5.6%', trend: 'up', color: 'purple', icon: 'user-plus' },
                    { label: '流失风险', value: '123', change: '-2.1%', trend: 'down', color: 'orange', icon: 'user-times' }
                ]
            },
            churn_prediction: {
                data: [
                    { customer_id: 'C001', customer_name: '客户A', churn_probability: 0.85, risk_level: 'high', last_purchase: '30天前', recommendation: '立即联系' },
                    { customer_id: 'C002', customer_name: '客户B', churn_probability: 0.65, risk_level: 'medium', last_purchase: '15天前', recommendation: '发送优惠券' }
                ]
            }
        },
        charts: {
            rfm_analysis: { data: getDefaultDoughnutChartData() },
            customer_lifecycle: { data: getDefaultLineChartData() },
            churn_distribution: { data: getDefaultBarChartData() }
        },
        cohort_analysis: {
            cohort_chart: { data: getDefaultLineChartData() }
        }
    };
}

function getMockInventoryData() {
    return {
        widgets: {
            inventory_overview: {
                data: [
                    { label: '总库存价值', value: '¥2,345,678', change: '+3.2%', trend: 'up', color: 'green', icon: 'warehouse' },
                    { label: '库存周转率', value: '4.5', change: '+0.3', trend: 'up', color: 'blue', icon: 'sync' },
                    { label: '缺货产品', value: '12', change: '-2', trend: 'down', color: 'orange', icon: 'exclamation-triangle' },
                    { label: '过剩库存', value: '8', change: '+1', trend: 'up', color: 'purple', icon: 'boxes' }
                ]
            },
            reorder_recommendations: {
                data: [
                    { sku: 'SKU-001', current_stock: 15, safety_stock: 50, recommended: 100, urgency: 'high', estimated_cost: 15000 },
                    { sku: 'SKU-002', current_stock: 80, safety_stock: 30, recommended: 50, urgency: 'medium', estimated_cost: 8000 }
                ]
            }
        },
        charts: {
            stock_trend: { data: getDefaultLineChartData() },
            category_performance: { data: getDefaultBarChartData() }
        },
        alerts: [
            { type: 'critical', title: '缺货警报', message: 'SKU-001库存为零，需要立即补货', timestamp: new Date().toISOString() },
            { type: 'warning', title: '库存过剩', message: 'SKU-002库存超过安全水平3倍', timestamp: new Date().toISOString() }
        ]
    };
}

function getMockReportTemplates() {
    return {
        sales_performance: {
            name: '销售绩效报表',
            description: '分析销售趋势、区域表现和产品销售情况',
            type: 'sales'
        },
        customer_analysis: {
            name: '客户分析报表',
            description: 'RFM客户分析、客户细分和留存分析',
            type: 'customer'
        },
        inventory_status: {
            name: '库存状态报表',
            description: '库存水平、周转率和优化建议',
            type: 'inventory'
        },
        financial_summary: {
            name: '财务汇总报表',
            description: '收入、成本、利润和现金流分析',
            type: 'financial'
        },
        product_performance: {
            name: '产品绩效报表',
            description: '产品销售、盈利能力和市场表现',
            type: 'product'
        }
    };
}

function getMockPredictionsData() {
    return {
        model_info: {
            algorithm: '自动选择',
            accuracy: 0.87,
            features: ['历史销售', '季节性', '趋势']
        },
        forecast: {
            predictions: [45000, 48000, 52000, 55000, 58000, 62000, 65000],
            confidence_intervals: [
                { lower: 42000, upper: 48000 },
                { lower: 45000, upper: 51000 },
                { lower: 49000, upper: 55000 },
                { lower: 52000, upper: 58000 },
                { lower: 55000, upper: 61000 },
                { lower: 59000, upper: 65000 },
                { lower: 62000, upper: 68000 }
            ]
        },
        accuracy_metrics: {
            r_squared: 0.87,
            mae: 1234,
            confidence: 0.95
        }
    };
}

// 页面卸载时清理
window.addEventListener('beforeunload', () => {
    stopAutoRefresh();
    
    // 销毁所有图表
    Object.values(charts).forEach(chart => {
        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    });
});

// 页面可见性变化时的处理
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        // 页面变为可见时刷新数据
        refreshData();
    }
});