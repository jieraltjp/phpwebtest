@extends('layouts.app')

@section('title', '权限管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">权限管理</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPermissionModal">
                            <i class="fas fa-plus"></i> 添加权限
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 搜索和筛选 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchPermission" placeholder="搜索权限...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="groupFilter">
                                <option value="">所有组</option>
                                @foreach($permissionGroups as $group => $permissions)
                                <option value="{{ $group }}">{{ $group }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="statusFilter">
                                <option value="">所有状态</option>
                                <option value="1">激活</option>
                                <option value="0">未激活</option>
                            </select>
                        </div>
                    </div>

                    <!-- 权限列表 -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="permissionsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>标识</th>
                                    <th>组</th>
                                    <th>描述</th>
                                    <th>状态</th>
                                    <th>使用次数</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="permissionsTableBody">
                                <!-- 权限数据将通过JavaScript动态加载 -->
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="dataTables_info">
                            显示 <span id="showingFrom">1</span> 到 <span id="showingTo">10</span> 项，共 <span id="totalItems">0</span> 项
                        </div>
                        <div class="dataTables_paginate paging_simple_numbers" id="permissionsPagination">
                            <!-- 分页按钮将通过JavaScript动态生成 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加权限模态框 -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" role="dialog" aria-labelledby="addPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPermissionModalLabel">添加权限</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPermissionForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="permissionName">权限名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="permissionName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="permissionSlug">权限标识 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="permissionSlug" name="slug" required>
                                <small class="form-text text-muted">权限标识应为唯一且易于理解的字符串</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="permissionGroup">权限组</label>
                                <select class="form-control" id="permissionGroup" name="group">
                                    <option value="">选择权限组</option>
                                    <option value="用户管理">用户管理</option>
                                    <option value="产品管理">产品管理</option>
                                    <option value="订单管理">订单管理</option>
                                    <option value="询价管理">询价管理</option>
                                    <option value="系统设置">系统设置</option>
                                    <option value="报表统计">报表统计</option>
                                    <option value="其他">其他</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="permissionStatus">状态</label>
                                <select class="form-control" id="permissionStatus" name="is_active">
                                    <option value="1">激活</option>
                                    <option value="0">未激活</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="permissionDescription">描述</label>
                        <textarea class="form-control" id="permissionDescription" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="savePermissionBtn">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- 编辑权限模态框 -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" role="dialog" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPermissionModalLabel">编辑权限</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editPermissionForm">
                    <input type="hidden" id="editPermissionId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editPermissionName">权限名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editPermissionName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editPermissionSlug">权限标识 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editPermissionSlug" name="slug" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editPermissionGroup">权限组</label>
                                <select class="form-control" id="editPermissionGroup" name="group">
                                    <option value="">选择权限组</option>
                                    <option value="用户管理">用户管理</option>
                                    <option value="产品管理">产品管理</option>
                                    <option value="订单管理">订单管理</option>
                                    <option value="询价管理">询价管理</option>
                                    <option value="系统设置">系统设置</option>
                                    <option value="报表统计">报表统计</option>
                                    <option value="其他">其他</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editPermissionStatus">状态</label>
                                <select class="form-control" id="editPermissionStatus" name="is_active">
                                    <option value="1">激活</option>
                                    <option value="0">未激活</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editPermissionDescription">描述</label>
                        <textarea class="form-control" id="editPermissionDescription" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="updatePermissionBtn">更新</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush

@push('scripts')
<script src="{{ asset('js/permission-management.js') }}"></script>
@endpush