// 用户权限管理JavaScript
class UserPermissionManagement {
    constructor() {
        this.currentPage = 1;
        this.perPage = 10;
        this.searchTerm = '';
        this.roleFilter = '';
        this.statusFilter = '';
        this.selectedUsers = [];
        this.allRoles = [];
        this.allPermissions = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadUsers();
        this.loadRoles();
        this.loadPermissions();
    }

    bindEvents() {
        // 搜索事件
        document.getElementById('searchBtn').addEventListener('click', () => {
            this.searchTerm = document.getElementById('searchUser').value;
            this.currentPage = 1;
            this.loadUsers();
        });

        document.getElementById('searchUser').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.searchTerm = e.target.value;
                this.currentPage = 1;
                this.loadUsers();
            }
        });

        // 筛选事件
        document.getElementById('roleFilter').addEventListener('change', (e) => {
            this.roleFilter = e.target.value;
            this.currentPage = 1;
            this.loadUsers();
        });

        document.getElementById('statusFilter').addEventListener('change', (e) => {
            this.statusFilter = e.target.value;
            this.currentPage = 1;
            this.loadUsers();
        });

        // 刷新按钮
        document.getElementById('refreshBtn').addEventListener('click', () => {
            this.loadUsers();
        });

        // 全选用户
        document.getElementById('selectAllUsers').addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('input[name="user_checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
            this.updateSelectedUsers();
        });

        // 保存用户角色
        document.getElementById('saveUserRoleBtn').addEventListener('click', () => {
            this.saveUserRole();
        });

        // 保存用户权限
        document.getElementById('saveUserPermissionsBtn').addEventListener('click', () => {
            this.saveUserPermissions();
        });

        // 批量保存权限
        document.getElementById('saveBatchPermissionsBtn').addEventListener('click', () => {
            this.saveBatchPermissions();
        });
    }

    updateSelectedUsers() {
        this.selectedUsers = [];
        document.querySelectorAll('input[name="user_checkbox"]:checked').forEach(checkbox => {
            this.selectedUsers.push({
                id: parseInt(checkbox.value),
                name: checkbox.dataset.name,
                username: checkbox.dataset.username
            });
        });
        
        // 更新批量分配模态框中的用户列表
        this.updateSelectedUsersList();
    }

    updateSelectedUsersList() {
        const list = document.getElementById('selectedUsersList');
        const count = document.getElementById('selectedUserCount');
        
        count.textContent = this.selectedUsers.length;
        
        if (this.selectedUsers.length === 0) {
            list.innerHTML = '<p class="text-muted">未选择用户</p>';
            return;
        }
        
        let html = '<div class="list-group">';
        this.selectedUsers.forEach(user => {
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${user.name}</strong>
                        <small class="text-muted">(${user.username})</small>
                    </div>
                    <span class="badge badge-primary">ID: ${user.id}</span>
                </div>
            `;
        });
        html += '</div>';
        list.innerHTML = html;
    }

    async loadUsers() {
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage
            });

            if (this.searchTerm) params.append('search', this.searchTerm);
            if (this.roleFilter) params.append('role_id', this.roleFilter);
            if (this.statusFilter) params.append('is_active', this.statusFilter);

            const response = await fetch(`/api/users/permissions?${params}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取用户列表失败');

            const data = await response.json();
            this.renderUsersTable(data.data);
            this.renderPagination(data);
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async loadRoles() {
        try {
            const response = await fetch('/api/roles/all', {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取角色列表失败');

            const data = await response.json();
            this.allRoles = data.data;
        } catch (error) {
            console.error('加载角色失败:', error.message);
        }
    }

    async loadPermissions() {
        try {
            const response = await fetch('/api/permissions/all', {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取权限列表失败');

            const data = await response.json();
            this.allPermissions = data.data;
        } catch (error) {
            console.error('加载权限失败:', error.message);
        }
    }

    renderUsersTable(users) {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = '';

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-center">暂无数据</td></tr>';
            return;
        }

        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="checkbox" name="user_checkbox" value="${user.id}" 
                           data-name="${user.name}" data-username="${user.username}">
                </td>
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.company_name || '-'}</td>
                <td>${this.renderUserRoles(user.roles)}</td>
                <td><span class="badge badge-info">${user.permission_stats.direct_permissions}</span></td>
                <td><span class="badge badge-success">${user.permission_stats.total_permissions}</span></td>
                <td>
                    <span class="badge ${user.is_active ? 'badge-success' : 'badge-secondary'}">
                        ${user.is_active ? '激活' : '未激活'}
                    </span>
                </td>
                <td>${user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : '-'}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick="userPermissionManagement.assignRole(${user.id})">
                        <i class="fas fa-user-tag"></i> 角色
                    </button>
                    <button type="button" class="btn btn-sm btn-info" onclick="userPermissionManagement.assignPermissions(${user.id}, '${user.name}')">
                        <i class="fas fa-user-shield"></i> 权限
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="userPermissionManagement.showPermissionDetails(${user.id})">
                        <i class="fas fa-eye"></i> 详情
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });

        // 重新绑定复选框事件
        tbody.querySelectorAll('input[name="user_checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateSelectedUsers();
            });
        });
    }

    renderUserRoles(roles) {
        if (!roles || roles.length === 0) {
            return '<span class="text-muted">无角色</span>';
        }
        
        return roles.map(role => 
            `<span class="badge badge-primary mr-1">${role.name}</span>`
        ).join('');
    }

    renderPagination(data) {
        const pagination = document.getElementById('usersPagination');
        const showingFrom = document.getElementById('showingFrom');
        const showingTo = document.getElementById('showingTo');
        const totalItems = document.getElementById('totalItems');

        showingFrom.textContent = data.from || 0;
        showingTo.textContent = data.to || 0;
        totalItems.textContent = data.total;

        if (data.last_page <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let paginationHtml = '<ul class="pagination">';

        if (data.prev_page_url) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">上一页</a></li>`;
        }

        for (let i = 1; i <= data.last_page; i++) {
            const active = i === data.current_page ? 'active' : '';
            paginationHtml += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }

        if (data.next_page_url) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">下一页</a></li>`;
        }

        paginationHtml += '</ul>';
        pagination.innerHTML = paginationHtml;

        pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(e.target.dataset.page);
                if (page && page !== this.currentPage) {
                    this.currentPage = page;
                    this.loadUsers();
                }
            });
        });
    }

    renderRoleCheckboxes(containerId, selectedRoles = []) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';

        this.allRoles.forEach(role => {
            const isChecked = selectedRoles.includes(role.id) ? 'checked' : '';
            
            const checkboxHtml = `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${role.id}" id="role_${role.id}" ${isChecked}>
                    <label class="form-check-label" for="role_${role.id}">
                        ${role.name}
                        <small class="text-muted">(${role.slug})</small>
                    </label>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', checkboxHtml);
        });
    }

    renderPermissionCheckboxes(containerId, selectedPermissions = []) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';

        // 按组分类权限
        const permissionsByGroup = {};
        this.allPermissions.forEach(permission => {
            const group = permission.group || '其他';
            if (!permissionsByGroup[group]) {
                permissionsByGroup[group] = [];
            }
            permissionsByGroup[group].push(permission);
        });

        Object.keys(permissionsByGroup).forEach(group => {
            const groupDiv = document.createElement('div');
            groupDiv.className = 'permission-group mb-3';
            
            const groupTitle = document.createElement('h6');
            groupTitle.className = 'font-weight-bold';
            groupTitle.textContent = group;
            groupDiv.appendChild(groupTitle);

            const rowDiv = document.createElement('div');
            rowDiv.className = 'row';

            permissionsByGroup[group].forEach(permission => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-6 mb-2';

                const isChecked = selectedPermissions.includes(permission.id) ? 'checked' : '';
                
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${permission.id}" id="permission_${permission.id}" ${isChecked}>
                        <label class="form-check-label" for="permission_${permission.id}">
                            ${permission.name}
                            <small class="text-muted">(${permission.slug})</small>
                        </label>
                    </div>
                `;
                rowDiv.appendChild(colDiv);
            });

            groupDiv.appendChild(rowDiv);
            container.appendChild(groupDiv);
        });
    }

    async assignRole(userId) {
        try {
            const response = await fetch(`/api/users/${userId}/roles`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取用户角色失败');

            const data = await response.json();
            const userRoles = data.data.roles ? data.data.roles.map(r => r.id) : [];
            
            document.getElementById('assignRoleUserId').value = userId;
            this.renderRoleCheckboxes('roleCheckboxes', userRoles);

            $('#assignRoleModal').modal('show');
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async assignPermissions(userId, userName) {
        try {
            const response = await fetch(`/api/users/${userId}/permissions`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取用户权限失败');

            const data = await response.json();
            const userPermissions = data.data.permissions ? data.data.permissions.map(p => p.id) : [];
            
            document.getElementById('assignPermissionsUserId').value = userId;
            document.getElementById('currentUserDisplay').textContent = userName;
            this.renderPermissionCheckboxes('userPermissionGroups', userPermissions);

            $('#assignPermissionsModal').modal('show');
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async showPermissionDetails(userId) {
        try {
            const response = await fetch(`/api/users/${userId}/permissions/details`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取用户权限详情失败');

            const data = await response.json();
            const user = data.data.user;
            const stats = data.data.stats;
            
            // 填充基本信息
            document.getElementById('detailUsername').textContent = user.username;
            document.getElementById('detailName').textContent = user.name;
            document.getElementById('detailEmail').textContent = user.email;
            document.getElementById('detailCompany').textContent = user.company_name || '-';
            
            // 填充统计信息
            document.getElementById('detailDirectPermissions').textContent = stats.direct_permissions;
            document.getElementById('detailRoleCount').textContent = stats.roles_count;
            document.getElementById('detailRolePermissions').textContent = stats.role_permissions;
            document.getElementById('detailTotalPermissions').textContent = stats.total_permissions;
            
            // 填充角色列表
            const rolesList = document.getElementById('userRolesList');
            if (user.roles && user.roles.length > 0) {
                rolesList.innerHTML = user.roles.map(role => 
                    `<span class="badge badge-primary mr-1">${role.name}</span>`
                ).join('');
            } else {
                rolesList.innerHTML = '<span class="text-muted">无角色</span>';
            }
            
            // 填充直接权限列表
            const directPermissionsList = document.getElementById('userDirectPermissionsList');
            if (user.permissions && user.permissions.length > 0) {
                directPermissionsList.innerHTML = user.permissions.map(permission => 
                    `<span class="badge badge-info mr-1">${permission.name}</span>`
                ).join('');
            } else {
                directPermissionsList.innerHTML = '<span class="text-muted">无直接权限</span>';
            }
            
            // 填充角色权限列表
            const rolePermissionsList = document.getElementById('userRolePermissionsList');
            if (data.data.role_permissions && data.data.role_permissions.length > 0) {
                rolePermissionsList.innerHTML = data.data.role_permissions.map(permission => 
                    `<span class="badge badge-success mr-1">${permission.name}</span>`
                ).join('');
            } else {
                rolePermissionsList.innerHTML = '<span class="text-muted">无角色权限</span>';
            }

            $('#permissionDetailsModal').modal('show');
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async saveUserRole() {
        try {
            const userId = document.getElementById('assignRoleUserId').value;
            
            // 获取选中的角色
            const selectedRoles = [];
            document.querySelectorAll('#roleCheckboxes input[type="checkbox"]:checked').forEach(checkbox => {
                selectedRoles.push(parseInt(checkbox.value));
            });
            
            const response = await fetch(`/api/users/${userId}/roles`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ roles: selectedRoles })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '分配角色失败');
            }

            this.showAlert('success', '角色分配成功');
            $('#assignRoleModal').modal('hide');
            this.loadUsers();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async saveUserPermissions() {
        try {
            const userId = document.getElementById('assignPermissionsUserId').value;
            
            // 获取选中的权限
            const selectedPermissions = [];
            document.querySelectorAll('#userPermissionGroups input[type="checkbox"]:checked').forEach(checkbox => {
                selectedPermissions.push(parseInt(checkbox.value));
            });
            
            const response = await fetch(`/api/users/${userId}/permissions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ permissions: selectedPermissions })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '分配权限失败');
            }

            this.showAlert('success', '权限分配成功');
            $('#assignPermissionsModal').modal('hide');
            this.loadUsers();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async saveBatchPermissions() {
        if (this.selectedUsers.length === 0) {
            this.showAlert('error', '请先选择用户');
            return;
        }

        try {
            // 获取选中的权限
            const selectedPermissions = [];
            document.querySelectorAll('#batchPermissionGroups input[type="checkbox"]:checked').forEach(checkbox => {
                selectedPermissions.push(parseInt(checkbox.value));
            });

            if (selectedPermissions.length === 0) {
                this.showAlert('error', '请选择要分配的权限');
                return;
            }
            
            const userIds = this.selectedUsers.map(user => user.id);
            
            const response = await fetch('/api/users/batch/permissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    user_ids: userIds,
                    permissions: selectedPermissions 
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '批量分配权限失败');
            }

            this.showAlert('success', `成功为 ${this.selectedUsers.length} 个用户分配权限`);
            $('#batchAssignPermissionsModal').modal('hide');
            this.loadUsers();
            
            // 清空选择
            this.selectedUsers = [];
            document.getElementById('selectAllUsers').checked = false;
            this.updateSelectedUsersList();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    getAuthToken() {
        return localStorage.getItem('auth_token') || '';
    }

    showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
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

// 初始化用户权限管理
const userPermissionManagement = new UserPermissionManagement();

// 当显示批量分配权限模态框时，加载权限复选框
$('#batchAssignPermissionsModal').on('show.bs.modal', function () {
    userPermissionManagement.renderPermissionCheckboxes('batchPermissionGroups');
});