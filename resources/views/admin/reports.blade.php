@extends('layouts.app')

@section('title', '业务报表管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">业务报表管理</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-info btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i> 刷新
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportReport()">
                            <i class="fas fa-download"></i> 导出
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="clearCache()">
                            <i class="fas fa-trash"></i> 清除缓存
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 日期选择器 -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="dateRange">时间范围</label>
                            <select class="form-control" id="dateRange" onchange="changeDateRange()">
                                <option value="last_7_days">最近7天</option>
                                <option value="last_30_days" selected>最近30天</option>
                                <option value="this_month">本月</option>
                                <option value="last_month">上月</option>
                                <option value="custom">自定义</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="customDateRange" style="display: none;">
                            <label for="startDate">开始日期</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-3" id="customDateRangeEnd" style="display: none;">
                            <label for="endDate">结束日期</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                        <div class="col-md-3">
                            <label for="reportType">报表类型</label>
                            <select class="form-control" id="reportType" onchange="switchReportType()">
                                <option value="dashboard">仪表板概览</option>
                                <option value="sales">销售报表</option>
                                <option value="user_behavior">用户行为报表</option>
                                <option value="product_analysis">产品分析报表</option>
                                <option value="inquiry_analysis">询价分析报表</option>
                                <option value="financial">财务报表</option>
                            </select>
                        </div>
                    </div>

                    <!-- 仪表板概览 -->
                    <div id="dashboardView">
                        <!-- 关键指标 -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">总收入</span>
                                        <span class="info-box-number" id="totalRevenue">¥0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-shopping-cart"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">总订单</span>
                                        <span class="info-box-number" id="totalOrders">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">活跃用户</span>
                                        <span class="info-box-number" id="activeUsers">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">转化率</span>
                                        <span class="info-box-number" id="conversionRate">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 图表区域 -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>销售趋势</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="salesTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>订单状态分布</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="orderStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>热门产品</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="topProductsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>用户活跃度</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="userActivityChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 详细报表视图 -->
                    <div id="detailReportView" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="reportTable">
                                <thead id="reportTableHeader">
                                    <!-- 表头将通过JavaScript动态生成 -->
                                </thead>
                                <tbody id="reportTableBody">
                                    <!-- 数据将通过JavaScript动态加载 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 导出报表模态框 -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">导出报表</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="form-group">
                        <label for="exportReportType">报表类型</label>
                        <select class="form-control" id="exportReportType" name="report_type">
                            <option value="sales">销售报表</option>
                            <option value="user_behavior">用户行为报表</option>
                            <option value="product_analysis">产品分析报表</option>
                            <option value="inquiry_analysis">询价分析报表</option>
                            <option value="financial">财务报表</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exportFormat">导出格式</label>
                        <select class="form-control" id="exportFormat" name="format">
                            <option value="json">JSON</option>
                            <option value="csv">CSV</option>
                            <option value="xlsx">Excel</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exportStartDate">开始日期</label>
                        <input type="date" class="form-control" id="exportStartDate" name="start_date">
                    </div>
                    <div class="form-group">
                        <label for="exportEndDate">结束日期</label>
                        <input type="date" class="form-control" id="exportEndDate" name="end_date">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="executeExport()">导出</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
.info-box {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #fff;
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}

.info-box-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
    font-size: 1.5rem;
    border-radius: 0.25rem;
}

.info-box-content {
    flex: 1;
    padding: 0 1rem;
}

.info-box-text {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
}

.info-box-number {
    display: block;
    font-weight: 700;
    font-size: 1.25rem;
}

.chart-container {
    position: relative;
    height: 300px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/report-management.js') }}"></script>
@endpush