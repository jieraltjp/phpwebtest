/**
 * 实时通知UI组件
 * 
 * 提供实时通知的显示、管理和交互功能
 * 支持多种通知类型、自定义样式和用户交互
 */
class RealtimeNotifications {
    constructor(options = {}) {
        this.options = {
            container: options.container || 'body',
            maxNotifications: options.maxNotifications || 5,
            displayDuration: options.displayDuration || 5000,
            position: options.position || 'top-right',
            showProgress: options.showProgress !== false,
            allowDismiss: options.allowDismiss !== false,
            soundEnabled: options.soundEnabled !== false,
            ...options
        };

        this.notifications = new Map();
        this.unreadCount = 0;
        this.container = null;
        this.isInitialized = false;

        this.init();
    }

    /**
     * 初始化通知系统
     */
    init() {
        this.createContainer();
        this.bindEvents();
        this.loadSettings();
        this.isInitialized = true;
    }

    /**
     * 创建通知容器
     */
    createContainer() {
        this.container = document.createElement('div');
        this.container.className = `realtime-notifications realtime-notifications--${this.options.position}`;
        this.container.innerHTML = `
            <div class="realtime-notifications__header" style="display: none;">
                <h4 class="realtime-notifications__title">实时通知</h4>
                <div class="realtime-notifications__controls">
                    <button class="realtime-notifications__clear-all" title="清除所有">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="realtime-notifications__settings" title="设置">
                        <i class="fas fa-cog"></i>
                    </button>
                    <button class="realtime-notifications__close" title="关闭">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="realtime-notifications__list"></div>
            <div class="realtime-notifications__empty" style="display: none;">
                <i class="fas fa-bell-slash"></i>
                <p>暂无通知</p>
            </div>
        `;

        document.querySelector(this.options.container).appendChild(this.container);
        this.listElement = this.container.querySelector('.realtime-notifications__list');
    }

    /**
     * 显示通知
     */
    show(notification) {
        if (!this.isInitialized) {
            console.warn('RealtimeNotifications not initialized');
            return;
        }

        const notificationData = {
            id: notification.id || this.generateId(),
            title: notification.title || '通知',
            message: notification.message || '',
            type: notification.type || 'info',
            timestamp: notification.timestamp || new Date().toISOString(),
            duration: notification.duration || this.options.displayDuration,
            persistent: notification.persistent || false,
            actions: notification.actions || [],
            data: notification.data || {}
        };

        // 检查通知数量限制
        if (this.notifications.size >= this.options.maxNotifications) {
            this.removeOldestNotification();
        }

        // 创建通知元素
        const notificationElement = this.createNotificationElement(notificationData);
        
        // 添加到容器
        this.listElement.appendChild(notificationElement);
        this.notifications.set(notificationData.id, {
            element: notificationElement,
            data: notificationData
        });

        // 显示动画
        requestAnimationFrame(() => {
            notificationElement.classList.add('realtime-notification--show');
        });

        // 播放声音
        if (this.options.soundEnabled) {
            this.playNotificationSound(notificationData.type);
        }

        // 更新未读计数
        this.updateUnreadCount(1);

        // 设置自动消失
        if (!notificationData.persistent) {
            setTimeout(() => {
                this.hide(notificationData.id);
            }, notificationData.duration);
        }

        // 触发显示事件
        this.onNotificationShown(notificationData);

        return notificationData.id;
    }

    /**
     * 隐藏通知
     */
    hide(notificationId) {
        const notification = this.notifications.get(notificationId);
        if (!notification) return;

        const { element, data } = notification;

        // 隐藏动画
        element.classList.add('realtime-notification--hide');
        
        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
            this.notifications.delete(notificationId);
            this.updateEmptyState();
            this.onNotificationHidden(data);
        }, 300);
    }

    /**
     * 清除所有通知
     */
    clearAll() {
        const notificationIds = Array.from(this.notifications.keys());
        notificationIds.forEach(id => this.hide(id));
        this.unreadCount = 0;
        this.updateUnreadBadge();
    }

    /**
     * 标记通知为已读
     */
    markAsRead(notificationId) {
        const notification = this.notifications.get(notificationId);
        if (notification && !notification.data.read) {
            notification.data.read = true;
            notification.element.classList.add('realtime-notification--read');
            this.updateUnreadCount(-1);
        }
    }

    /**
     * 创建通知元素
     */
    createNotificationElement(notification) {
        const element = document.createElement('div');
        element.className = `realtime-notification realtime-notification--${notification.type}`;
        element.dataset.notificationId = notification.id;

        element.innerHTML = `
            <div class="realtime-notification__content">
                <div class="realtime-notification__header">
                    <div class="realtime-notification__icon">
                        ${this.getNotificationIcon(notification.type)}
                    </div>
                    <div class="realtime-notification__title">${this.escapeHtml(notification.title)}</div>
                    ${this.options.allowDismiss ? `
                        <button class="realtime-notification__dismiss" title="关闭">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                </div>
                <div class="realtime-notification__message">${this.escapeHtml(notification.message)}</div>
                ${notification.actions.length > 0 ? `
                    <div class="realtime-notification__actions">
                        ${notification.actions.map(action => `
                            <button class="realtime-notification__action" data-action="${action.id}">
                                ${this.escapeHtml(action.label)}
                            </button>
                        `).join('')}
                    </div>
                ` : ''}
                ${this.options.showProgress && !notification.persistent ? `
                    <div class="realtime-notification__progress">
                        <div class="realtime-notification__progress-bar" style="animation-duration: ${notification.duration}ms"></div>
                    </div>
                ` : ''}
            </div>
            <div class="realtime-notification__timestamp">
                ${this.formatTimestamp(notification.timestamp)}
            </div>
        `;

        // 绑定事件
        this.bindNotificationEvents(element, notification);

        return element;
    }

    /**
     * 绑定通知事件
     */
    bindNotificationEvents(element, notification) {
        // 关闭按钮
        const dismissBtn = element.querySelector('.realtime-notification__dismiss');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.hide(notification.id);
            });
        }

        // 操作按钮
        const actionBtns = element.querySelectorAll('.realtime-notification__action');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const actionId = btn.dataset.action;
                this.handleNotificationAction(notification, actionId);
            });
        });

        // 点击通知
        element.addEventListener('click', () => {
            this.markAsRead(notification.id);
            this.handleNotificationClick(notification);
        });

        // 鼠标悬停暂停自动消失
        if (!notification.persistent) {
            let timeoutId;
            
            element.addEventListener('mouseenter', () => {
                // 暂停进度条
                const progressBar = element.querySelector('.realtime-notification__progress-bar');
                if (progressBar) {
                    progressBar.style.animationPlayState = 'paused';
                }
            });

            element.addEventListener('mouseleave', () => {
                // 恢复进度条
                const progressBar = element.querySelector('.realtime-notification__progress-bar');
                if (progressBar) {
                    progressBar.style.animationPlayState = 'running';
                }
            });
        }
    }

    /**
     * 处理通知点击
     */
    handleNotificationClick(notification) {
        // 触发自定义事件
        const event = new CustomEvent('notificationClick', {
            detail: notification
        });
        document.dispatchEvent(event);

        // 根据通知类型执行默认操作
        switch (notification.type) {
            case 'order_status_changed':
                if (notification.data.order_id) {
                    window.location.href = `/orders/${notification.data.order_id}`;
                }
                break;
                
            case 'new_order':
                if (notification.data.order_id) {
                    window.location.href = `/orders/${notification.data.order_id}`;
                }
                break;
                
            case 'chat_message':
                // 打开聊天窗口
                this.openChatWindow(notification.data.from_user_id);
                break;
                
            default:
                // 默认不执行任何操作
                break;
        }
    }

    /**
     * 处理通知操作
     */
    handleNotificationAction(notification, actionId) {
        const action = notification.actions.find(a => a.id === actionId);
        if (!action) return;

        // 执行操作
        if (action.handler) {
            action.handler(notification);
        }

        // 触发自定义事件
        const event = new CustomEvent('notificationAction', {
            detail: { notification, actionId, action }
        });
        document.dispatchEvent(event);
    }

    /**
     * 获取通知图标
     */
    getNotificationIcon(type) {
        const icons = {
            info: '<i class="fas fa-info-circle"></i>',
            success: '<i class="fas fa-check-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            error: '<i class="fas fa-times-circle"></i>',
            maintenance: '<i class="fas fa-tools"></i>',
            chat: '<i class="fas fa-comments"></i>',
            order: '<i class="fas fa-shopping-cart"></i>',
            inventory: '<i class="fas fa-boxes"></i>'
        };

        return icons[type] || icons.info;
    }

    /**
     * 播放通知声音
     */
    playNotificationSound(type) {
        if (!this.options.soundEnabled) return;

        try {
            const audio = new Audio(`/sounds/notification-${type}.mp3`);
            audio.volume = 0.3;
            audio.play().catch(() => {
                // 忽略自动播放限制错误
            });
        } catch (error) {
            console.warn('Failed to play notification sound:', error);
        }
    }

    /**
     * 更新未读计数
     */
    updateUnreadCount(delta) {
        this.unreadCount = Math.max(0, this.unreadCount + delta);
        this.updateUnreadBadge();
    }

    /**
     * 更新未读徽章
     */
    updateUnreadBadge() {
        let badge = document.querySelector('.realtime-notifications__badge');
        
        if (this.unreadCount > 0 && !badge) {
            badge = document.createElement('span');
            badge.className = 'realtime-notifications__badge';
            this.container.appendChild(badge);
        }

        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * 更新空状态显示
     */
    updateEmptyState() {
        const emptyElement = this.container.querySelector('.realtime-notifications__empty');
        const headerElement = this.container.querySelector('.realtime-notifications__header');

        if (this.notifications.size === 0) {
            if (emptyElement) emptyElement.style.display = 'block';
            if (headerElement) headerElement.style.display = 'none';
        } else {
            if (emptyElement) emptyElement.style.display = 'none';
            if (headerElement) headerElement.style.display = 'block';
        }
    }

    /**
     * 移除最旧的通知
     */
    removeOldestNotification() {
        const firstNotification = this.notifications.values().next().value;
        if (firstNotification) {
            this.hide(firstNotification.data.id);
        }
    }

    /**
     * 打开聊天窗口
     */
    openChatWindow(userId) {
        // 触发打开聊天窗口事件
        const event = new CustomEvent('openChat', {
            detail: { userId }
        });
        document.dispatchEvent(event);
    }

    /**
     * 绑定容器事件
     */
    bindEvents() {
        // 清除所有按钮
        const clearAllBtn = this.container.querySelector('.realtime-notifications__clear-all');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => {
                this.clearAll();
            });
        }

        // 设置按钮
        const settingsBtn = this.container.querySelector('.realtime-notifications__settings');
        if (settingsBtn) {
            settingsBtn.addEventListener('click', () => {
                this.showSettings();
            });
        }

        // 关闭按钮
        const closeBtn = this.container.querySelector('.realtime-notifications__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.container.classList.add('realtime-notifications--collapsed');
            });
        }
    }

    /**
     * 显示设置面板
     */
    showSettings() {
        // 创建设置面板
        const settingsPanel = document.createElement('div');
        settingsPanel.className = 'realtime-notifications__settings-panel';
        settingsPanel.innerHTML = `
            <div class="settings-panel__content">
                <h4>通知设置</h4>
                <div class="settings-panel__option">
                    <label>
                        <input type="checkbox" ${this.options.soundEnabled ? 'checked' : ''}>
                        启用声音提醒
                    </label>
                </div>
                <div class="settings-panel__option">
                    <label>
                        显示时长
                        <select>
                            <option value="3000" ${this.options.displayDuration === 3000 ? 'selected' : ''}>3秒</option>
                            <option value="5000" ${this.options.displayDuration === 5000 ? 'selected' : ''}>5秒</option>
                            <option value="10000" ${this.options.displayDuration === 10000 ? 'selected' : ''}>10秒</option>
                            <option value="0">不自动消失</option>
                        </select>
                    </label>
                </div>
                <div class="settings-panel__actions">
                    <button class="btn btn-primary">保存</button>
                    <button class="btn btn-secondary">取消</button>
                </div>
            </div>
        `;

        document.body.appendChild(settingsPanel);

        // 绑定设置面板事件
        settingsPanel.querySelector('.btn-primary').addEventListener('click', () => {
            this.saveSettings(settingsPanel);
            document.body.removeChild(settingsPanel);
        });

        settingsPanel.querySelector('.btn-secondary').addEventListener('click', () => {
            document.body.removeChild(settingsPanel);
        });
    }

    /**
     * 加载设置
     */
    loadSettings() {
        const saved = localStorage.getItem('realtime-notifications-settings');
        if (saved) {
            const settings = JSON.parse(saved);
            Object.assign(this.options, settings);
        }
    }

    /**
     * 保存设置
     */
    saveSettings(panel) {
        const soundEnabled = panel.querySelector('input[type="checkbox"]').checked;
        const duration = parseInt(panel.querySelector('select').value);

        this.options.soundEnabled = soundEnabled;
        this.options.displayDuration = duration;

        localStorage.setItem('realtime-notifications-settings', JSON.stringify({
            soundEnabled,
            displayDuration: duration
        }));
    }

    /**
     * 生成唯一ID
     */
    generateId() {
        return `notification_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * 格式化时间戳
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) {
            return '刚刚';
        } else if (diff < 3600000) {
            return `${Math.floor(diff / 60000)}分钟前`;
        } else if (diff < 86400000) {
            return `${Math.floor(diff / 3600000)}小时前`;
        } else {
            return date.toLocaleDateString();
        }
    }

    /**
     * HTML转义
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * 通知显示事件
     */
    onNotificationShown(notification) {
        console.log('Notification shown:', notification);
    }

    /**
     * 通知隐藏事件
     */
    onNotificationHidden(notification) {
        console.log('Notification hidden:', notification);
    }
}

// 导出为全局变量
window.RealtimeNotifications = RealtimeNotifications;

// 自动初始化
window.initRealtimeNotifications = function(options = {}) {
    if (!window.notificationManager) {
        window.notificationManager = new RealtimeNotifications(options);
    }
    return window.notificationManager;
};