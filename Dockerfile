# 万方商事 B2B 采购门户 Docker 镜像
# 多阶段构建，优化镜像大小和安全性

# ================================
# 第一阶段：构建阶段
# ================================
FROM node:20-alpine AS node-builder

LABEL maintainer="万方商事株式会社 <dev@manpou.jp>"
LABEL description="万方商事 B2B 采购门户前端构建"
LABEL version="2.0.0"

# 设置工作目录
WORKDIR /app

# 复制 package 文件
COPY package*.json ./

# 安装依赖
RUN npm ci --only=production && npm cache clean --force

# 复制源代码
COPY . .

# 构建前端资源
RUN npm run build

# ================================
# 第二阶段：PHP 应用阶段
# ================================
FROM php:8.2-fpm-alpine AS php-app

LABEL maintainer="万方商事株式会社 <dev@manpou.jp>"
LABEL description="万方商事 B2B 采购门户 PHP 应用"
LABEL version="2.0.0"

# 设置环境变量
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_NAME="万方商事 B2B 采购门户" \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
    PHP_OPCACHE_REVALIDATE_FREQ=0

# 安装系统依赖
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    icu-dev \
    imagemagick-dev \
    redis \
    supervisor \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_mysql \
        pdo_sqlite \
        zip \
        bcmath \
        soap \
        intl \
        opcache \
        exif \
    && pecl install imagick redis \
    && docker-php-ext-enable imagick redis \
    && rm -rf /var/cache/apk/*

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 复制应用代码
COPY . .

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# 安装 PHP 依赖
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# 复制前端构建文件
COPY --from=node-builder /app/public/build ./public/build

# 优化 Laravel 应用
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

# 创建 supervisor 配置
RUN mkdir -p /var/log/supervisor

# 复制配置文件
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zzz-custom.conf

# 暴露端口
EXPOSE 80 443

# 健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# 启动命令
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# ================================
# 第三阶段：开发环境
# ================================
FROM php-app AS development

ENV APP_ENV=local \
    APP_DEBUG=true

# 安装开发依赖
RUN apk add --no-cache \
    supervisor \
    && composer install --optimize-autoloader

# 开发环境启动命令
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]