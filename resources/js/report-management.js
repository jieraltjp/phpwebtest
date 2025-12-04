// 报表管理JavaScript
class ReportManagement {
    constructor() {
        this.currentReportType = 'dashboard';
        this.currentDateRange = 'last_30_days';
        this.charts = {};
        this.init();
    }

    init() {
        this.loadDashboardData();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // 刷新按钮
        window.refreshDashboard = () => {
            this.loadDashboardData();
        };

        // 导出按钮
        window.exportReport = () => {
            this.showExportModal();
        };

        // 清除缓存按钮
        window.clearCache = () => {
            this.clearReportCache();
        };

        // 日期范围切换
        window.changeDateRange = () => {
            this.handleDateRangeChange();
        };

        // 报表类型切换
        window.switchReportType = () => {
            this.switchReportType();
        };

        // 执行导出
        window.executeExport = () => {
            this.executeExport();
        };
    }

    async loadDashboardData() {
        try {
            this.showLoading();
            const params = this.buildDateParams();
            const response = await fetch(`/api/reports/dashboard?${new URLSearchParams(params)}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取仪表板数据失败');

            const data = await response.json();
            this.renderDashboard(data.data);
        } catch (error) {
            this.showAlert('error', error.message);
        } finally {
            this.hideLoading();
        }
    }

    renderDashboard(data) {
        // 更新关键指标
        this.updateKeyMetrics(data.summary);
        
        // 渲染图表
        this.renderSalesTrendChart(data.trends.sales_trend);
        this.renderOrderStatusChart(data.summary);
        this.renderTopProductsChart(data.top_performers.top_products);
        this.renderUserActivityChart(data.trends.user_activity);
    }

    updateKeyMetrics(summary) {
        document.getElementById('totalRevenue').textContent = `¥${this.formatNumber(summary.total_revenue)}`;
        document.getElementById('totalOrders').textContent = this.formatNumber(summary.total_orders);
        document.getElementById('activeUsers').textContent = this.formatNumber(summary.total_users);
        document.getElementById('conversionRate').textContent = `${summary.key_metrics.conversion_rate}%`;
    }

    renderSalesTrendChart(data) {
        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        
        if (this.charts.salesTrend) {
            this.charts.salesTrend.destroy();
        }

        this.charts.salesTrend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.date),
                datasets: [{
                    label: '销售额',
                    data: data.map(item => item.daily_revenue),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: '订单数',
                    data: data.map(item => item.order_count),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    renderOrderStatusChart(summary) {
        const ctx = document.getElementById('orderStatusChart').getContext('2d');
        
        if (this.charts.orderStatus) {
            this.charts.orderStatus.destroy();
        }

        // 模拟订单状态数据
        const statusData = {
            'PENDING': 15,
            'PROCESSING': 25,
            'SHIPPED': 35,
            'DELIVERED': 20,
            'CANCELLED': 5
        };

        this.charts.orderStatus = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    renderTopProductsChart(data) {
        const ctx = document.getElementById('topProductsChart').getContext('2d');
        
        if (this.charts.topProducts) {
            this.charts.topProducts.destroy();
        }

        this.charts.topProducts = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.name),
                datasets: [{
                    label: '销售额',
                    data: data.map(item => item.total_revenue),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    renderUserActivityChart(data) {
        const ctx = document.getElementById('userActivityChart').getContext('2d');
        
        if (this.charts.userActivity) {
            this.charts.userActivity.destroy();
        }

        this.charts.userActivity = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.date),
                datasets: [{
                    label: '活跃用户',
                    data: data.map(item => item.active_users),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    handleDateRangeChange() {
        const dateRange = document.getElementById('dateRange').value;
        const customRange = document.getElementById('customDateRange');
        const customRangeEnd = document.getElementById('customDateRangeEnd');

        if (dateRange === 'custom') {
            customRange.style.display = 'block';
            customRangeEnd.style.display = 'block';
        } else {
            customRange.style.display = 'none';
            customRangeEnd.style.display = 'none';
        }

        this.currentDateRange = dateRange;
        this.loadDashboardData();
    }

    switchReportType() {
        const reportType = document.getElementById('reportType').value;
        this.currentReportType = reportType;

        if (reportType === 'dashboard') {
            document.getElementById('dashboardView').style.display = 'block';
            document.getElementById('detailReportView').style.display = 'none';
            this.loadDashboardData();
        } else {
            document.getElementById('dashboardView').style.display = 'none';
            document.getElementById('detailReportView').style.display = 'block';
            this.loadDetailReport(reportType);
        }
    }

    async loadDetailReport(reportType) {
        try {
            this.showLoading();
            const params = this.buildDateParams();
            const response = await fetch(`/api/reports/${reportType}?${new URLSearchParams(params)}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取报表数据失败');

            const data = await response.json();
            this.renderDetailReport(data.data);
        } catch (error) {
            this.showAlert('error', error.message);
        } finally {
            this.hideLoading();
        }
    }

    renderDetailReport(data) {
        const header = document.getElementById('reportTableHeader');
        const body = document.getElementById('reportTableBody');

        // 根据报表类型生成不同的表格结构
        let headerHtml = '';
        let bodyHtml = '';

        switch (this.currentReportType) {
            case 'sales':
                headerHtml = `
                    <tr>
                        <th>日期</th>
                        <th>订单数</th>
                        <th>总收入</th>
                        <th>平均订单价值</th>
                        <th>独立客户</th>
                    </tr>
                `;
                
                data.daily_data.forEach(row => {
                    bodyHtml += `
                        <tr>
                            <td>${row.date}</td>
                            <td>${row.total_orders}</td>
                            <td>¥${this.formatNumber(row.total_revenue)}</td>
                            <td>¥${this.formatNumber(row.avg_order_value)}</td>
                            <td>${row.unique_customers}</td>
                        </tr>
                    `;
                });
                break;

            case 'user_behavior':
                headerHtml = `
                    <tr>
                        <th>日期</th>
                        <th>注册用户</th>
                        <th>活跃用户</th>
                        <th>留存率</th>
                    </tr>
                `;
                
                data.registration_trend.forEach((row, index) => {
                    const activeUsers = data.active_users[index] || { active_users: 0 };
                    const retention = data.retention_data[index] || { retention_rate: 0 };
                    
                    bodyHtml += `
                        <tr>
                            <td>${row.date}</td>
                            <td>${row.registrations}</td>
                            <td>${activeUsers.active_users}</td>
                            <td>${retention.retention_rate}%</td>
                        </tr>
                    `;
                });
                break;

            case 'product_analysis':
                headerHtml = `
                    <tr>
                        <th>产品名称</th>
                        <th>SKU</th>
                        <th>价格</th>
                        <th>库存</th>
                        <th>销售数量</th>
                        <th>总收入</th>
                    </tr>
                `;
                
                data.product_performance.forEach(row => {
                    bodyHtml += `
                        <tr>
                            <td>${row.name}</td>
                            <td>${row.sku}</td>
                            <td>¥${this.formatNumber(row.price)}</td>
                            <td>${row.stock}</td>
                            <td>${row.total_sold}</td>
                            <td>¥${this.formatNumber(row.total_revenue)}</td>
                        </tr>
                    `;
                });
                break;

            case 'inquiry_analysis':
                headerHtml = `
                    <tr>
                        <th>日期</th>
                        <th>询价总数</th>
                        <th>平均预算</th>
                        <th>已报价</th>
                        <th>已接受</th>
                    </tr>
                `;
                
                data.inquiry_trend.forEach(row => {
                    bodyHtml += `
                        <tr>
                            <td>${row.date}</td>
                            <td>${row.total_inquiries}</td>
                            <td>¥${this.formatNumber(row.avg_budget)}</td>
                            <td>${row.quoted_count}</td>
                            <td>${row.accepted_count}</td>
                        </tr>
                    `;
                });
                break;

            case 'financial':
                headerHtml = `
                    <tr>
                        <th>日期</th>
                        <th>收入</th>
                        <th>订单数</th>
                        <th>平均订单价值</th>
                    </tr>
                `;
                
                data.revenue_trend.forEach(row => {
                    bodyHtml += `
                        <tr>
                            <td>${row.date}</td>
                            <td>¥${this.formatNumber(row.daily_revenue)}</td>
                            <td>${row.order_count}</td>
                            <td>¥${this.formatNumber(row.avg_value)}</td>
                        </tr>
                    `;
                });
                break;
        }

        header.innerHTML = headerHtml;
        body.innerHTML = bodyHtml;
    }

    showExportModal() {
        // 设置默认日期
        const today = new Date().toISOString().split('T')[0];
        const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        
        document.getElementById('exportStartDate').value = thirtyDaysAgo;
        document.getElementById('exportEndDate').value = today;
        
        $('#exportModal').modal('show');
    }

    async executeExport() {
        try {
            const form = document.getElementById('exportForm');
            const formData = new FormData(form);
            
            const params = {};
            for (let [key, value] of formData.entries()) {
                params[key] = value;
            }
            
            const response = await fetch(`/api/reports/export`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(params)
            });

            if (!response.ok) throw new Error('导出失败');

            const data = await response.json();
            
            // 处理下载
            if (params.format !== 'json') {
                const blob = new Blob([data.data], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${params.report_type}_report_${new Date().toISOString().split('T')[0]}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            } else {
                // JSON格式直接显示或下载
                const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${params.report_type}_report_${new Date().toISOString().split('T')[0]}.json`;
                a.click();
                window.URL.revokeObjectURL(url);
            }
            
            $('#exportModal').modal('hide');
            this.showAlert('success', '报表导出成功');
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async clearReportCache() {
        try {
            const response = await fetch('/api/reports/clear-cache', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('清除缓存失败');

            const data = await response.json();
            this.showAlert('success', `已清除 ${data.data.cleared_keys} 个缓存项`);
            
            // 重新加载数据
            this.loadDashboardData();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    buildDateParams() {
        const params = {};
        
        if (this.currentDateRange === 'custom') {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;
        } else {
            params.date_range = this.currentDateRange;
        }
        
        return params;
    }

    formatNumber(num) {
        if (num === null || num === undefined) return '0';
        return new Intl.NumberFormat('zh-CN').format(num);
    }

    getAuthToken() {
        return localStorage.getItem('auth_token') || '';
    }

    showLoading() {
        // 简单的加载提示
        const loader = document.createElement('div');
        loader.id = 'reportLoader';
        loader.className = 'text-center';
        loader.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x"></i><p>加载中...</p>';
        loader.style.position = 'fixed';
        loader.style.top = '50%';
        loader.style.left = '50%';
        loader.style.transform = 'translate(-50%, -50%)';
        loader.style.zIndex = '9999';
        document.body.appendChild(loader);
    }

    hideLoading() {
        const loader = document.getElementById('reportLoader');
        if (loader) {
            loader.remove();
        }
    }

    showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) alert.remove();
        }, 3000);
    }
}

// 初始化报表管理
const reportManagement = new ReportManagement();