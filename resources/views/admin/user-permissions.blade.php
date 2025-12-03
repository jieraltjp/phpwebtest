@extends('layouts.app')

@section('title', '用户权限管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">用户权限管理</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#assignPermissionsModal">
                            <i class="fas fa-user-shield"></i> 批量分配权限
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 搜索和筛选 -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchUser" placeholder="搜索用户...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="roleFilter">
                                <option value="">所有角色</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
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
                        <div class="col-md-2">
                            <button type="button" class="btn btn-success btn-block" id="refreshBtn">
                                <i class="fas fa-sync-alt"></i> 刷新
                            </button>
                        </div>
                    </div>

                    <!-- 用户列表 -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="usersTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAllUsers">
                                    </th>
                                    <th>ID</th>
                                    <th>用户名</th>
                                    <th>姓名</th>
                                    <th>邮箱</th>
                                    <th>公司</th>
                                    <th>角色</th>
                                    <th>直接权限</th>
                                    <th>总权限</th>
                                    <th>状态</th>
                                    <th>最后登录</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- 用户数据将通过JavaScript动态加载 -->
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="dataTables_info">
                            显示 <span id="showingFrom">1</span> 到 <span id="showingTo">10</span> 项，共 <span id="totalItems">0</span> 项
                        </div>
                        <div class="dataTables_paginate paging_simple_numbers" id="usersPagination">
                            <!-- 分页按钮将通过JavaScript动态生成 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 分配角色模态框 -->
<div class="modal fade" id="assignRoleModal" tabindex="-1" role="dialog" aria-labelledby="assignRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignRoleModalLabel">分配角色</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignRoleForm">
                    <input type="hidden" id="assignRoleUserId" name="user_id">
                    <div class="form-group">
                        <label for="userRoles">选择角色</label>
                        <div class="role-checkboxes" id="roleCheckboxes">
                            <!-- 角色复选框将通过JavaScript动态加载 -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveUserRoleBtn">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- 分配权限模态框 -->
<div class="modal fade" id="assignPermissionsModal" tabindex="-1" role="dialog" aria-labelledby="assignPermissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignPermissionsModalLabel">分配权限</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignPermissionsForm">
                    <input type="hidden" id="assignPermissionsUserId" name="user_id">
                    <div class="form-group">
                        <label>当前用户: <span id="currentUserDisplay" class="font-weight-bold"></span></label>
                    </div>
                    <div class="form-group">
                        <label>权限分配</label>
                        <div class="permission-groups" id="userPermissionGroups">
                            <!-- 权限组将通过JavaScript动态加载 -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveUserPermissionsBtn">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- 批量分配权限模态框 -->
<div class="modal fade" id="batchAssignPermissionsModal" tabindex="-1" role="dialog" aria-labelledby="batchAssignPermissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchAssignPermissionsModalLabel">批量分配权限</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="batchAssignPermissionsForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>已选择用户 (<span id="selectedUserCount">0</span>)</label>
                                <div class="selected-users-list" id="selectedUsersList" style="max-height: 200px; overflow-y: auto;">
                                    <!-- 已选择的用户将通过JavaScript动态显示 -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>权限分配</label>
                                <div class="permission-groups" id="batchPermissionGroups">
                                    <!-- 权限组将通过JavaScript动态加载 -->
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveBatchPermissionsBtn">批量保存</button>
            </div>
        </div>
    </div>
</div>

<!-- 权限详情模态框 -->
<div class="modal fade" id="permissionDetailsModal" tabindex="-1" role="dialog" aria-labelledby="permissionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionDetailsModalLabel">用户权限详情</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>用户信息</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>用户名:</strong></td>
                                <td id="detailUsername"></td>
                            </tr>
                            <tr>
                                <td><strong>姓名:</strong></td>
                                <td id="detailName"></td>
                            </tr>
                            <tr>
                                <td><strong>邮箱:</strong></td>
                                <td id="detailEmail"></td>
                            </tr>
                            <tr>
                                <td><strong>公司:</strong></td>
                                <td id="detailCompany"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>权限统计</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>直接权限:</strong></td>
                                <td id="detailDirectPermissions"></td>
                            </tr>
                            <tr>
                                <td><strong>角色数量:</strong></td>
                                <td id="detailRoleCount"></td>
                            </tr>
                            <tr>
                                <td><strong>角色权限:</strong></td>
                                <td id="detailRolePermissions"></td>
                            </tr>
                            <tr>
                                <td><strong>总权限:</strong></td>
                                <td id="detailTotalPermissions"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
                <h6>用户角色</h6>
                <div id="userRolesList"></div>
                <hr>
                <h6>直接权限</h6>
                <div id="userDirectPermissionsList"></div>
                <hr>
                <h6>通过角色获得的权限</h6>
                <div id="userRolePermissionsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush

@push('scripts')
<script src="{{ asset('js/user-permission-management.js') }}"></script>
@endpush