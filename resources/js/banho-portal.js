/**
 * 万方商事 B2B采购门户 - 企业级JavaScript架构
 * @version 2.0.0
 * @author 万方商事技术团队
 */

// 全局配置
const BanhoConfig = {
    api: {
        baseUrl: '/api',
        timeout: 30000,
        retryAttempts: 3,
    },
    theme: {
        primary: '#1a365d',
        secondary: '#d4af37',
        accent: '#dc2626',
    },
    language: 'ja',
    currency: 'JPY',
};

// 工具函数
const Utils = {
    /**
     * 格式化货币
     */
    formatCurrency(amount, currency = BanhoConfig.currency) {
        return new Intl.NumberFormat('ja-JP', {
            style: 'currency',
            currency: currency,
        }).format(amount);
    },

    /**
     * 格式化日期
     */
    formatDate(date, locale = 'ja-JP') {
        return new Intl.DateTimeFormat(locale, {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(date));
    },

    /**
     * 防抖函数
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * 节流函数
     */
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    /**
     * 生成UUID
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    },
};

// HTTP客户端
class HttpClient {
    constructor() {
        this.baseURL = BanhoConfig.api.baseUrl;
        this.timeout = BanhoConfig.api.timeout;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            timeout: this.timeout,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers,
            },
            ...options,
        };

        // 添加认证头
        const token = localStorage.getItem('access_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('HTTP请求失败:', error);
            throw error;
        }
    }

    async get(endpoint, params = {}) {
        const query = new URLSearchParams(params).toString();
        const url = query ? `${endpoint}?${query}` : endpoint;
        return this.request(url);
    }

    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE',
        });
    }
}

// API服务
class ApiService {
    constructor() {
        this.http = new HttpClient();
    }

    // 认证相关
    async login(credentials) {
        return this.http.post('/auth/login', credentials);
    }

    async logout() {
        return this.http.post('/auth/logout');
    }

    async getUserInfo() {
        return this.http.get('/auth/me');
    }

    // 产品相关
    async getProducts(params = {}) {
        return this.http.get('/products', params);
    }

    async getProduct(id) {
        return this.http.get(`/products/${id}`);
    }

    // 订单相关
    async getOrders(params = {}) {
        return this.http.get('/orders', params);
    }

    async createOrder(orderData) {
        return this.http.post('/orders', orderData);
    }

    async getOrder(id) {
        return this.http.get(`/orders/${id}`);
    }

    // 询价相关
    async getInquiries(params = {}) {
        return this.http.get('/inquiries', params);
    }

    async createInquiry(inquiryData) {
        return this.http.post('/inquiries', inquiryData);
    }

    // 批量采购相关
    async getBulkPurchaseQuote(items) {
        return this.http.post('/bulk-purchase/quote', { items });
    }

    async createBulkPurchaseOrder(orderData) {
        return this.http.post('/bulk-purchase/orders', orderData);
    }

    // 万方商事配置相关
    async getConfig() {
        return this.http.get('/banho/config');
    }

    async getBrandConfig() {
        return this.http.get('/banho/brand');
    }

    async exchangeRate(from, to, amount) {
        return this.http.post('/banho/exchange-rate', { from, to, amount });
    }
}

// 状态管理
class StateManager {
    constructor() {
        this.state = {
            user: null,
            config: null,
            theme: 'light',
            language: BanhoConfig.language,
            loading: false,
            notifications: [],
        };
        this.listeners = new Map();
    }

    setState(key, value) {
        this.state[key] = value;
        this.notifyListeners(key, value);
    }

    getState(key) {
        return key ? this.state[key] : this.state;
    }

    subscribe(key, callback) {
        if (!this.listeners.has(key)) {
            this.listeners.set(key, []);
        }
        this.listeners.get(key).push(callback);
    }

    notifyListeners(key, value) {
        const callbacks = this.listeners.get(key);
        if (callbacks) {
            callbacks.forEach(callback => callback(value));
        }
    }
}

// 通知系统
class NotificationManager {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(this.container);
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `banho-alert banho-alert-${type}`;
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${message}</span>
                <button class="ml-4 text-lg hover:opacity-75">&times;</button>
            </div>
        `;
        notification.style.cssText = `
            margin-bottom: 10px;
            animation: slideInRight 0.3s ease-out;
        `;

        const closeBtn = notification.querySelector('button');
        closeBtn.addEventListener('click', () => this.remove(notification));

        this.container.appendChild(notification);

        if (duration > 0) {
            setTimeout(() => this.remove(notification), duration);
        }
    }

    remove(notification) {
        notification.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    success(message, duration) {
        this.show(message, 'success', duration);
    }

    error(message, duration) {
        this.show(message, 'error', duration);
    }

    warning(message, duration) {
        this.show(message, 'warning', duration);
    }

    info(message, duration) {
        this.show(message, 'info', duration);
    }
}

// 表单验证
class FormValidator {
    constructor(formElement) {
        this.form = formElement;
        this.rules = {};
        this.errors = {};
    }

    addRule(fieldName, rule) {
        this.rules[fieldName] = rule;
    }

    validate() {
        this.errors = {};
        const formData = new FormData(this.form);

        for (const [fieldName, rule] of Object.entries(this.rules)) {
            const value = formData.get(fieldName);
            
            if (rule.required && !value) {
                this.errors[fieldName] = `${fieldName}は必須です`;
                continue;
            }

            if (rule.minLength && value.length < rule.minLength) {
                this.errors[fieldName] = `${fieldName}は${rule.minLength}文字以上でなければなりません`;
            }

            if (rule.email && value && !this.validateEmail(value)) {
                this.errors[fieldName] = `${fieldName}は有効なメールアドレスではありません`;
            }

            if (rule.custom && !rule.custom(value)) {
                this.errors[fieldName] = rule.message || `${fieldName}が無効です`;
            }
        }

        return Object.keys(this.errors).length === 0;
    }

    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    showErrors() {
        for (const [fieldName, error] of Object.entries(this.errors)) {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('is-invalid');
                
                let errorElement = field.parentNode.querySelector('.error-message');
                if (!errorElement) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'error-message text-red-500 text-sm mt-1';
                    field.parentNode.appendChild(errorElement);
                }
                errorElement.textContent = error;
            }
        }
    }

    clearErrors() {
        this.form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });
        this.form.querySelectorAll('.error-message').forEach(error => {
            error.remove();
        });
    }
}

// 图表管理器
class ChartManager {
    constructor() {
        this.charts = new Map();
        this.defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
            },
        };
    }

    createChart(canvasId, type, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`Canvas element with id '${canvasId}' not found`);
            return null;
        }

        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, {
            type,
            data,
            options: { ...this.defaultOptions, ...options },
        });

        this.charts.set(canvasId, chart);
        return chart;
    }

    getChart(canvasId) {
        return this.charts.get(canvasId);
    }

    destroyChart(canvasId) {
        const chart = this.charts.get(canvasId);
        if (chart) {
            chart.destroy();
            this.charts.delete(canvasId);
        }
    }

    updateChart(canvasId, newData) {
        const chart = this.charts.get(canvasId);
        if (chart) {
            chart.data = newData;
            chart.update();
        }
    }
}

// 初始化应用
class BanhoPortal {
    constructor() {
        this.api = new ApiService();
        this.state = new StateManager();
        this.notifications = new NotificationManager();
        this.validator = null;
        this.charts = new ChartManager();
        
        this.init();
    }

    async init() {
        try {
            // 加载配置
            await this.loadConfig();
            
            // 检查认证状态
            await this.checkAuthStatus();
            
            // 初始化事件监听
            this.initEventListeners();
            
            // 应用初始化完成
            this.notifications.success('万方商事B2B采购门户が正常に起動しました');
        } catch (error) {
            console.error('应用初始化失败:', error);
            this.notifications.error('アプリの起動に失敗しました');
        }
    }

    async loadConfig() {
        try {
            const config = await this.api.getConfig();
            if (config.status === 'success') {
                this.state.setState('config', config.data);
                this.applyTheme(config.data.brand?.colors);
            }
        } catch (error) {
            console.error('配置加载失败:', error);
        }
    }

    async checkAuthStatus() {
        const token = localStorage.getItem('access_token');
        if (token) {
            try {
                const user = await this.api.getUserInfo();
                if (user.status === 'success') {
                    this.state.setState('user', user.data);
                } else {
                    this.logout();
                }
            } catch (error) {
                this.logout();
            }
        }
    }

    applyTheme(colors) {
        if (colors) {
            const root = document.documentElement;
            root.style.setProperty('--banho-primary', colors.primary);
            root.style.setProperty('--banho-secondary', colors.secondary);
            root.style.setProperty('--banho-accent', colors.accent);
        }
    }

    initEventListeners() {
        // 全局错误处理
        window.addEventListener('error', (event) => {
            console.error('全局错误:', event.error);
            this.notifications.error('システムエラーが発生しました');
        });

        // 网络状态监听
        window.addEventListener('online', () => {
            this.notifications.success('ネットワーク接続が復旧しました');
        });

        window.addEventListener('offline', () => {
            this.notifications.warning('ネットワーク接続が切断されました');
        });
    }

    async login(credentials) {
        try {
            this.state.setState('loading', true);
            const response = await this.api.login(credentials);
            
            if (response.status === 'success') {
                localStorage.setItem('access_token', response.data.access_token);
                this.state.setState('user', response.data.user);
                this.notifications.success('ログイン成功しました');
                return true;
            } else {
                this.notifications.error('ログインに失敗しました');
                return false;
            }
        } catch (error) {
            this.notifications.error('ログインエラーが発生しました');
            return false;
        } finally {
            this.state.setState('loading', false);
        }
    }

    logout() {
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
        this.state.setState('user', null);
        this.notifications.info('ログアウトしました');
        window.location.href = '/auth';
    }
}

// 全局实例
window.BanhoPortal = new BanhoPortal();

// 添加CSS动画
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// 导出模块
window.BanhoConfig = BanhoConfig;
window.Utils = Utils;
window.HttpClient = HttpClient;
window.ApiService = ApiService;
window.StateManager = StateManager;
window.NotificationManager = NotificationManager;
window.FormValidator = FormValidator;
window.ChartManager = ChartManager;