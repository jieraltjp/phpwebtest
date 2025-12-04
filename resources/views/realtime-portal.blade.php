@extends('layouts.app')

@section('title', '实时通信门户 - 万方商事')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/realtime-communications.css') }}">
<style>
.realtime-portal {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.portal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
    text-align: center;
}

.portal-header h1 {
    margin: 0 0 10px 0;
    font-size: 2.5rem;
    font-weight: 700;
}

.portal-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.portal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.portal-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.portal-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.portal-card h3 {
    margin: 0 0 16px 0;
    color: #2c3e50;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.portal-card .card-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #3498db;
    color: white;
    border-radius: 6px;
    font-size: 14px;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}

.status-indicator.disconnected {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.status-indicator.connecting {
    background: rgba(243, 156, 18, 0.1);
    color: #f39c12;
}

.connection-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-top: 16px;
}

.stat-item {
    text-align: center;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    display: block;
}

.stat-label {
    font-size: 0.875rem;
    color: #7f8c8d;
    margin-top: 4px;
}

.test-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 16px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
    transform: translateY(-1px);
}

.btn-success {
    background: #27ae60;
    color: white;
}

.btn-success:hover {
    background: #229954;
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover {
    background: #e67e22;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.log-container {
    background: #2c3e50;
    color: #ecf0f1;
    border-radius: 8px;
    padding: 16px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.5;
    max-height: 300px;
    overflow-y: auto;
    margin-top: 16px;
}

.log-entry {
    margin-bottom: 4px;
    padding: 4px 0;
}

.log-entry.info {
    color: #3498db;
}

.log-entry.success {
    color: #27ae60;
}

.log-entry.warning {
    color: #f39c12;
}

.log-entry.error {
    color: #e74c3c;
}

.log-timestamp {
    color: #95a5a6;
    font-size: 11px;
}

.performance-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.metric-card {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 8px;
    text-align: center;
}

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: #3498db;
    display: block;
}

.metric-label {
    font-size: 0.875rem;
    color: #7f8c8d;
    margin-top: 4px;
}

.metric-unit {
    font-size: 0.75rem;
    color: #95a5a6;
}

.channel-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ecf0f1;
    border-radius: 6px;
    padding: 8px;
}

.channel-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 6px;
}

.channel-name {
    font-family: monospace;
    font-size: 13px;
    color: #2c3e50;
}

.channel-count {
    background: #3498db;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .portal-header {
        padding: 20px;
    }
    
    .portal-header h1 {
        font-size: 2rem;
    }
    
    .portal-grid {
        grid-template-columns: 1fr;
    }
    
    .connection-stats {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
<div class="realtime-portal">
    <!-- 页面头部 -->
    <div class="portal-header">
        <h1>实时通信门户</h1>
        <p>万方商事 B2B 采购门户 - 实时通信管理中心</p>
    </div>

    <!-- 连接状态卡片 -->
    <div class="portal-grid">
        <div class="portal-card">
            <h3>
                <span class="card-icon">🔗</span>
                连接状态
            </h3>
            <div id="connectionStatus" class="status-indicator disconnected">
                <span class="status-dot"></span>
                <span class="status-text">未连接</span>
            </div>
            
            <div class="connection-stats">
                <div class="stat-item">
                    <span class="stat-value" id="totalConnections">0</span>
                    <div class="stat-label">总连接数</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="authenticatedConnections">0</span>
                    <div class="stat-label">已认证</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="messageCount">0</span>
                    <div class="stat-label">消息数</div>
                </div>
                <div class="stat-item">
                    <span class="stat-value" id="latency">0ms</span>
                    <div class="stat-label">延迟</div>
                </div>
            </div>

            <div class="test-controls">
                <button id="connectBtn" class="btn btn-primary" onclick="connectWebSocket()">
                    <i class="fas fa-plug"></i> 连接
                </button>
                <button id="disconnectBtn" class="btn btn-danger" onclick="disconnectWebSocket()" disabled>
                    <i class="fas fa-unlink"></i> 断开
                </button>
                <button id="authenticateBtn" class="btn btn-success" onclick="authenticateWebSocket()" disabled>
                    <i class="fas fa-key"></i> 认证
                </button>
            </div>
        </div>

        <!-- 频道管理 -->
        <div class="portal-card">
            <h3>
                <span class="card-icon">📡</span>
                频道管理
            </h3>
            <div class="channel-list" id="channelList">
                <div class="channel-item">
                    <span class="channel-name">暂无订阅频道</span>
                    <span class="channel-count">0</span>
                </div>
            </div>

            <div class="test-controls">
                <button class="btn btn-primary" onclick="subscribeToChannel('system_announcements')">
                    <i class="fas fa-bell"></i> 系统通知
                </button>
                <button class="btn btn-primary" onclick="subscribeToChannel('admin_orders')">
                    <i class="fas fa-shopping-cart"></i> 订单频道
                </button>
                <button class="btn btn-primary" onclick="subscribeToChannel('inventory')">
                    <i class="fas fa-boxes"></i> 库存频道
                </button>
            </div>
        </div>

        <!-- 消息测试 -->
        <div class="portal-card">
            <h3>
                <span class="card-icon">💬</span>
                消息测试
            </h3>
            <div class="test-controls">
                <button class="btn btn-warning" onclick="sendTestMessage('ping')">
                    <i class="fas fa-heartbeat"></i> 发送心跳
                </button>
                <button class="btn btn-success" onclick="sendTestMessage('test')">
                    <i class="fas fa-paper-plane"></i> 测试消息
                </button>
                <button class="btn btn-secondary" onclick="sendSystemMessage()">
                    <i class="fas fa-bullhorn"></i> 系统消息
                </button>
            </div>

            <div class="test-controls">
                <button class="btn btn-primary" onclick="sendOrderNotification()">
                    <i class="fas fa-shopping-bag"></i> 订单通知
                </button>
                <button class="btn btn-warning" onclick="sendInventoryAlert()">
                    <i class="fas fa-exclamation-triangle"></i> 库存预警
                </button>
                <button class="btn btn-success" onclick="sendChatMessage()">
                    <i class="fas fa-comments"></i> 聊天消息
                </button>
            </div>
        </div>

        <!-- 性能监控 -->
        <div class="portal-card">
            <h3>
                <span class="card-icon">📊</span>
                性能监控
            </h3>
            <div class="performance-metrics">
                <div class="metric-card">
                    <span class="metric-value" id="memoryUsage">0</span>
                    <div class="metric-label">内存使用 <span class="metric-unit">MB</span></div>
                </div>
                <div class="metric-card">
                    <span class="metric-value" id="messageRate">0</span>
                    <div class="metric-label">消息速率 <span class="metric-unit">msg/s</span></div>
                </div>
                <div class="metric-card">
                    <span class="metric-value" id="uptime">0:00</span>
                    <div class="metric-label">运行时间</div>
                </div>
                <div class="metric-card">
                    <span class="metric-value" id="errorRate">0%</span>
                    <div class="metric-label">错误率</div>
                </div>
            </div>

            <div class="test-controls">
                <button class="btn btn-primary" onclick="refreshStats()">
                    <i class="fas fa-sync"></i> 刷新统计
                </button>
                <button class="btn btn-warning" onclick="cleanupConnections()">
                    <i class="fas fa-broom"></i> 清理连接
                </button>
            </div>
        </div>
    </div>

    <!-- 日志输出 -->
    <div class="portal-card">
        <h3>
            <span class="card-icon">📝</span>
            实时日志
        </h3>
        <div class="test-controls">
            <button class="btn btn-secondary" onclick="clearLogs()">
                <i class="fas fa-trash"></i> 清空日志
            </button>
            <button class="btn btn-primary" onclick="exportLogs()">
                <i class="fas fa-download"></i> 导出日志
            </button>
        </div>
        <div class="log-container" id="logContainer">
            <div class="log-entry info">
                <span class="log-timestamp">[{{ now()->format('H:i:s') }}]</span>
                实时通信门户已加载
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/websocket-client.js') }}"></script>
<script src="{{ asset('js/realtime-notifications.js') }}"></script>
<script>
// 全局变量
let wsClient = null;
let notificationManager = null;
let stats = {
    messagesSent: 0,
    messagesReceived: 0,
    errors: 0,
    startTime: Date.now()
};

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    initializePortal();
    startMetricsUpdate();
});

/**
 * 初始化门户
 */
function initializePortal() {
    // 初始化通知管理器
    notificationManager = new RealtimeNotifications({
        position: 'top-right',
        maxNotifications: 5,
        soundEnabled: true
    });

    // 初始化WebSocket客户端
    initWebSocket();

    // 绑定事件监听器
    bindEventListeners();

    addLog('info', '门户初始化完成');
}

/**
 * 初始化WebSocket客户端
 */
function initWebSocket() {
    wsClient = new WebSocketClient({
        url: 'ws://localhost:8080',
        maxReconnectAttempts: 5,
        heartbeatInterval: 30000
    });

    // 绑定WebSocket事件
    wsClient.on('connecting', () => {
        updateConnectionStatus('connecting', '连接中...');
        addLog('info', '正在连接WebSocket服务器...');
    });

    wsClient.on('connected', () => {
        updateConnectionStatus('connected', '已连接');
        document.getElementById('connectBtn').disabled = true;
        document.getElementById('disconnectBtn').disabled = false;
        document.getElementById('authenticateBtn').disabled = false;
        addLog('success', 'WebSocket连接成功');
    });

    wsClient.on('disconnected', () => {
        updateConnectionStatus('disconnected', '未连接');
        document.getElementById('connectBtn').disabled = false;
        document.getElementById('disconnectBtn').disabled = true;
        document.getElementById('authenticateBtn').disabled = true;
        addLog('warning', 'WebSocket连接断开');
    });

    wsClient.on('authenticated', () => {
        addLog('success', 'WebSocket认证成功');
    });

    wsClient.on('message', (message) => {
        handleWebSocketMessage(message);
    });

    wsClient.on('error', (error) => {
        addLog('error', `WebSocket错误: ${error.type}`);
        stats.errors++;
    });
}

/**
 * 连接WebSocket
 */
function connectWebSocket() {
    if (wsClient) {
        wsClient.connect();
    }
}

/**
 * 断开WebSocket连接
 */
function disconnectWebSocket() {
    if (wsClient) {
        wsClient.disconnect();
    }
}

/**
 * 认证WebSocket
 */
async function authenticateWebSocket() {
    try {
        const token = localStorage.getItem('jwt_token') || 'test-token';
        const success = await wsClient.authenticate(token);
        
        if (success) {
            addLog('success', '认证请求已发送');
        } else {
            addLog('error', '认证失败');
        }
    } catch (error) {
        addLog('error', `认证错误: ${error.message}`);
    }
}

/**
 * 订阅频道
 */
function subscribeToChannel(channelName) {
    if (wsClient && wsClient.isConnected) {
        const success = wsClient.subscribe(channelName);
        if (success) {
            addLog('info', `订阅频道: ${channelName}`);
            updateChannelList();
        } else {
            addLog('error', `订阅频道失败: ${channelName}`);
        }
    } else {
        addLog('warning', '请先连接WebSocket');
    }
}

/**
 * 发送测试消息
 */
function sendTestMessage(type) {
    if (!wsClient || !wsClient.isConnected) {
        addLog('warning', 'WebSocket未连接');
        return;
    }

    let message = {};
    switch (type) {
        case 'ping':
            message = { type: 'ping' };
            break;
        case 'test':
            message = {
                type: 'client_message',
                data: {
                    action: 'test',
                    message: '这是一条测试消息',
                    timestamp: new Date().toISOString()
                }
            };
            break;
    }

    const success = wsClient.send(message);
    if (success) {
        stats.messagesSent++;
        addLog('info', `发送消息: ${type}`);
    } else {
        addLog('error', '发送消息失败');
    }
}

/**
 * 发送系统消息
 */
async function sendSystemMessage() {
    try {
        const response = await fetch('/api/websocket/system-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
            },
            body: JSON.stringify({
                title: '测试系统消息',
                message: '这是一条测试系统消息',
                type: 'info'
            })
        });

        const data = await response.json();
        if (data.status === 'success') {
            addLog('success', '系统消息发送成功');
        } else {
            addLog('error', '系统消息发送失败');
        }
    } catch (error) {
        addLog('error', `系统消息错误: ${error.message}`);
    }
}

/**
 * 发送订单通知
 */
function sendOrderNotification() {
    if (notificationManager) {
        notificationManager.show({
            type: 'order_status_changed',
            title: '订单状态更新',
            message: '订单 #12345 状态已更新为已发货',
            data: {
                order_id: 12345,
                order_number: 'ORD-2024-12345',
                old_status: 'processing',
                new_status: 'shipped'
            }
        });
        addLog('info', '订单通知已显示');
    }
}

/**
 * 发送库存预警
 */
function sendInventoryAlert() {
    if (notificationManager) {
        notificationManager.show({
            type: 'inventory_changed',
            title: '库存预警',
            message: '产品 "办公椅" 库存不足，当前库存: 5',
            data: {
                product_id: 1,
                product_name: '办公椅',
                current_stock: 5,
                threshold: 10,
                urgency: 'warning'
            }
        });
        addLog('info', '库存预警已显示');
    }
}

/**
 * 发送聊天消息
 */
function sendChatMessage() {
    if (notificationManager) {
        notificationManager.show({
            type: 'chat_message',
            title: '新消息',
            message: '您有一条来自客服的新消息',
            data: {
                from_user_id: 2,
                to_user_id: 1,
                message: '您好，有什么可以帮助您的吗？',
                chat_type: 'customer_service'
            }
        });
        addLog('info', '聊天消息已显示');
    }
}

/**
 * 处理WebSocket消息
 */
function handleWebSocketMessage(message) {
    stats.messagesReceived++;
    
    // 根据消息类型处理
    switch (message.type) {
        case 'pong':
            updateLatency(message.data?.timestamp);
            break;
        case 'channel_joined':
            addLog('success', `已加入频道: ${message.data.channel}`);
            break;
        case 'system_message':
            if (notificationManager) {
                notificationManager.show(message.data);
            }
            break;
        default:
            addLog('info', `收到消息: ${message.type}`);
    }

    updateStats();
}

/**
 * 更新连接状态
 */
function updateConnectionStatus(status, text) {
    const statusElement = document.getElementById('connectionStatus');
    const statusText = statusElement.querySelector('.status-text');
    
    statusElement.className = `status-indicator ${status}`;
    statusText.textContent = text;
}

/**
 * 更新频道列表
 */
function updateChannelList() {
    if (!wsClient) return;

    const status = wsClient.getStatus();
    const channelList = document.getElementById('channelList');
    
    if (status.subscribedChannels.length === 0) {
        channelList.innerHTML = `
            <div class="channel-item">
                <span class="channel-name">暂无订阅频道</span>
                <span class="channel-count">0</span>
            </div>
        `;
    } else {
        channelList.innerHTML = status.subscribedChannels.map(channel => `
            <div class="channel-item">
                <span class="channel-name">${channel}</span>
                <span class="channel-count">1</span>
            </div>
        `).join('');
    }
}

/**
 * 更新统计信息
 */
function updateStats() {
    if (!wsClient) return;

    const status = wsClient.getStatus();
    document.getElementById('totalConnections').textContent = status.stats?.messagesSent || 0;
    document.getElementById('authenticatedConnections').textContent = status.isAuthenticated ? '1' : '0';
    document.getElementById('messageCount').textContent = status.stats?.messagesReceived || 0;
    document.getElementById('latency').textContent = (status.stats?.latency || 0) + 'ms';
}

/**
 * 更新延迟
 */
function updateLatency(timestamp) {
    if (timestamp) {
        const latency = Date.now() - new Date(timestamp).getTime();
        document.getElementById('latency').textContent = latency + 'ms';
    }
}

/**
 * 刷新统计信息
 */
async function refreshStats() {
    try {
        const response = await fetch('/api/websocket/stats', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
            }
        });

        const data = await response.json();
        if (data.status === 'success') {
            const stats = data.data;
            document.getElementById('memoryUsage').textContent = Math.round(stats.memory_usage / 1024 / 1024);
            addLog('info', '统计信息已刷新');
        }
    } catch (error) {
        addLog('error', `刷新统计失败: ${error.message}`);
    }
}

/**
 * 清理连接
 */
async function cleanupConnections() {
    try {
        const response = await fetch('/api/websocket/cleanup', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`
            }
        });

        const data = await response.json();
        if (data.status === 'success') {
            addLog('success', `清理了 ${data.data.cleaned_connections} 个连接`);
        }
    } catch (error) {
        addLog('error', `清理连接失败: ${error.message}`);
    }
}

/**
 * 添加日志
 */
function addLog(level, message) {
    const logContainer = document.getElementById('logContainer');
    const timestamp = new Date().toLocaleTimeString();
    
    const logEntry = document.createElement('div');
    logEntry.className = `log-entry ${level}`;
    logEntry.innerHTML = `<span class="log-timestamp">[${timestamp}]</span> ${message}`;
    
    logContainer.appendChild(logEntry);
    logContainer.scrollTop = logContainer.scrollHeight;

    // 限制日志条数
    const entries = logContainer.querySelectorAll('.log-entry');
    if (entries.length > 100) {
        entries[0].remove();
    }
}

/**
 * 清空日志
 */
function clearLogs() {
    document.getElementById('logContainer').innerHTML = '';
    addLog('info', '日志已清空');
}

/**
 * 导出日志
 */
function exportLogs() {
    const logContainer = document.getElementById('logContainer');
    const logs = Array.from(logContainer.querySelectorAll('.log-entry'))
        .map(entry => entry.textContent)
        .join('\n');

    const blob = new Blob([logs], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `websocket-logs-${new Date().toISOString().slice(0, 19)}.txt`;
    a.click();
    URL.revokeObjectURL(url);

    addLog('info', '日志已导出');
}

/**
 * 绑定事件监听器
 */
function bindEventListeners() {
    // 监听通知事件
    document.addEventListener('notificationClick', (event) => {
        addLog('info', `通知被点击: ${event.detail.title}`);
    });

    document.addEventListener('notificationAction', (event) => {
        addLog('info', `通知操作: ${event.detail.actionId}`);
    });

    // 监听页面卸载事件
    window.addEventListener('beforeunload', () => {
        if (wsClient) {
            wsClient.disconnect();
        }
    });
}

/**
 * 启动指标更新
 */
function startMetricsUpdate() {
    setInterval(() => {
        // 更新运行时间
        const uptime = Date.now() - stats.startTime;
        const hours = Math.floor(uptime / 3600000);
        const minutes = Math.floor((uptime % 3600000) / 60000);
        const seconds = Math.floor((uptime % 60000) / 1000);
        document.getElementById('uptime').textContent = 
            `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        // 更新消息速率
        const messageRate = (stats.messagesSent + stats.messagesReceived) / (uptime / 1000);
        document.getElementById('messageRate').textContent = messageRate.toFixed(1);

        // 更新错误率
        const totalMessages = stats.messagesSent + stats.messagesReceived;
        const errorRate = totalMessages > 0 ? (stats.errors / totalMessages * 100) : 0;
        document.getElementById('errorRate').textContent = errorRate.toFixed(1) + '%';

        // 更新内存使用
        if (performance.memory) {
            const memoryMB = Math.round(performance.memory.usedJSHeapSize / 1024 / 1024);
            document.getElementById('memoryUsage').textContent = memoryMB;
        }

    }, 1000);
}
</script>
@endsection