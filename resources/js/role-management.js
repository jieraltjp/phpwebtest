// 角色管理JavaScript
class RoleManagement {
    constructor() {
        this.currentPage = 1;
        this.perPage = 10;
        this.searchTerm = '';
        this.levelFilter = '';
        this.statusFilter = '';
        this.allPermissions = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadRoles();
        this.loadPermissions();
    }

    bindEvents() {
        // 搜索事件
        document.getElementById('searchBtn').addEventListener('click', () => {
            this.searchTerm = document.getElementById('searchRole').value;
            this.currentPage = 1;
            this.loadRoles();
        });

        document.getElementById('searchRole').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.searchTerm = e.target.value;
                this.currentPage = 1;
                this.loadRoles();
            }
        });

        // 筛选事件
        document.getElementById('levelFilter').addEventListener('change', (e) => {
            this.levelFilter = e.target.value;
            this.currentPage = 1;
            this.loadRoles();
        });

        document.getElementById('statusFilter').addEventListener('change', (e) => {
            this.statusFilter = e.target.value;
            this.currentPage = 1;
            this.loadRoles();
        });

        // 保存角色
        document.getElementById('saveRoleBtn').addEventListener('click', () => {
            this.saveRole();
        });

        // 更新角色
        document.getElementById('updateRoleBtn').addEventListener('click', () => {
            this.updateRole();
        });

        // 角色名称自动生成标识
        document.getElementById('roleName').addEventListener('input', (e) => {
            const slug = this.generateSlug(e.target.value);
            document.getElementById('roleSlug').value = slug;
        });

        document.getElementById('editRoleName').addEventListener('input', (e) => {
            const slug = this.generateSlug(e.target.value);
            document.getElementById('editRoleSlug').value = slug;
        });
    }

    generateSlug(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    async loadRoles() {
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage
            });

            if (this.searchTerm) params.append('search', this.searchTerm);
            if (this.levelFilter) params.append('level', this.levelFilter);
            if (this.statusFilter) params.append('is_active', this.statusFilter);

            const response = await fetch(`/api/roles?${params}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取角色列表失败');

            const data = await response.json();
            this.renderRolesTable(data.data);
            this.renderPagination(data);
        } catch (error) {
            this.showAlert('error', error.message);
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

    renderRolesTable(roles) {
        const tbody = document.getElementById('rolesTableBody');
        tbody.innerHTML = '';

        if (roles.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">暂无数据</td></tr>';
            return;
        }

        roles.forEach(role => {
            const levelName = this.getLevelName(role.level);
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${role.id}</td>
                <td>${role.name}</td>
                <td><code>${role.slug}</code></td>
                <td><span class="badge badge-primary">${levelName}</span></td>
                <td>${role.description || '-'}</td>
                <td>${role.users_count || 0}</td>
                <td>${role.permissions_count || 0}</td>
                <td>
                    <span class="badge ${role.is_active ? 'badge-success' : 'badge-secondary'}">
                        ${role.is_active ? '激活' : '未激活'}
                    </span>
                </td>
                <td>${new Date(role.created_at).toLocaleDateString()}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-info" onclick="roleManagement.editRole(${role.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="roleManagement.deleteRole(${role.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    renderPagination(data) {
        const pagination = document.getElementById('rolesPagination');
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

        // 上一页
        if (data.prev_page_url) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">上一页</a></li>`;
        }

        // 页码
        for (let i = 1; i <= data.last_page; i++) {
            const active = i === data.current_page ? 'active' : '';
            paginationHtml += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }

        // 下一页
        if (data.next_page_url) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">下一页</a></li>`;
        }

        paginationHtml += '</ul>';
        pagination.innerHTML = paginationHtml;

        // 绑定分页点击事件
        pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(e.target.dataset.page);
                if (page && page !== this.currentPage) {
                    this.currentPage = page;
                    this.loadRoles();
                }
            });
        });
    }

    getLevelName(level) {
        const levelNames = {
            1: '超级管理员',
            2: '管理员',
            3: '经理',
            4: '员工',
            5: '客户'
        };
        return levelNames[level] || '未知';
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

        // 生成权限复选框
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

    async saveRole() {
        try {
            const form = document.getElementById('addRoleForm');
            const formData = new FormData(form);
            
            // 获取选中的权限
            const selectedPermissions = [];
            document.querySelectorAll('#permissionGroups input[type="checkbox"]:checked').forEach(checkbox => {
                selectedPermissions.push(parseInt(checkbox.value));
            });
            
            const data = {
                name: formData.get('name'),
                slug: formData.get('slug'),
                level: parseInt(formData.get('level')),
                description: formData.get('description'),
                is_active: parseInt(formData.get('is_active')),
                permissions: selectedPermissions
            };

            const response = await fetch('/api/roles', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '保存角色失败');
            }

            this.showAlert('success', '角色创建成功');
            $('#addRoleModal').modal('hide');
            form.reset();
            this.loadRoles();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async editRole(id) {
        try {
            const response = await fetch(`/api/roles/${id}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取角色详情失败');

            const role = await response.json();
            
            document.getElementById('editRoleId').value = role.id;
            document.getElementById('editRoleName').value = role.name;
            document.getElementById('editRoleSlug').value = role.slug;
            document.getElementById('editRoleLevel').value = role.level;
            document.getElementById('editRoleDescription').value = role.description || '';
            document.getElementById('editRoleStatus').value = role.is_active ? '1' : '0';

            // 渲染权限复选框
            const selectedPermissions = role.permissions ? role.permissions.map(p => p.id) : [];
            this.renderPermissionCheckboxes('editPermissionGroups', selectedPermissions);

            $('#editRoleModal').modal('show');
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async updateRole() {
        try {
            const form = document.getElementById('editRoleForm');
            const formData = new FormData(form);
            const id = formData.get('id');
            
            // 获取选中的权限
            const selectedPermissions = [];
            document.querySelectorAll('#editPermissionGroups input[type="checkbox"]:checked').forEach(checkbox => {
                selectedPermissions.push(parseInt(checkbox.value));
            });
            
            const data = {
                name: formData.get('name'),
                slug: formData.get('slug'),
                level: parseInt(formData.get('level')),
                description: formData.get('description'),
                is_active: parseInt(formData.get('is_active')),
                permissions: selectedPermissions
            };

            const response = await fetch(`/api/roles/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '更新角色失败');
            }

            this.showAlert('success', '角色更新成功');
            $('#editRoleModal').modal('hide');
            this.loadRoles();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async deleteRole(id) {
        if (!confirm('确定要删除这个角色吗？此操作不可恢复。')) {
            return;
        }

        try {
            const response = await fetch(`/api/roles/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '删除角色失败');
            }

            this.showAlert('success', '角色删除成功');
            this.loadRoles();
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

// 初始化角色管理
const roleManagement = new RoleManagement();

// 当显示添加角色模态框时，加载权限复选框
$('#addRoleModal').on('show.bs.modal', function () {
    roleManagement.renderPermissionCheckboxes('permissionGroups');
});