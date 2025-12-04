/**
 * WebSocket客户端管理器
 * 
 * 提供WebSocket连接管理、消息处理、自动重连等功能
 * 支持频道订阅、认证、离线消息处理
 */
class WebSocketClient {
    constructor(options = {}) {
        this.url = options.url || `ws://${window.location.hostname}:8080`;
        this.token = options.token || null;
        this.connectionId = this.generateConnectionId();
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = options.maxReconnectAttempts || 10;
        this.reconnectDelay = options.reconnectDelay || 1000;
        this.heartbeatInterval = options.heartbeatInterval || 30000;
        this.heartbeatTimeout = options.heartbeatTimeout || 5000;
        
        this.ws = null;
        this.isConnected = false;
        this.isConnecting = false;
        this.isAuthenticated = false;
        
        this.channels = new Set();
        this.messageQueue = [];
        this.eventHandlers = new Map();
        this.pendingMessages = new Map();
        
        this.heartbeatTimer = null;
        this.heartbeatTimeoutTimer = null;
        this.reconnectTimer = null;
        
        // 性能监控
        this.stats = {
            messagesSent: 0,
            messagesReceived: 0,
            reconnectCount: 0,
            lastMessageTime: null,
            connectionTime: null,
            latency: 0
        };

        this.bindEventHandlers();
    }

    /**
     * 连接WebSocket服务器
     */
    async connect() {
        if (this.isConnecting || this.isConnected) {
            return;
        }

        this.isConnecting = true;
        this.emit('connecting');

        try {
            // 获取WebSocket配置
            const config = await this.fetchWebSocketConfig();
            
            // 建立WebSocket连接
            this.ws = new WebSocket(this.url);
            
            this.ws.onopen = () => this.handleOpen();
            this.ws.onmessage = (event) => this.handleMessage(event);
            this.ws.onclose = (event) => this.handleClose(event);
            this.ws.onerror = (error) => this.handleError(error);

        } catch (error) {
            this.isConnecting = false;
            this.emit('error', { type: 'connection_error', error });
            this.scheduleReconnect();
        }
    }

    /**
     * 断开WebSocket连接
     */
    disconnect() {
        this.clearTimers();
        this.shouldReconnect = false;
        
        if (this.ws) {
            this.ws.close(1000, 'Client disconnect');
            this.ws = null;
        }
        
        this.isConnected = false;
        this.isConnecting = false;
        this.isAuthenticated = false;
        
        this.emit('disconnected');
    }

    /**
     * 发送消息
     */
    send(message) {
        if (!this.isConnected) {
            this.messageQueue.push(message);
            return false;
        }

        try {
            const messageData = {
                ...message,
                connection_id: this.connectionId,
                timestamp: new Date().toISOString()
            };

            this.ws.send(JSON.stringify(messageData));
            this.stats.messagesSent++;
            this.stats.lastMessageTime = Date.now();
            
            this.emit('message_sent', messageData);
            return true;

        } catch (error) {
            this.emit('error', { type: 'send_error', error, message });
            return false;
        }
    }

    /**
     * 认证连接
     */
    async authenticate(token) {
        if (!this.isConnected) {
            throw new Error('WebSocket not connected');
        }

        try {
            const response = await fetch('/api/websocket/auth', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    connection_id: this.connectionId
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                this.send({
                    type: 'authenticate',
                    token: data.data.auth_token
                });
                
                this.token = token;
                return true;
            } else {
                throw new Error(data.message || 'Authentication failed');
            }

        } catch (error) {
            this.emit('error', { type: 'auth_error', error });
            return false;
        }
    }

    /**
     * 订阅频道
     */
    subscribe(channel) {
        if (this.channels.has(channel)) {
            return true;
        }

        const success = this.send({
            type: 'join_channel',
            channel: channel
        });

        if (success) {
            this.channels.add(channel);
            this.emit('channel_subscribed', { channel });
        }

        return success;
    }

    /**
     * 取消订阅频道
     */
    unsubscribe(channel) {
        if (!this.channels.has(channel)) {
            return true;
        }

        const success = this.send({
            type: 'leave_channel',
            channel: channel
        });

        if (success) {
            this.channels.delete(channel);
            this.emit('channel_unsubscribed', { channel });
        }

        return success;
    }

    /**
     * 发送聊天消息
     */
    sendChatMessage(toUserId, message, chatType = 'direct') {
        return this.send({
            type: 'client_message',
            data: {
                action: 'send_chat',
                to_user_id: toUserId,
                message: message,
                chat_type: chatType
            }
        });
    }

    /**
     * 获取在线用户列表
     */
    async getOnlineUsers() {
        try {
            const response = await fetch('/api/websocket/online-users', {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });

            const data = await response.json();
            return data.status === 'success' ? data.data.online_users : [];

        } catch (error) {
            this.emit('error', { type: 'api_error', error });
            return [];
        }
    }

    /**
     * 获取消息历史
     */
    async getMessageHistory(channel, limit = 50, before = null) {
        try {
            const params = new URLSearchParams({
                channel: channel,
                limit: limit.toString()
            });

            if (before) {
                params.append('before', before);
            }

            const response = await fetch(`/api/websocket/message-history?${params}`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });

            const data = await response.json();
            return data.status === 'success' ? data.data.messages : [];

        } catch (error) {
            this.emit('error', { type: 'api_error', error });
            return [];
        }
    }

    /**
     * 添加事件监听器
     */
    on(event, handler) {
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, []);
        }
        this.eventHandlers.get(event).push(handler);
    }

    /**
     * 移除事件监听器
     */
    off(event, handler) {
        if (this.eventHandlers.has(event)) {
            const handlers = this.eventHandlers.get(event);
            const index = handlers.indexOf(handler);
            if (index > -1) {
                handlers.splice(index, 1);
            }
        }
    }

    /**
     * 获取连接状态
     */
    getStatus() {
        return {
            isConnected: this.isConnected,
            isConnecting: this.isConnecting,
            isAuthenticated: this.isAuthenticated,
            connectionId: this.connectionId,
            subscribedChannels: Array.from(this.channels),
            stats: { ...this.stats },
            queuedMessages: this.messageQueue.length
        };
    }

    // 私有方法

    /**
     * 生成连接ID
     */
    generateConnectionId() {
        return `conn_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * 获取WebSocket配置
     */
    async fetchWebSocketConfig() {
        const response = await fetch('/api/websocket/config');
        const data = await response.json();
        
        if (data.status === 'success') {
            return data.data;
        } else {
            throw new Error(data.message || 'Failed to fetch config');
        }
    }

    /**
     * 处理连接打开
     */
    handleOpen() {
        this.isConnecting = false;
        this.isConnected = true;
        this.stats.connectionTime = Date.now();
        this.reconnectAttempts = 0;

        this.emit('connected');
        this.startHeartbeat();
        this.processMessageQueue();
    }

    /**
     * 处理接收到的消息
     */
    handleMessage(event) {
        try {
            const message = JSON.parse(event.data);
            this.stats.messagesReceived++;
            this.stats.lastMessageTime = Date.now();

            // 处理心跳响应
            if (message.type === 'pong') {
                this.handlePong(message);
                return;
            }

            // 处理认证响应
            if (message.type === 'authentication_success') {
                this.handleAuthenticationSuccess(message);
                return;
            }

            // 处理频道订阅响应
            if (message.type === 'channel_joined') {
                this.handleChannelJoined(message);
                return;
            }

            // 触发通用消息事件
            this.emit('message', message);
            this.emit(`message:${message.type}`, message);

        } catch (error) {
            this.emit('error', { type: 'message_parse_error', error, data: event.data });
        }
    }

    /**
     * 处理连接关闭
     */
    handleClose(event) {
        this.isConnected = false;
        this.isAuthenticated = false;
        this.clearTimers();

        this.emit('disconnected', {
            code: event.code,
            reason: event.reason,
            wasClean: event.wasClean
        });

        // 如果不是主动断开，尝试重连
        if (this.shouldReconnect !== false) {
            this.scheduleReconnect();
        }
    }

    /**
     * 处理连接错误
     */
    handleError(error) {
        this.isConnecting = false;
        this.emit('error', { type: 'websocket_error', error });
    }

    /**
     * 处理认证成功
     */
    handleAuthenticationSuccess(message) {
        this.isAuthenticated = true;
        this.emit('authenticated', message.data);

        // 重新订阅之前的频道
        this.channels.forEach(channel => {
            this.subscribe(channel);
        });
    }

    /**
     * 处理频道加入成功
     */
    handleChannelJoined(message) {
        this.emit('channel_joined', message.data);
    }

    /**
     * 开始心跳
     */
    startHeartbeat() {
        this.heartbeatTimer = setInterval(() => {
            this.sendHeartbeat();
        }, this.heartbeatInterval);

        this.sendHeartbeat();
    }

    /**
     * 发送心跳
     */
    sendHeartbeat() {
        const startTime = Date.now();
        
        this.send({
            type: 'ping',
            timestamp: new Date().toISOString()
        });

        // 设置心跳超时
        this.heartbeatTimeoutTimer = setTimeout(() => {
            this.emit('error', { type: 'heartbeat_timeout' });
            this.disconnect();
            this.scheduleReconnect();
        }, this.heartbeatTimeout);

        // 计算延迟
        this.once('message:pong', () => {
            this.stats.latency = Date.now() - startTime;
            if (this.heartbeatTimeoutTimer) {
                clearTimeout(this.heartbeatTimeoutTimer);
            }
        });
    }

    /**
     * 处理心跳响应
     */
    handlePong(message) {
        this.emit('pong', message.data);
    }

    /**
     * 清除定时器
     */
    clearTimers() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }

        if (this.heartbeatTimeoutTimer) {
            clearTimeout(this.heartbeatTimeoutTimer);
            this.heartbeatTimeoutTimer = null;
        }

        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }
    }

    /**
     * 安排重连
     */
    scheduleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            this.emit('error', { type: 'max_reconnect_attempts_reached' });
            return;
        }

        const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts);
        
        this.reconnectTimer = setTimeout(() => {
            this.reconnectAttempts++;
            this.stats.reconnectCount++;
            this.emit('reconnecting', { attempt: this.reconnectAttempts, delay });
            this.connect();
        }, delay);
    }

    /**
     * 处理消息队列
     */
    processMessageQueue() {
        while (this.messageQueue.length > 0) {
            const message = this.messageQueue.shift();
            this.send(message);
        }
    }

    /**
     * 绑定事件处理器
     */
    bindEventHandlers() {
        // 连接状态变化时的处理
        this.on('connected', () => {
            console.log('WebSocket connected');
        });

        this.on('disconnected', () => {
            console.log('WebSocket disconnected');
        });

        this.on('error', (error) => {
            console.error('WebSocket error:', error);
        });

        // 消息类型处理器
        this.on('message:order_status_changed', (message) => {
            this.showNotification('订单状态更新', message.data.message, 'info');
        });

        this.on('message:new_order', (message) => {
            this.showNotification('新订单', `订单 ${message.data.order_number} 已创建`, 'success');
        });

        this.on('message:inventory_changed', (message) => {
            if (message.data.urgency === 'critical') {
                this.showNotification('库存预警', `${message.data.product_name} 库存不足`, 'warning');
            }
        });

        this.on('message:system_message', (message) => {
            this.showNotification(message.data.title, message.data.message, message.data.message_type);
        });

        this.on('message:chat_message', (message) => {
            this.handleChatMessage(message);
        });
    }

    /**
     * 显示通知
     */
    showNotification(title, body, type = 'info') {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: body,
                icon: '/favicon.ico',
                tag: 'websocket-notification'
            });
        }

        // 触发自定义事件供UI组件处理
        this.emit('notification', { title, body, type });
    }

    /**
     * 处理聊天消息
     */
    handleChatMessage(message) {
        const { from_user_id, to_user_id, message: chatMessage } = message.data;
        
        // 如果是发给当前用户的消息
        if (to_user_id === this.getCurrentUserId()) {
            this.showNotification('新消息', chatMessage, 'info');
            this.emit('chat_message_received', message.data);
        }
    }

    /**
     * 获取当前用户ID（需要根据实际认证系统调整）
     */
    getCurrentUserId() {
        // 这里应该从认证系统获取当前用户ID
        return window.currentUser?.id || null;
    }

    /**
     * 触发事件
     */
    emit(event, data = null) {
        if (this.eventHandlers.has(event)) {
            this.eventHandlers.get(event).forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in event handler for ${event}:`, error);
                }
            });
        }
    }

    /**
     * 一次性事件监听
     */
    once(event, handler) {
        const onceHandler = (data) => {
            handler(data);
            this.off(event, onceHandler);
        };
        this.on(event, onceHandler);
    }
}

// 导出为全局变量
window.WebSocketClient = WebSocketClient;

// 自动初始化（如果页面需要）
window.initWebSocket = function(options = {}) {
    if (!window.wsClient) {
        window.wsClient = new WebSocketClient(options);
        
        // 请求通知权限
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
        
        return window.wsClient;
    }
    return window.wsClient;
};