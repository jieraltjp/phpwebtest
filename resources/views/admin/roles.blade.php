@extends('layouts.app')

@section('title', '角色管理')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">角色管理</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addRoleModal">
                            <i class="fas fa-plus"></i> 添加角色
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 搜索和筛选 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchRole" placeholder="搜索角色...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="levelFilter">
                                <option value="">所有级别</option>
                                <option value="1">超级管理员</option>
                                <option value="2">管理员</option>
                                <option value="3">经理</option>
                                <option value="4">员工</option>
                                <option value="5">客户</option>
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

                    <!-- 角色列表 -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="rolesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>标识</th>
                                    <th>级别</th>
                                    <th>描述</th>
                                    <th>用户数量</th>
                                    <th>权限数量</th>
                                    <th>状态</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="rolesTableBody">
                                <!-- 角色数据将通过JavaScript动态加载 -->
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="dataTables_info">
                            显示 <span id="showingFrom">1</span> 到 <span id="showingTo">10</span> 项，共 <span id="totalItems">0</span> 项
                        </div>
                        <div class="dataTables_paginate paging_simple_numbers" id="rolesPagination">
                            <!-- 分页按钮将通过JavaScript动态生成 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加角色模态框 -->
<div class="modal fade" id="addRoleModal" tabindex="-1" role="dialog" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">添加角色</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addRoleForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="roleName">角色名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="roleName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="roleSlug">角色标识 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="roleSlug" name="slug" required>
                                <small class="form-text text-muted">角色标识应为唯一且易于理解的字符串</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="roleLevel">角色级别</label>
                                <select class="form-control" id="roleLevel" name="level">
                                    <option value="1">超级管理员</option>
                                    <option value="2">管理员</option>
                                    <option value="3">经理</option>
                                    <option value="4">员工</option>
                                    <option value="5">客户</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="roleStatus">状态</label>
                                <select class="form-control" id="roleStatus" name="is_active">
                                    <option value="1">激活</option>
                                    <option value="0">未激活</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="roleDescription">描述</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>权限分配</label>
                        <div class="permission-groups" id="permissionGroups">
                            <!-- 权限组将通过JavaScript动态加载 -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveRoleBtn">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- 编辑角色模态框 -->
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">编辑角色</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editRoleForm">
                    <input type="hidden" id="editRoleId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRoleName">角色名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editRoleName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRoleSlug">角色标识 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editRoleSlug" name="slug" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRoleLevel">角色级别</label>
                                <select class="form-control" id="editRoleLevel" name="level">
                                    <option value="1">超级管理员</option>
                                    <option value="2">管理员</option>
                                    <option value="3">经理</option>
                                    <option value="4">员工</option>
                                    <option value="5">客户</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRoleStatus">状态</label>
                                <select class="form-control" id="editRoleStatus" name="is_active">
                                    <option value="1">激活</option>
                                    <option value="0">未激活</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editRoleDescription">描述</label>
                        <textarea class="form-control" id="editRoleDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>权限分配</label>
                        <div class="permission-groups" id="editPermissionGroups">
                            <!-- 权限组将通过JavaScript动态加载 -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="updateRoleBtn">更新</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush

@push('scripts')
<script src="{{ asset('js/role-management.js') }}"></script>
@endpush