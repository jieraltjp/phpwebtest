/**
 * Service Worker for PWA functionality
 * 提供离线缓存、后台同步等功能
 */

const CACHE_NAME = 'b2b-portal-v2.1.0';
const RUNTIME_CACHE = 'b2b-portal-runtime';
const OFFLINE_CACHE = 'b2b-portal-offline';

// 需要缓存的资源
const STATIC_CACHE_URLS = [
    '/',
    '/dashboard',
    '/products',
    '/orders',
    '/admin',
    '/admin/reports',
    '/admin/permissions',
    '/admin/roles',
    '/admin/user-permissions',
    '/css/app.css',
    '/css/japanese-effects.css',
    '/js/app.js',
    '/js/bootstrap.js',
    '/js/japanese-interactions.js',
    '/js/performance-optimizations.js',
    '/js/permission-management.js',
    '/js/role-management.js',
    '/js/user-permission-management.js',
    '/js/report-management.js',
    '/fonts/NotoSansJP-Regular.woff2',
    '/fonts/NotoSerifJP-Regular.woff2',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
    'https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&family=Noto+Sans+JP:wght@300;400;500;700&display=swap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// API缓存策略
const API_CACHE_STRATEGIES = {
    '/api/health': 'network-first',
    '/api/products': 'cache-first',
    '/api/inquiries': 'network-first',
    '/api/orders': 'network-first'
};

// 安装事件
self.addEventListener('install', event => {
    console.log('SW: 安装中...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('SW: 缓存静态资源');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => self.skipWaiting())
    );
});

// 激活事件
self.addEventListener('activate', event => {
    console.log('SW: 激活中...');
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
                        console.log('SW: 删除旧缓存', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 网络请求拦截
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // 跳过非HTTP请求
    if (!request.url.startsWith('http')) {
        return;
    }

    // API请求处理
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(handleApiRequest(request));
        return;
    }

    // 静态资源请求处理
    if (isStaticAsset(request.url)) {
        event.respondWith(handleStaticAsset(request));
        return;
    }

    // HTML页面请求处理
    if (request.destination === 'document') {
        event.respondWith(handleHtmlRequest(request));
        return;
    }
});

/**
 * 处理API请求
 */
async function handleApiRequest(request) {
    const url = new URL(request.url);
    const strategy = getCacheStrategy(url.pathname);
    
    switch (strategy) {
        case 'network-first':
            return networkFirst(request);
        case 'cache-first':
            return cacheFirst(request);
        case 'stale-while-revalidate':
            return staleWhileRevalidate(request);
        default:
            return networkFirst(request);
    }
}

/**
 * 处理静态资源请求
 */
async function handleStaticAsset(request) {
    return cacheFirst(request);
}

/**
 * 处理HTML页面请求
 */
async function handleHtmlRequest(request) {
    try {
        // 优先从网络获取
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // 缓存响应
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, networkResponse.clone());
            return networkResponse;
        }
        
        // 网络失败，从缓存获取
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // 返回离线页面
        return getOfflinePage();
        
    } catch (error) {
        console.log('HTML请求失败，尝试缓存:', error);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        return getOfflinePage();
    }
}

/**
 * Network First 策略
 */
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // 缓存GET请求的响应
            if (request.method === 'GET') {
                const cache = await caches.open(RUNTIME_CACHE);
                cache.put(request, networkResponse.clone());
            }
            
            return networkResponse;
        }
        
        throw new Error('Network response not ok');
        
    } catch (error) {
        console.log('网络请求失败，尝试缓存:', error);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // 返回错误响应
        return new Response(JSON.stringify({
            error: 'Network error',
            message: '网络连接失败，请检查网络设置'
        }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

/**
 * Cache First 策略
 */
async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        // 后台更新缓存
        updateCacheInBackground(request);
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, networkResponse.clone());
            return networkResponse;
        }
        
        throw new Error('Network response not ok');
        
    } catch (error) {
        console.log('缓存和网络都失败:', error);
        
        // 返回默认响应或错误页面
        if (request.destination === 'image') {
            return getDefaultImage();
        }
        
        return new Response('资源加载失败', { status: 404 });
    }
}

/**
 * Stale While Revalidate 策略
 */
async function staleWhileRevalidate(request) {
    const cachedResponse = await caches.match(request);
    const fetchPromise = fetch(request).then(response => {
        if (response.ok) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    });
    
    return cachedResponse || fetchPromise;
}

/**
 * 后台更新缓存
 */
function updateCacheInBackground(request) {
    fetch(request).then(response => {
        if (response.ok) {
            caches.open(RUNTIME_CACHE).then(cache => {
                cache.put(request, response);
            });
        }
    }).catch(error => {
        console.log('后台更新缓存失败:', error);
    });
}

/**
 * 获取缓存策略
 */
function getCacheStrategy(pathname) {
    for (const [pattern, strategy] of Object.entries(API_CACHE_STRATEGIES)) {
        if (pathname.startsWith(pattern)) {
            return strategy;
        }
    }
    return 'network-first';
}

/**
 * 判断是否为静态资源
 */
function isStaticAsset(url) {
    const staticExtensions = [
        '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.eot'
    ];
    
    return staticExtensions.some(ext => url.includes(ext));
}

/**
 * 获取离线页面
 */
function getOfflinePage() {
    return caches.match('/offline.html').then(response => {
        if (response) {
            return response;
        }
        
        // 返回简单的离线页面
        return new Response(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>离线模式</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    body { font-family: 'Noto Sans JP', sans-serif; text-align: center; padding: 50px; }
                    .offline-icon { font-size: 48px; color: #666; margin-bottom: 20px; }
                    h1 { color: #333; margin-bottom: 10px; }
                    p { color: #666; line-height: 1.6; }
                    .retry-btn { background: #C00000; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
                </style>
            </head>
            <body>
                <div class="offline-icon">📱</div>
                <h1>离线模式</h1>
                <p>您当前处于离线状态，请检查网络连接后重试。</p>
                <button class="retry-btn" onclick="window.location.reload()">重新加载</button>
            </body>
            </html>
        `, {
            headers: { 'Content-Type': 'text/html' }
        });
    });
}

/**
 * 获取默认图片
 */
function getDefaultImage() {
    return new Response(`
        <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
            <rect width="200" height="200" fill="#f0f0f0"/>
            <text x="100" y="100" text-anchor="middle" dy=".3em" font-family="Arial" font-size="14" fill="#999">图片加载失败</text>
        </svg>
    `, {
        headers: { 'Content-Type': 'image/svg+xml' }
    });
}

/**
 * 后台同步
 */
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

/**
 * 执行后台同步
 */
async function doBackgroundSync() {
    try {
        // 获取待同步的数据
        const pendingData = await getPendingSyncData();
        
        for (const data of pendingData) {
            try {
                await syncData(data);
                await removePendingSyncData(data.id);
            } catch (error) {
                console.log('同步失败:', error);
            }
        }
        
        // 通知用户同步完成
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({
                type: 'SYNC_COMPLETED',
                count: pendingData.length
            });
        });
        
    } catch (error) {
        console.log('后台同步失败:', error);
    }
}

/**
 * 推送通知
 */
self.addEventListener('push', event => {
    const options = {
        body: event.data ? event.data.text() : '您有新的消息',
        icon: '/icon-192x192.png',
        badge: '/badge-72x72.png',
        tag: 'b2b-portal',
        renotify: true,
        requireInteraction: false,
        actions: [
            {
                action: 'view',
                title: '查看'
            },
            {
                action: 'dismiss',
                title: '忽略'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('雅虎B2B采购门户', options)
    );
});

/**
 * 通知点击处理
 */
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow('/dashboard')
        );
    } else if (event.action === 'dismiss') {
        // 忽略通知
    } else {
        // 点击通知本身
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

/**
 * 消息处理
 */
self.addEventListener('message', event => {
    const { type, data } = event.data;
    
    switch (type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
            
        case 'CACHE_UPDATE':
            updateCache(data.urls);
            break;
            
        case 'CLEAR_CACHE':
            clearCache(data.pattern);
            break;
            
        default:
            console.log('未知消息类型:', type);
    }
});

/**
 * 更新缓存
 */
async function updateCache(urls) {
    const cache = await caches.open(RUNTIME_CACHE);
    
    for (const url of urls) {
        try {
            const response = await fetch(url);
            if (response.ok) {
                await cache.put(url, response);
            }
        } catch (error) {
            console.log('更新缓存失败:', url, error);
        }
    }
}

/**
 * 清除缓存
 */
async function clearCache(pattern) {
    const cacheNames = await caches.keys();
    
    for (const cacheName of cacheNames) {
        if (cacheName.includes(pattern)) {
            await caches.delete(cacheName);
        }
    }
}

// 辅助函数（简化版本，实际应用中需要使用IndexedDB）
async function getPendingSyncData() {
    // 实际实现应该使用IndexedDB存储待同步数据
    return [];
}

async function removePendingSyncData(id) {
    // 实际实现应该从IndexedDB删除数据
}

async function syncData(data) {
    // 实际实现应该将数据同步到服务器
    return fetch('/api/sync', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
}

console.log('Service Worker 已加载');