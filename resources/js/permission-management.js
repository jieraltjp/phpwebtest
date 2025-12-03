// 权限管理JavaScript
class PermissionManagement {
    constructor() {
        this.currentPage = 1;
        this.perPage = 10;
        this.searchTerm = '';
        this.groupFilter = '';
        this.statusFilter = '';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadPermissions();
    }

    bindEvents() {
        // 搜索事件
        document.getElementById('searchBtn').addEventListener('click', () => {
            this.searchTerm = document.getElementById('searchPermission').value;
            this.currentPage = 1;
            this.loadPermissions();
        });

        document.getElementById('searchPermission').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.searchTerm = e.target.value;
                this.currentPage = 1;
                this.loadPermissions();
            }
        });

        // 筛选事件
        document.getElementById('groupFilter').addEventListener('change', (e) => {
            this.groupFilter = e.target.value;
            this.currentPage = 1;
            this.loadPermissions();
        });

        document.getElementById('statusFilter').addEventListener('change', (e) => {
            this.statusFilter = e.target.value;
            this.currentPage = 1;
            this.loadPermissions();
        });

        // 保存权限
        document.getElementById('savePermissionBtn').addEventListener('click', () => {
            this.savePermission();
        });

        // 更新权限
        document.getElementById('updatePermissionBtn').addEventListener('click', () => {
            this.updatePermission();
        });

        // 权限名称自动生成标识
        document.getElementById('permissionName').addEventListener('input', (e) => {
            const slug = this.generateSlug(e.target.value);
            document.getElementById('permissionSlug').value = slug;
        });

        document.getElementById('editPermissionName').addEventListener('input', (e) => {
            const slug = this.generateSlug(e.target.value);
            document.getElementById('editPermissionSlug').value = slug;
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

    async loadPermissions() {
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage
            });

            if (this.searchTerm) params.append('search', this.searchTerm);
            if (this.groupFilter) params.append('group', this.groupFilter);
            if (this.statusFilter) params.append('is_active', this.statusFilter);

            const response = await fetch(`/api/permissions?${params}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取权限列表失败');

            const data = await response.json();
            this.renderPermissionsTable(data.data);
            this.renderPagination(data);
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    renderPermissionsTable(permissions) {
        const tbody = document.getElementById('permissionsTableBody');
        tbody.innerHTML = '';

        if (permissions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">暂无数据</td></tr>';
            return;
        }

        permissions.forEach(permission => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${permission.id}</td>
                <td>${permission.name}</td>
                <td><code>${permission.slug}</code></td>
                <td><span class="badge badge-info">${permission.group || '未分组'}</span></td>
                <td>${permission.description || '-'}</td>
                <td>
                    <span class="badge ${permission.is_active ? 'badge-success' : 'badge-secondary'}">
                        ${permission.is_active ? '激活' : '未激活'}
                    </span>
                </td>
                <td>${permission.usage_count || 0}</td>
                <td>${new Date(permission.created_at).toLocaleDateString()}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-info" onclick="permissionManagement.editPermission(${permission.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="permissionManagement.deletePermission(${permission.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    renderPagination(data) {
        const pagination = document.getElementById('permissionsPagination');
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
                    this.loadPermissions();
                }
            });
        });
    }

    async savePermission() {
        try {
            const form = document.getElementById('addPermissionForm');
            const formData = new FormData(form);
            
            const data = {
                name: formData.get('name'),
                slug: formData.get('slug'),
                group: formData.get('group'),
                description: formData.get('description'),
                is_active: parseInt(formData.get('is_active'))
            };

            const response = await fetch('/api/permissions', {
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
                throw new Error(error.message || '保存权限失败');
            }

            this.showAlert('success', '权限创建成功');
            $('#addPermissionModal').modal('hide');
            form.reset();
            this.loadPermissions();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async editPermission(id) {
        try {
            const response = await fetch(`/api/permissions/${id}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('获取权限详情失败');

            const permission = await response.json();
            
            document.getElementById('editPermissionId').value = permission.id;
            document.getElementById('editPermissionName').value = permission.name;
            document.getElementById('editPermissionSlug').value = permission.slug;
            document.getElementById('editPermissionGroup').value = permission.group || '';
            document.getElementById('editPermissionDescription').value = permission.description || '';
            document.getElementById('editPermissionStatus').value = permission.is_active ? '1' : '0';

            $('#editPermissionModal').modal('show');
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async updatePermission() {
        try {
            const form = document.getElementById('editPermissionForm');
            const formData = new FormData(form);
            const id = formData.get('id');
            
            const data = {
                name: formData.get('name'),
                slug: formData.get('slug'),
                group: formData.get('group'),
                description: formData.get('description'),
                is_active: parseInt(formData.get('is_active'))
            };

            const response = await fetch(`/api/permissions/${id}`, {
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
                throw new Error(error.message || '更新权限失败');
            }

            this.showAlert('success', '权限更新成功');
            $('#editPermissionModal').modal('hide');
            this.loadPermissions();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    async deletePermission(id) {
        if (!confirm('确定要删除这个权限吗？此操作不可恢复。')) {
            return;
        }

        try {
            const response = await fetch(`/api/permissions/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '删除权限失败');
            }

            this.showAlert('success', '权限删除成功');
            this.loadPermissions();
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }

    getAuthToken() {
        return localStorage.getItem('auth_token') || '';
    }

    showAlert(type, message) {
        // 简单的提示实现，可以根据实际项目使用的UI框架调整
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
        
        // 3秒后自动关闭
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) alert.remove();
        }, 3000);
    }
}

// 初始化权限管理
const permissionManagement = new PermissionManagement();