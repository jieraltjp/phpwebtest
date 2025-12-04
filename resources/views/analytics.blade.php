<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>高级分析中心 - 万方商事 B2B 采购门户</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Analytics CSS -->
    <link href="{{ asset('build/css/analytics.css') }}" rel="stylesheet">
</head>
<body class="analytics-bg">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark analytics-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="fas fa-chart-line me-2"></i>
                <strong>分析中心</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-dashboard="executive">
                            <i class="fas fa-tachometer-alt me-1"></i>高管仪表板
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-dashboard="sales">
                            <i class="fas fa-shopping-cart me-1"></i>销售分析
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-dashboard="customer">
                            <i class="fas fa-users me-1"></i>客户分析
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-dashboard="inventory">
                            <i class="fas fa-warehouse me-1"></i>库存管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-dashboard="reports">
                            <i class="fas fa-file-alt me-1"></i>报表中心
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-dashboard="predictions">
                            <i class="fas fa-crystal-ball me-1"></i>预测分析
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>设置
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="refreshData()">
                                <i class="fas fa-sync me-2"></i>刷新数据
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportDashboard()">
                                <i class="fas fa-download me-2"></i>导出仪表板
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/api/analytics/system-health" target="_blank">
                                <i class="fas fa-heartbeat me-2"></i>系统状态
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="fas fa-arrow-left me-1"></i>返回主站
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="analytics-main">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="loading-overlay">
            <div class="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">加载中...</span>
                </div>
                <p class="mt-3">正在加载数据...</p>
            </div>
        </div>

        <!-- Executive Dashboard -->
        <div id="executiveDashboard" class="dashboard-content active">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="dashboard-header">
                            <h1><i class="fas fa-tachometer-alt me-3"></i>高管仪表板</h1>
                            <p class="text-muted">实时业务概览和关键绩效指标</p>
                        </div>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="row mb-4" id="kpiCards">
                    <!-- KPI cards will be dynamically loaded -->
                </div>

                <!-- Charts Row 1 -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>收入趋势</h5>
                                <div class="chart-controls">
                                    <select class="form-select form-select-sm" id="revenuePeriod">
                                        <option value="7d">最近7天</option>
                                        <option value="30d" selected>最近30天</option>
                                        <option value="90d">最近90天</option>
                                    </select>
                                </div>
                            </div>
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>客户细分</h5>
                            </div>
                            <canvas id="customerSegmentChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>热销产品</h5>
                            </div>
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>地区表现</h5>
                            </div>
                            <canvas id="regionalChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Alerts Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="alerts-container">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>业务警报</h5>
                            <div id="alertsList">
                                <!-- Alerts will be dynamically loaded -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Dashboard -->
        <div id="salesDashboard" class="dashboard-content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="dashboard-header">
                            <h1><i class="fas fa-shopping-cart me-3"></i>销售分析</h1>
                            <p class="text-muted">详细的销售数据分析和趋势预测</p>
                        </div>
                    </div>
                </div>

                <!-- Sales Metrics -->
                <div class="row mb-4" id="salesMetrics">
                    <!-- Sales metrics will be dynamically loaded -->
                </div>

                <!-- Sales Charts -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>销售趋势分析</h5>
                                <div class="chart-controls">
                                    <select class="form-select form-select-sm" id="salesGranularity">
                                        <option value="daily">按日</option>
                                        <option value="weekly">按周</option>
                                        <option value="monthly">按月</option>
                                    </select>
                                </div>
                            </div>
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>品类销售分布</h5>
                            </div>
                            <canvas id="categorySalesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Sales Tables -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="table-container">
                            <div class="table-header">
                                <h5>热销产品排行</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="topProductsTable">
                                    <thead>
                                        <tr>
                                            <th>排名</th>
                                            <th>产品</th>
                                            <th>销量</th>
                                            <th>收入</th>
                                            <th>增长率</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="table-container">
                            <div class="table-header">
                                <h5>最近订单</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="recentOrdersTable">
                                    <thead>
                                        <tr>
                                            <th>订单号</th>
                                            <th>客户</th>
                                            <th>金额</th>
                                            <th>状态</th>
                                            <th>时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Dashboard -->
        <div id="customerDashboard" class="dashboard-content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="dashboard-header">
                            <h1><i class="fas fa-users me-3"></i>客户分析</h1>
                            <p class="text-muted">RFM客户分析、细分和留存分析</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Overview -->
                <div class="row mb-4" id="customerOverview">
                    <!-- Customer overview will be dynamically loaded -->
                </div>

                <!-- RFM Analysis -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>RFM分析</h5>
                            </div>
                            <canvas id="rfmChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>客户生命周期</h5>
                            </div>
                            <canvas id="lifecycleChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Cohort Analysis -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>同期群留存分析</h5>
                            </div>
                            <canvas id="cohortChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Churn Prediction -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="table-container">
                            <div class="table-header">
                                <h5>高风险客户预警</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="churnRiskTable">
                                    <thead>
                                        <tr>
                                            <th>客户ID</th>
                                            <th>客户名称</th>
                                            <th>流失概率</th>
                                            <th>风险等级</th>
                                            <th>最后购买</th>
                                            <th>建议行动</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>流失风险分布</h5>
                            </div>
                            <canvas id="churnDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Dashboard -->
        <div id="inventoryDashboard" class="dashboard-content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="dashboard-header">
                            <h1><i class="fas fa-warehouse me-3"></i>库存管理</h1>
                            <p class="text-muted">库存水平、周转率和优化建议</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Overview -->
                <div class="row mb-4" id="inventoryOverview">
                    <!-- Inventory overview will be dynamically loaded -->
                </div>

                <!-- Inventory Charts -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>库存水平趋势</h5>
                            </div>
                            <canvas id="stockLevelChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>库存价值分布</h5>
                            </div>
                            <canvas id="inventoryValueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Inventory Alerts -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alerts-container">
                            <h5><i class="fas fa-exclamation-circle me-2"></i>库存警报</h5>
                            <div id="inventoryAlerts">
                                <!-- Inventory alerts will be dynamically loaded -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reorder Recommendations -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="table-header">
                                <h5>补货建议</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="reorderTable">
                                    <thead>
                                        <tr>
                                            <th>产品SKU</th>
                                            <th>当前库存</th>
                                            <th>安全库存</th>
                                            <th>建议补货</th>
                                            <th>紧急程度</th>
                                            <th>预计成本</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Center -->
        <div id="reportsCenter" class="dashboard-content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="dashboard-header">
                            <h1><i class="fas fa-file-alt me-3"></i>报表中心</h1>
                            <p class="text-muted">动态报表生成和导出</p>
                        </div>
                    </div>
                </div>

                <!-- Report Templates -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="reports-container">
                            <h5>报表模板</h5>
                            <div class="row" id="reportTemplates">
                                <!-- Report templates will be dynamically loaded -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Report Builder -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="report-builder">
                            <h5>自定义报表生成器</h5>
                            <form id="customReportForm">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">报表类型</label>
                                        <select class="form-select" id="reportType" required>
                                            <option value="">选择类型</option>
                                            <option value="sales">销售报表</option>
                                            <option value="customer">客户报表</option>
                                            <option value="inventory">库存报表</option>
                                            <option value="financial">财务报表</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">时间范围</label>
                                        <select class="form-select" id="reportTimeRange">
                                            <option value="7d">最近7天</option>
                                            <option value="30d">最近30天</option>
                                            <option value="90d">最近90天</option>
                                            <option value="12m">最近12个月</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">导出格式</label>
                                        <select class="form-select" id="exportFormat">
                                            <option value="pdf">PDF</option>
                                            <option value="excel">Excel</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-file-export me-2"></i>生成报表
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Generated Reports -->
                <div class="row">
                    <div class="col-12">
                        <div class="reports-list">
                            <h5>最近生成的报表</h5>
                            <div class="table-responsive">
                                <table class="table table-hover" id="reportsTable">
                                    <thead>
                                        <tr>
                                            <th>报表名称</th>
                                            <th>类型</th>
                                            <th>生成时间</th>
                                            <th>文件大小</th>
                                            <th>状态</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Reports will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Predictions Center -->
        <div id="predictionsCenter" class="dashboard-content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="dashboard-header">
                            <h1><i class="fas fa-crystal-ball me-3"></i>预测分析</h1>
                            <p class="text-muted">基于机器学习的业务预测和趋势分析</p>
                        </div>
                    </div>
                </div>

                <!-- Prediction Models Status -->
                <div class="row mb-4" id="modelsStatus">
                    <!-- Models status will be dynamically loaded -->
                </div>

                <!-- Sales Forecast -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>销售预测</h5>
                                <div class="chart-controls">
                                    <select class="form-select form-select-sm" id="forecastPeriod">
                                        <option value="7">未来7天</option>
                                        <option value="30" selected>未来30天</option>
                                        <option value="90">未来90天</option>
                                    </select>
                                </div>
                            </div>
                            <canvas id="salesForecastChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="prediction-metrics">
                            <h5>预测准确性</h5>
                            <div class="metric-item">
                                <label>模型算法</label>
                                <span class="metric-value" id="forecastModel">自动选择</span>
                            </div>
                            <div class="metric-item">
                                <label>R² 分数</label>
                                <span class="metric-value" id="forecastR2">0.87</span>
                            </div>
                            <div class="metric-item">
                                <label>平均绝对误差</label>
                                <span class="metric-value" id="forecastMAE">¥1,234</span>
                            </div>
                            <div class="metric-item">
                                <label>置信度</label>
                                <span class="metric-value" id="forecastConfidence">95%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Churn Prediction -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>客户流失预测</h5>
                            </div>
                            <canvas id="churnPredictionChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>库存需求预测</h5>
                            </div>
                            <canvas id="inventoryForecastChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Market Trends -->
                <div class="row">
                    <div class="col-12">
                        <div class="chart-container">
                            <div class="chart-header">
                                <h5>市场趋势预测</h5>
                            </div>
                            <canvas id="marketTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="analytics-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">© 2024 万方商事株式会社 - 高级分析中心</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted">最后更新: <span id="lastUpdate">--</span></span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Analytics JS -->
    <script src="{{ asset('build/js/analytics.js') }}"></script>
    
    <script>
        // Initialize Analytics Dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeAnalytics();
        });

        // Navigation handling
        document.querySelectorAll('.nav-link[data-dashboard]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const dashboard = this.dataset.dashboard;
                switchDashboard(dashboard);
            });
        });

        function switchDashboard(dashboardName) {
            // Hide all dashboards
            document.querySelectorAll('.dashboard-content').forEach(dashboard => {
                dashboard.classList.remove('active');
            });
            
            // Remove active class from nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected dashboard
            const targetDashboard = document.getElementById(dashboardName + 'Dashboard') || 
                                  document.getElementById(dashboardName + 'Center');
            if (targetDashboard) {
                targetDashboard.classList.add('active');
            }
            
            // Add active class to clicked nav link
            document.querySelector(`[data-dashboard="${dashboardName}"]`).classList.add('active');
            
            // Load dashboard data
            loadDashboardData(dashboardName);
        }

        function loadDashboardData(dashboardName) {
            showLoading();
            
            // Simulate API call to load dashboard data
            setTimeout(() => {
                switch(dashboardName) {
                    case 'executive':
                        loadExecutiveDashboard();
                        break;
                    case 'sales':
                        loadSalesDashboard();
                        break;
                    case 'customer':
                        loadCustomerDashboard();
                        break;
                    case 'inventory':
                        loadInventoryDashboard();
                        break;
                    case 'reports':
                        loadReportsCenter();
                        break;
                    case 'predictions':
                        loadPredictionsCenter();
                        break;
                }
                hideLoading();
                updateLastUpdateTime();
            }, 1000);
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function updateLastUpdateTime() {
            document.getElementById('lastUpdate').textContent = new Date().toLocaleString('zh-CN');
        }

        function refreshData() {
            const activeDashboard = document.querySelector('.dashboard-content.active');
            const dashboardName = activeDashboard.id.replace('Dashboard', '').replace('Center', '');
            loadDashboardData(dashboardName);
        }

        function exportDashboard() {
            // Implement dashboard export functionality
            alert('导出功能开发中...');
        }
    </script>
</body>
</html>