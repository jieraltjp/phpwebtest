/**
 * 和风高级交互效果库 - Japanese Interactions Library
 * 提供丰富的用户交互体验和动画效果
 */

class JapaneseEffects {
    constructor() {
        this.init();
    }

    init() {
        this.setupMouseTracking();
        this.setupScrollEffects();
        this.setupMagneticButtons();
        this.setupRippleEffects();
        this.setupParallaxEffects();
        this.setupIntersectionObserver();
        this.setupKeyboardNavigation();
        this.setupTouchGestures();
        this.setupPerformanceOptimizations();
    }

    // 鼠标跟踪效果
    setupMouseTracking() {
        let mouseX = 0;
        let mouseY = 0;
        let currentX = 0;
        let currentY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;

            // 更新鼠标跟随光效
            const mouseGlowElements = document.querySelectorAll('.mouse-glow');
            mouseGlowElements.forEach(element => {
                const rect = element.getBoundingClientRect();
                const x = ((mouseX - rect.left) / rect.width) * 100;
                const y = ((mouseY - rect.top) / rect.height) * 100;
                element.style.setProperty('--mouse-x', `${x}%`);
                element.style.setProperty('--mouse-y', `${y}%`);
            });
        });

        // 平滑鼠标移动动画
        function animateMouseFollow() {
            currentX += (mouseX - currentX) * 0.1;
            currentY += (mouseY - currentY) * 0.1;

            // 3D视差效果 - 排除图表容器
            const parallaxElements = document.querySelectorAll('.parallax-slow');
            parallaxElements.forEach(element => {
                // 跳过图表容器，避免影响图表渲染
                if (element.closest('.chart-container') || element.closest('#swagger-ui')) {
                    return;
                }
                
                const rect = element.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                const moveX = (currentX - centerX) * 0.02;
                const moveY = (currentY - centerY) * 0.02;
                
                element.style.transform = `perspective(1000px) rotateY(${moveX}deg) rotateX(${-moveY}deg)`;
            });

            requestAnimationFrame(animateMouseFollow);
        }
        animateMouseFollow();
    }

    // 滚动效果
    setupScrollEffects() {
        let ticking = false;

        function updateScrollEffects() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;

            // 视差滚动 - 排除图表容器
            document.querySelectorAll('.parallax-slow').forEach(element => {
                // 跳过图表容器，避免影响图表渲染
                if (element.closest('.chart-container') || element.closest('#swagger-ui')) {
                    return;
                }
                element.style.transform = `translateY(${rate * 0.5}px)`;
            });

            document.querySelectorAll('.parallax-fast').forEach(element => {
                // 跳过图表容器，避免影响图表渲染
                if (element.closest('.chart-container') || element.closest('#swagger-ui')) {
                    return;
                }
                element.style.transform = `translateY(${rate}px)`;
            });

            // 导航栏效果
            const navbar = document.querySelector('.navbar, .admin-navbar, .top-navbar');
            if (navbar) {
                if (scrolled > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }

            ticking = false;
        }

        function requestTick() {
            if (!ticking) {
                window.requestAnimationFrame(updateScrollEffects);
                ticking = true;
            }
        }

        window.addEventListener('scroll', requestTick, { passive: true });
    }

    // 磁性按钮效果
    setupMagneticButtons() {
        const magneticElements = document.querySelectorAll('.magnetic');

        magneticElements.forEach(element => {
            element.addEventListener('mousemove', (e) => {
                const rect = element.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;

                element.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
            });

            element.addEventListener('mouseleave', () => {
                element.style.transform = 'translate(0, 0)';
            });
        });
    }

    // 波纹效果
    setupRippleEffects() {
        const rippleElements = document.querySelectorAll('.ripple');

        rippleElements.forEach(element => {
            element.addEventListener('click', (e) => {
                const rect = element.getBoundingClientRect();
                const ripple = document.createElement('span');
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple-effect');

                element.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    }

    // 视差效果
    setupParallaxEffects() {
        const parallaxElements = document.querySelectorAll('[data-parallax]');

        parallaxElements.forEach(element => {
            // 跳过图表容器，避免影响图表渲染
            if (element.closest('.chart-container') || element.closest('#swagger-ui')) {
                return;
            }
            
            const speed = element.dataset.parallax || 0.5;
            
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -speed;
                element.style.transform = `translateY(${rate}px)`;
            }, { passive: true });
        });
    }

    // 交叉观察器动画
    setupIntersectionObserver() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    
                    // 触发自定义动画
                    if (entry.target.dataset.animation) {
                        entry.target.style.animation = entry.target.dataset.animation;
                    }
                }
            });
        }, observerOptions);

        // 观察所有需要动画的元素
        document.querySelectorAll('.fade-in-up, .fade-in-sequence > *').forEach(el => {
            observer.observe(el);
        });
    }

    // 键盘导航增强
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // ESC键关闭模态框
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    modal.classList.remove('show');
                });
            }

            // Tab键焦点指示器
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });
    }

    // 触摸手势支持
    setupTouchGestures() {
        let touchStartX = 0;
        let touchStartY = 0;

        document.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;

            // 滑动手势
            if (Math.abs(deltaX) > 50) {
                if (deltaX > 0) {
                    this.handleSwipeRight();
                } else {
                    this.handleSwipeLeft();
                }
            }
        }, { passive: true });
    }

    handleSwipeLeft() {
        // 左滑处理逻辑
        console.log('Swipe left detected');
    }

    handleSwipeRight() {
        // 右滑处理逻辑
        console.log('Swipe right detected');
    }

    // 性能优化
    setupPerformanceOptimizations() {
        // 防抖函数
        function debounce(func, wait) {
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

        // 节流函数
        function throttle(func, limit) {
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

        // 懒加载图片
        const lazyImages = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    }

    // 工具方法
    static addClassWithDelay(element, className, delay) {
        setTimeout(() => {
            element.classList.add(className);
        }, delay);
    }

    static smoothScrollTo(target, duration = 1000) {
        const targetPosition = target.offsetTop;
        const startPosition = window.pageYOffset;
        const distance = targetPosition - startPosition;
        let startTime = null;

        function animation(currentTime) {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const run = ease(timeElapsed, startPosition, distance, duration);
            window.scrollTo(0, run);
            if (timeElapsed < duration) requestAnimationFrame(animation);
        }

        function ease(t, b, c, d) {
            t /= d / 2;
            if (t < 1) return c / 2 * t * t + b;
            t--;
            return -c / 2 * (t * (t - 2) - 1) + b;
        }

        requestAnimationFrame(animation);
    }
}

// 初始化和风效果
document.addEventListener('DOMContentLoaded', () => {
    new JapaneseEffects();
    
    // 添加页面加载动画
    document.body.classList.add('page-loaded');
    
    // 初始化工具提示
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // 初始化弹出框
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// 导出到全局
window.JapaneseEffects = JapaneseEffects;