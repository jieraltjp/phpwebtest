/**
 * 前端性能优化库
 * 提供代码分割、懒加载、缓存等功能
 */

class PerformanceOptimizer {
    constructor() {
        this.cache = new Map();
        this.loadedModules = new Set();
        this.observedElements = new Set();
        this.init();
    }

    init() {
        this.setupIntersectionObserver();
        this.setupServiceWorker();
        this.setupResourceHints();
        this.setupImageOptimization();
    }

    /**
     * 设置交叉观察器用于懒加载
     */
    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadLazyContent(entry.target);
                        this.observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.1
            });
        }
    }

    /**
     * 加载懒加载内容
     */
    loadLazyContent(element) {
        // 懒加载图片
        if (element.dataset.src) {
            element.src = element.dataset.src;
            element.onload = () => {
                element.classList.add('loaded');
            };
        }

        // 懒加载背景图片
        if (element.dataset.bg) {
            element.style.backgroundImage = `url(${element.dataset.bg})`;
            element.classList.add('bg-loaded');
        }

        // 懒加载模块
        if (element.dataset.module) {
            this.loadModule(element.dataset.module, element);
        }
    }

    /**
     * 动态加载模块
     */
    async loadModule(moduleName, container = null) {
        if (this.loadedModules.has(moduleName)) {
            return;
        }

        try {
            const module = await import(`/js/modules/${moduleName}.js`);
            this.loadedModules.add(moduleName);
            
            if (container && module.default) {
                module.default(container);
            }
            
            console.log(`模块 ${moduleName} 加载成功`);
        } catch (error) {
            console.error(`模块 ${moduleName} 加载失败:`, error);
        }
    }

    /**
     * 设置Service Worker
     */
    setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('SW registered: ', registration);
                    })
                    .catch(registrationError => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    }

    /**
     * 设置资源预加载
     */
    setupResourceHints() {
        // DNS预解析
        this.addDNSPrefetch([
            '//fonts.googleapis.com',
            '//cdn.jsdelivr.net',
            '//api.github.com'
        ]);

        // 预连接
        this.addPreconnect([
            '//fonts.googleapis.com',
            '//cdn.jsdelivr.net'
        ]);

        // 预加载关键资源
        this.preloadCriticalResources();
    }

    /**
     * 添加DNS预解析
     */
    addDNSPrefetch(domains) {
        domains.forEach(domain => {
            const link = document.createElement('link');
            link.rel = 'dns-prefetch';
            link.href = domain;
            document.head.appendChild(link);
        });
    }

    /**
     * 添加预连接
     */
    addPreconnect(origins) {
        origins.forEach(origin => {
            const link = document.createElement('link');
            link.rel = 'preconnect';
            link.href = origin;
            document.head.appendChild(link);
        });
    }

    /**
     * 预加载关键资源
     */
    preloadCriticalResources() {
        const criticalResources = [
            { href: '/css/japanese-effects.css', as: 'style' },
            { href: '/js/japanese-interactions.js', as: 'script' },
            { href: 'https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&family=Noto+Sans+JP:wght@300;400;500;700&display=swap', as: 'style' }
        ];

        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource.href;
            link.as = resource.as;
            
            if (resource.as === 'style') {
                link.onload = () => {
                    link.rel = 'stylesheet';
                };
            }
            
            document.head.appendChild(link);
        });
    }

    /**
     * 设置图片优化
     */
    setupImageOptimization() {
        // 响应式图片处理
        this.setupResponsiveImages();
        
        // WebP支持检测
        this.setupWebPSupport();
        
        // 图片懒加载
        this.setupImageLazyLoading();
    }

    /**
     * 设置响应式图片
     */
    setupResponsiveImages() {
        const images = document.querySelectorAll('img[data-sizes]');
        images.forEach(img => {
            if (img.dataset.sizes) {
                img.sizes = img.dataset.sizes;
            }
            
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
            }
        });
    }

    /**
     * 设置WebP支持
     */
    setupWebPSupport() {
        const webP = new Image();
        webP.onload = webP.onerror = () => {
            const isSupported = (webP.height === 2);
            document.documentElement.classList.toggle('webp', isSupported);
            document.documentElement.classList.toggle('no-webp', !isSupported);
            
            // 替换图片格式为WebP
            if (isSupported) {
                this.replaceImagesWithWebP();
            }
        };
        webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
    }

    /**
     * 替换图片为WebP格式
     */
    replaceImagesWithWebP() {
        const images = document.querySelectorAll('img[data-webp]');
        images.forEach(img => {
            if (img.dataset.webp) {
                img.src = img.dataset.webp;
            }
        });
    }

    /**
     * 设置图片懒加载
     */
    setupImageLazyLoading() {
        const lazyImages = document.querySelectorAll('img[data-src], picture source[data-srcset]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        imageObserver.unobserve(img);
                    }
                });
            });

            lazyImages.forEach(img => imageObserver.observe(img));
        } else {
            // 降级处理
            lazyImages.forEach(img => this.loadImage(img));
        }
    }

    /**
     * 加载图片
     */
    loadImage(img) {
        if (img.dataset.src) {
            img.src = img.dataset.src;
        }
        
        if (img.dataset.srcset) {
            img.srcset = img.dataset.srcset;
        }
        
        img.classList.add('loaded');
    }

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
    }

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
    }

    /**
     * 虚拟滚动
     */
    setupVirtualScroll(container, itemHeight, renderItem) {
        const items = Array.from(container.children);
        const visibleCount = Math.ceil(container.clientHeight / itemHeight) + 2;
        let scrollTop = 0;

        const updateVisibleItems = () => {
            const startIndex = Math.floor(scrollTop / itemHeight);
            const endIndex = Math.min(startIndex + visibleCount, items.length - 1);

            container.innerHTML = '';
            
            for (let i = startIndex; i <= endIndex; i++) {
                if (items[i]) {
                    const item = renderItem(items[i], i);
                    item.style.position = 'absolute';
                    item.style.top = `${i * itemHeight}px`;
                    container.appendChild(item);
                }
            }

            container.style.height = `${items.length * itemHeight}px`;
        };

        const handleScroll = this.throttle(() => {
            scrollTop = container.scrollTop;
            updateVisibleItems();
        }, 16);

        container.addEventListener('scroll', handleScroll);
        updateVisibleItems();
    }

    /**
     * 代码分割
     */
    async loadChunk(chunkName) {
        if (this.cache.has(chunkName)) {
            return this.cache.get(chunkName);
        }

        try {
            const chunk = await import(`/js/chunks/${chunkName}.js`);
            this.cache.set(chunkName, chunk);
            return chunk;
        } catch (error) {
            console.error(`加载代码块 ${chunkName} 失败:`, error);
            throw error;
        }
    }

    /**
     * 缓存API响应
     */
    async cacheApiResponse(url, options = {}) {
        const cacheKey = `${url}:${JSON.stringify(options)}`;
        
        // 检查缓存
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < (options.ttl || 300000)) { // 5分钟默认TTL
                return cached.data;
            }
        }

        try {
            const response = await fetch(url, options);
            const data = await response.json();

            // 缓存响应
            this.cache.set(cacheKey, {
                data,
                timestamp: Date.now()
            });

            return data;
        } catch (error) {
            console.error('API请求失败:', error);
            throw error;
        }
    }

    /**
     * 监控性能指标
     */
    setupPerformanceMonitoring() {
        if ('PerformanceObserver' in window) {
            // 监控导航性能
            const navObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    console.log('导航性能:', {
                        name: entry.name,
                        duration: entry.duration,
                        domContentLoaded: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart
                    });
                }
            });
            navObserver.observe({ entryTypes: ['navigation'] });

            // 监控资源加载性能
            const resourceObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.duration > 1000) { // 超过1秒的资源
                        console.warn('慢速资源:', {
                            name: entry.name,
                            duration: entry.duration,
                            size: entry.transferSize
                        });
                    }
                }
            });
            resourceObserver.observe({ entryTypes: ['resource'] });
        }
    }

    /**
     * 优化关键渲染路径
     */
    optimizeCriticalRenderingPath() {
        // 内联关键CSS
        this.inlineCriticalCSS();
        
        // 延迟加载非关键CSS
        this.deferNonCriticalCSS();
        
        // 异步加载JavaScript
        this.asyncLoadScripts();
    }

    /**
     * 内联关键CSS
     */
    inlineCriticalCSS() {
        const criticalCSS = `
            body { margin: 0; font-family: 'Noto Sans JP', sans-serif; }
            .loading { display: flex; justify-content: center; align-items: center; height: 100vh; }
        `;
        
        const style = document.createElement('style');
        style.textContent = criticalCSS;
        document.head.insertBefore(style, document.head.firstChild);
    }

    /**
     * 延迟加载非关键CSS
     */
    deferNonCriticalCSS() {
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"]:not([data-critical])');
        
        cssLinks.forEach(link => {
            link.media = 'print';
            link.onload = function() {
                this.media = 'all';
            };
        });
    }

    /**
     * 异步加载JavaScript
     */
    asyncLoadScripts() {
        const scripts = document.querySelectorAll('script[data-async]');
        
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            newScript.src = script.src;
            newScript.async = true;
            document.head.appendChild(newScript);
            script.remove();
        });
    }
}

// 初始化性能优化器
const performanceOptimizer = new PerformanceOptimizer();

// 导出供其他模块使用
window.PerformanceOptimizer = PerformanceOptimizer;