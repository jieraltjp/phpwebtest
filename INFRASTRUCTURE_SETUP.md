# 微服务基础设施配置

## 概述

本文档详细描述了万方商事 B2B 采购门户微服务架构的基础设施配置，包括服务容器化、监控体系、配置中心和部署编排策略。

## 1. 服务容器化配置

### 1.1 基础 Dockerfile

```dockerfile
# docker/base/Dockerfile
FROM php:8.2-fpm-alpine

# 安装系统依赖
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    xml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# 安装 PHP 扩展
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www

# 复制应用代码
COPY . /var/www

# 安装 PHP 依赖
RUN composer install --no-dev --optimize-autoloader

# 设置权限
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# 复制配置文件
COPY docker/base/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/base/nginx.conf /etc/nginx/nginx.conf
COPY docker/base/php.ini /usr/local/etc/php/conf.d/custom.ini

# 暴露端口
EXPOSE 8080

# 启动命令
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### 1.2 用户服务 Dockerfile

```dockerfile
# docker/user-service/Dockerfile
FROM banho-portal-base:latest

# 设置环境变量
ENV APP_NAME=user-service
ENV APP_ENV=production
ENV APP_PORT=8001

# 复制用户服务特定配置
COPY docker/user-service/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/user-service/nginx.conf /etc/nginx/sites-available/default

# 创建用户服务特定目录
RUN mkdir -p /var/www/storage/logs/user-service

# 设置健康检查
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8001/health || exit 1

# 暴露端口
EXPOSE 8001

# 启动命令
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### 1.3 API 网关 Dockerfile

```dockerfile
# docker/gateway/Dockerfile
FROM nginx:alpine

# 复制网关配置
COPY docker/gateway/nginx.conf /etc/nginx/nginx.conf
COPY docker/gateway/default.conf /etc/nginx/conf.d/default.conf

# 复制 PHP 应用
COPY . /var/www/gateway

# 安装 PHP-FPM
RUN apk add --no-cache php8 php8-fpm php8-curl php8-json

# 配置 PHP-FPM
COPY docker/gateway/php-fpm.conf /etc/php8/php-fpm.conf

# 暴露端口
EXPOSE 8080

# 健康检查
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8080/health || exit 1

# 启动命令
CMD ["nginx", "-g", "daemon off;"]
```

### 1.4 多阶段构建优化

```dockerfile
# docker/base/Dockerfile.optimized
# 构建阶段
FROM composer:latest as builder

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 运行阶段
FROM php:8.2-fpm-alpine

# 安装运行时依赖
RUN apk add --no-cache \
    curl \
    nginx \
    supervisor

# 安装 PHP 扩展
RUN docker-php-ext-install pdo_mysql mbstring bcmath gd

# 从构建阶段复制依赖
COPY --from=builder /app/vendor/ /var/www/vendor/

# 复制应用代码
COPY . /var/www

# 设置权限
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# 复制配置
COPY docker/base/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/base/nginx.conf /etc/nginx/nginx.conf

EXPOSE 8080
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

## 2. Docker Compose 配置

### 2.1 开发环境配置

```yaml
# docker-compose.dev.yml
version: '3.8'

services:
  # 基础设施服务
  consul:
    image: consul:1.16
    container_name: banho-consul
    ports:
      - "8500:8500"
      - "8600:8600/udp"
    networks:
      - banho-network
    environment:
      - CONSUL_BIND_INTERFACE=eth0
    command: agent -server -bootstrap -ui -client=0.0.0.0
    volumes:
      - consul_data:/consul/data

  redis:
    image: redis:7-alpine
    container_name: banho-redis
    ports:
      - "6379:6379"
    networks:
      - banho-network
    volumes:
      - redis_data:/data
    command: redis-server --appendonly yes

  mysql-user:
    image: mysql:8.0
    container_name: banho-mysql-user
    ports:
      - "3306:3306"
    networks:
      - banho-network
    environment:
      - MYSQL_ROOT_PASSWORD=secret
      - MYSQL_DATABASE=user_db
      - MYSQL_USER=banho
      - MYSQL_PASSWORD=banho123
    volumes:
      - mysql_user_data:/var/lib/mysql
      - ./docker/mysql/init:/docker-entrypoint-initdb.d

  mysql-product:
    image: mysql:8.0
    container_name: banho-mysql-product
    ports:
      - "3307:3306"
    networks:
      - banho-network
    environment:
      - MYSQL_ROOT_PASSWORD=secret
      - MYSQL_DATABASE=product_db
      - MYSQL_USER=banho
      - MYSQL_PASSWORD=banho123
    volumes:
      - mysql_product_data:/var/lib/mysql

  mysql-order:
    image: mysql:8.0
    container_name: banho-mysql-order
    ports:
      - "3308:3306"
    networks:
      - banho-network
    environment:
      - MYSQL_ROOT_PASSWORD=secret
      - MYSQL_DATABASE=order_db
      - MYSQL_USER=banho
      - MYSQL_PASSWORD=banho123
    volumes:
      - mysql_order_data:/var/lib/mysql

  rabbitmq:
    image: rabbitmq:3.12-management-alpine
    container_name: banho-rabbitmq
    ports:
      - "5672:5672"
      - "15672:15672"
    networks:
      - banho-network
    environment:
      - RABBITMQ_DEFAULT_USER=banho
      - RABBITMQ_DEFAULT_PASS=banho123
    volumes:
      - rabbitmq_data:/var/lib/rabbitmq

  elasticsearch:
    image: elasticsearch:8.11.0
    container_name: banho-elasticsearch
    ports:
      - "9200:9200"
      - "9300:9300"
    networks:
      - banho-network
    environment:
      - discovery.type=single-node
      - xpack.security.enabled=false
      - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data

  # 应用服务
  api-gateway:
    build:
      context: .
      dockerfile: docker/gateway/Dockerfile
    container_name: banho-api-gateway
    ports:
      - "8080:8080"
    networks:
      - banho-network
    environment:
      - APP_ENV=local
      - CONSUL_HOST=consul
      - CONSUL_PORT=8500
    depends_on:
      - consul
      - user-service
      - product-service
      - order-service
    volumes:
      - ./logs:/var/www/storage/logs

  user-service:
    build:
      context: .
      dockerfile: docker/user-service/Dockerfile
    container_name: banho-user-service
    ports:
      - "8001:8001"
    networks:
      - banho-network
    environment:
      - APP_ENV=local
      - DB_HOST=mysql-user
      - DB_PORT=3306
      - DB_DATABASE=user_db
      - DB_USERNAME=banho
      - DB_PASSWORD=banho123
      - REDIS_HOST=redis
      - CONSUL_HOST=consul
      - RABBITMQ_HOST=rabbitmq
    depends_on:
      - mysql-user
      - redis
      - consul
      - rabbitmq
    volumes:
      - ./logs:/var/www/storage/logs

  product-service:
    build:
      context: .
      dockerfile: docker/product-service/Dockerfile
    container_name: banho-product-service
    ports:
      - "8002:8002"
    networks:
      - banho-network
    environment:
      - APP_ENV=local
      - DB_HOST=mysql-product
      - DB_PORT=3306
      - DB_DATABASE=product_db
      - DB_USERNAME=banho
      - DB_PASSWORD=banho123
      - REDIS_HOST=redis
      - ELASTICSEARCH_HOST=elasticsearch
      - CONSUL_HOST=consul
      - RABBITMQ_HOST=rabbitmq
    depends_on:
      - mysql-product
      - redis
      - elasticsearch
      - consul
      - rabbitmq
    volumes:
      - ./logs:/var/www/storage/logs

  order-service:
    build:
      context: .
      dockerfile: docker/order-service/Dockerfile
    container_name: banho-order-service
    ports:
      - "8003:8003"
    networks:
      - banho-network
    environment:
      - APP_ENV=local
      - DB_HOST=mysql-order
      - DB_PORT=3306
      - DB_DATABASE=order_db
      - DB_USERNAME=banho
      - DB_PASSWORD=banho123
      - REDIS_HOST=redis
      - CONSUL_HOST=consul
      - RABBITMQ_HOST=rabbitmq
    depends_on:
      - mysql-order
      - redis
      - consul
      - rabbitmq
    volumes:
      - ./logs:/var/www/storage/logs

  # 监控服务
  prometheus:
    image: prom/prometheus:latest
    container_name: banho-prometheus
    ports:
      - "9090:9090"
    networks:
      - banho-network
    volumes:
      - ./docker/prometheus/prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
      - '--web.console.libraries=/etc/prometheus/console_libraries'
      - '--web.console.templates=/etc/prometheus/consoles'

  grafana:
    image: grafana/grafana:latest
    container_name: banho-grafana
    ports:
      - "3000:3000"
    networks:
      - banho-network
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin123
    volumes:
      - grafana_data:/var/lib/grafana
      - ./docker/grafana/dashboards:/etc/grafana/provisioning/dashboards
      - ./docker/grafana/datasources:/etc/grafana/provisioning/datasources

  jaeger:
    image: jaegertracing/all-in-one:latest
    container_name: banho-jaeger
    ports:
      - "16686:16686"
      - "14268:14268"
    networks:
      - banho-network
    environment:
      - COLLECTOR_OTLP_ENABLED=true

networks:
  banho-network:
    driver: bridge

volumes:
  consul_data:
  redis_data:
  mysql_user_data:
  mysql_product_data:
  mysql_order_data:
  rabbitmq_data:
  elasticsearch_data:
  prometheus_data:
  grafana_data:
```

### 2.2 生产环境配置

```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  # 负载均衡器
  nginx-lb:
    image: nginx:alpine
    container_name: banho-nginx-lb
    ports:
      - "80:80"
      - "443:443"
    networks:
      - banho-network
    volumes:
      - ./docker/nginx/nginx-lb.conf:/etc/nginx/nginx.conf
      - ./docker/ssl:/etc/nginx/ssl
    depends_on:
      - api-gateway-1
      - api-gateway-2

  # API 网关集群
  api-gateway-1:
    build:
      context: .
      dockerfile: docker/gateway/Dockerfile
    environment:
      - APP_ENV=production
      - CONSUL_HOST=consul-cluster
    networks:
      - banho-network
    deploy:
      replicas: 2
      resources:
        limits:
          cpus: '1.0'
          memory: 1G
        reservations:
          cpus: '0.5'
          memory: 512M

  api-gateway-2:
    build:
      context: .
      dockerfile: docker/gateway/Dockerfile
    environment:
      - APP_ENV=production
      - CONSUL_HOST=consul-cluster
    networks:
      - banho-network
    deploy:
      replicas: 2
      resources:
        limits:
          cpus: '1.0'
          memory: 1G

  # 服务集群配置
  user-service:
    build:
      context: .
      dockerfile: docker/user-service/Dockerfile
    environment:
      - APP_ENV=production
      - DB_HOST=mysql-cluster
      - REDIS_HOST=redis-cluster
      - CONSUL_HOST=consul-cluster
    networks:
      - banho-network
    deploy:
      replicas: 3
      resources:
        limits:
          cpus: '0.5'
          memory: 512M

  product-service:
    build:
      context: .
      dockerfile: docker/product-service/Dockerfile
    environment:
      - APP_ENV=production
      - DB_HOST=mysql-cluster
      - REDIS_HOST=redis-cluster
      - CONSUL_HOST=consul-cluster
    networks:
      - banho-network
    deploy:
      replicas: 3
      resources:
        limits:
          cpus: '0.5'
          memory: 512M

networks:
  banho-network:
    driver: overlay
    attachable: true
```

## 3. Kubernetes 配置

### 3.1 命名空间和配置

```yaml
# k8s/namespace.yaml
apiVersion: v1
kind: Namespace
metadata:
  name: banho-portal
  labels:
    name: banho-portal
    environment: production

---
# k8s/configmap.yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: banho-config
  namespace: banho-portal
data:
  APP_ENV: "production"
  APP_DEBUG: "false"
  CONSUL_HOST: "consul-service"
  CONSUL_PORT: "8500"
  REDIS_HOST: "redis-service"
  RABBITMQ_HOST: "rabbitmq-service"

---
# k8s/secret.yaml
apiVersion: v1
kind: Secret
metadata:
  name: banho-secrets
  namespace: banho-portal
type: Opaque
data:
  DB_PASSWORD: YmFuaG8xMjM=  # base64 encoded
  REDIS_PASSWORD: YmFuaG8xMjM=
  JWT_SECRET: bXlfc2VjcmV0X2p3dF9rZXk=
```

### 3.2 服务部署配置

```yaml
# k8s/user-service.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: user-service
  namespace: banho-portal
  labels:
    app: user-service
spec:
  replicas: 3
  selector:
    matchLabels:
      app: user-service
  template:
    metadata:
      labels:
        app: user-service
    spec:
      containers:
      - name: user-service
        image: banho-portal/user-service:latest
        ports:
        - containerPort: 8001
        env:
        - name: APP_NAME
          value: "user-service"
        - name: APP_PORT
          value: "8001"
        - name: DB_HOST
          value: "mysql-user-service"
        - name: DB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: banho-secrets
              key: DB_PASSWORD
        envFrom:
        - configMapRef:
            name: banho-config
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
        livenessProbe:
          httpGet:
            path: /health
            port: 8001
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /readiness
            port: 8001
          initialDelaySeconds: 5
          periodSeconds: 5

---
apiVersion: v1
kind: Service
metadata:
  name: user-service
  namespace: banho-portal
spec:
  selector:
    app: user-service
  ports:
  - protocol: TCP
    port: 8001
    targetPort: 8001
  type: ClusterIP

---
apiVersion: v1
kind: Service
metadata:
  name: user-service-headless
  namespace: banho-portal
spec:
  selector:
    app: user-service
  ports:
  - protocol: TCP
    port: 8001
    targetPort: 8001
  clusterIP: None
```

### 3.3 API 网关配置

```yaml
# k8s/gateway.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: api-gateway
  namespace: banho-portal
spec:
  replicas: 2
  selector:
    matchLabels:
      app: api-gateway
  template:
    metadata:
      labels:
        app: api-gateway
    spec:
      containers:
      - name: api-gateway
        image: banho-portal/gateway:latest
        ports:
        - containerPort: 8080
        envFrom:
        - configMapRef:
            name: banho-config
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
        livenessProbe:
          httpGet:
            path: /health
            port: 8080
          initialDelaySeconds: 30
          periodSeconds: 10

---
apiVersion: v1
kind: Service
metadata:
  name: api-gateway
  namespace: banho-portal
spec:
  selector:
    app: api-gateway
  ports:
  - protocol: TCP
    port: 8080
    targetPort: 8080
  type: LoadBalancer
```

### 3.4 Ingress 配置

```yaml
# k8s/ingress.yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: banho-portal-ingress
  namespace: banho-portal
  annotations:
    kubernetes.io/ingress.class: nginx
    nginx.ingress.kubernetes.io/rewrite-target: /
    nginx.ingress.kubernetes.io/ssl-redirect: "true"
    cert-manager.io/cluster-issuer: letsencrypt-prod
spec:
  tls:
  - hosts:
    - api.manpou.jp
    secretName: banho-portal-tls
  rules:
  - host: api.manpou.jp
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: api-gateway
            port:
              number: 8080
```

## 4. 监控和可观测性

### 4.1 Prometheus 配置

```yaml
# docker/prometheus/prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  - "rules/*.yml"

alerting:
  alertmanagers:
    - static_configs:
        - targets:
          - alertmanager:9093

scrape_configs:
  - job_name: 'consul'
    static_configs:
      - targets: ['consul:8500']

  - job_name: 'user-service'
    consul_sd_configs:
      - server: 'consul:8500'
        services: ['user-service']
    relabel_configs:
      - source_labels: [__meta_consul_tags]
        target_label: __metrics_path__
        replacement: /metrics

  - job_name: 'product-service'
    consul_sd_configs:
      - server: 'consul:8500'
        services: ['product-service']

  - job_name: 'order-service'
    consul_sd_configs:
      - server: 'consul:8500'
        services: ['order-service']

  - job_name: 'api-gateway'
    static_configs:
      - targets: ['api-gateway:8080']

  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']

  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']
```

### 4.2 Grafana 仪表板配置

```json
{
  "dashboard": {
    "id": null,
    "title": "Banho Portal Microservices",
    "tags": ["banho", "microservices"],
    "timezone": "browser",
    "panels": [
      {
        "id": 1,
        "title": "Service Health",
        "type": "stat",
        "targets": [
          {
            "expr": "up{job=~\".*-service\"}",
            "legendFormat": "{{job}}"
          }
        ],
        "fieldConfig": {
          "defaults": {
            "mappings": [
              {
                "options": {
                  "0": {
                    "text": "DOWN",
                    "color": "red"
                  },
                  "1": {
                    "text": "UP",
                    "color": "green"
                  }
                },
                "type": "value"
              }
            ]
          }
        }
      },
      {
        "id": 2,
        "title": "Request Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(http_requests_total[5m])",
            "legendFormat": "{{service}} - {{method}}"
          }
        ]
      },
      {
        "id": 3,
        "title": "Response Time",
        "type": "graph",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "95th percentile - {{service}}"
          }
        ]
      },
      {
        "id": 4,
        "title": "Error Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(http_requests_total{status=~\"5..\"}[5m]) / rate(http_requests_total[5m])",
            "legendFormat": "Error Rate - {{service}}"
          }
        ]
      }
    ],
    "time": {
      "from": "now-1h",
      "to": "now"
    },
    "refresh": "30s"
  }
}
```

### 4.3 日志收集配置

```yaml
# docker/filebeat/filebeat.yml
filebeat.inputs:
- type: log
  enabled: true
  paths:
    - /var/www/storage/logs/*.log
  fields:
    service: ${SERVICE_NAME:unknown}
    environment: ${APP_ENV:production}
  fields_under_root: true
  multiline.pattern: '^\d{4}-\d{2}-\d{2}'
  multiline.negate: true
  multiline.match: after

output.elasticsearch:
  hosts: ["elasticsearch:9200"]
  index: "banho-logs-%{+yyyy.MM.dd}"

setup.kibana:
  host: "kibana:5601"

processors:
- add_host_metadata:
    when.not.contains.tags: forwarded
- add_docker_metadata: ~
- add_kubernetes_metadata: ~
```

## 5. 配置中心

### 5.1 Consul KV 配置

```bash
#!/bin/bash
# scripts/setup-consul-config.sh

# 基础配置
consul kv put banho-portal/config/app_name "Banho B2B Portal"
consul kv put banho-portal/config/app_version "2.0.0"
consul kv put banho-portal/config/app_env "production"

# 数据库配置
consul kv put banho-portal/database/host "mysql-cluster"
consul kv put banho-portal/database/port "3306"
consul kv put banho-portal/database/timeout "30"

# Redis 配置
consul kv put banho-portal/redis/host "redis-cluster"
consul kv put banho-portal/redis/port "6379"
consul kv put banho-portal/redis/database "0"

# JWT 配置
consul kv put banho-portal/jwt/secret "your-jwt-secret-key"
consul kv put banho-portal/jwt/ttl "3600"
consul kv put banho-portal/jwt/refresh_ttl "604800"

# 服务发现配置
consul kv put banho-portal/services/user_service/port "8001"
consul kv put banho-portal/services/product_service/port "8002"
consul kv put banho-portal/services/order_service/port "8003"

# 监控配置
consul kv put banho-portal/monitoring/prometheus_enabled "true"
consul kv put banho-portal/monitoring/jaeger_enabled "true"
consul kv put banho-portal/monitoring/metrics_interval "15"
```

### 5.2 配置服务实现

```php
<?php
// app/Services/ConfigService.php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    private Client $httpClient;
    private string $consulHost;
    private int $consulPort;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->consulHost = config('services.consul.host', 'consul');
        $this->consulPort = config('services.consul.port', 8500);
    }

    /**
     * 获取配置值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "consul_config:{$key}";
        
        return Cache::remember($cacheKey, 300, function () use ($key, $default) {
            try {
                $response = $this->httpClient->get("http://{$this->consulHost}:{$this->consulPort}/v1/kv/banho-portal/config/{$key}");
                
                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getBody()->getContents(), true);
                    
                    if (!empty($data)) {
                        return base64_decode($data[0]['Value']);
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Failed to get config from Consul: " . $e->getMessage());
            }

            return $default;
        });
    }

    /**
     * 设置配置值
     */
    public function set(string $key, mixed $value): bool
    {
        try {
            $response = $this->httpClient->put("http://{$this->consulHost}:{$this->consulPort}/v1/kv/banho-portal/config/{$key}", [
                'body' => $value
            ]);

            // 清除缓存
            Cache::forget("consul_config:{$key}");

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            \Log::error("Failed to set config in Consul: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取所有配置
     */
    public function getAll(): array
    {
        try {
            $response = $this->httpClient->get("http://{$this->consulHost}:{$this->consulPort}/v1/kv/banho-portal/config?recurse");
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                $config = [];

                foreach ($data as $item) {
                    $key = str_replace('banho-portal/config/', '', $item['Key']);
                    $config[$key] = base64_decode($item['Value']);
                }

                return $config;
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get all config from Consul: " . $e->getMessage());
        }

        return [];
    }
}
```

## 6. 部署脚本

### 6.1 自动化部署脚本

```bash
#!/bin/bash
# scripts/deploy.sh

set -e

# 配置变量
ENVIRONMENT=${1:-production}
VERSION=${2:-latest}
NAMESPACE="banho-portal"

echo "开始部署 Banho B2B Portal 到 $ENVIRONMENT 环境..."

# 检查 kubectl
if ! command -v kubectl &> /dev/null; then
    echo "错误: kubectl 未安装"
    exit 1
fi

# 检查连接
if ! kubectl cluster-info &> /dev/null; then
    echo "错误: 无法连接到 Kubernetes 集群"
    exit 1
fi

# 创建命名空间
echo "创建命名空间..."
kubectl apply -f k8s/namespace.yaml

# 应用配置
echo "应用配置和密钥..."
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/secret.yaml

# 部署基础设施服务
echo "部署基础设施服务..."
kubectl apply -f k8s/infrastructure/

# 等待基础设施就绪
echo "等待基础设施服务就绪..."
kubectl wait --for=condition=ready pod -l app=mysql -n $NAMESPACE --timeout=300s
kubectl wait --for=condition=ready pod -l app=redis -n $NAMESPACE --timeout=300s
kubectl wait --for=condition=ready pod -l app=consul -n $NAMESPACE --timeout=300s

# 部署应用服务
echo "部署应用服务..."
kubectl apply -f k8s/services/

# 等待应用服务就绪
echo "等待应用服务就绪..."
kubectl wait --for=condition=ready pod -l app=user-service -n $NAMESPACE --timeout=300s
kubectl wait --for=condition=ready pod -l app=product-service -n $NAMESPACE --timeout=300s
kubectl wait --for=condition=ready pod -l app=order-service -n $NAMESPACE --timeout=300s

# 部署 API 网关
echo "部署 API 网关..."
kubectl apply -f k8s/gateway.yaml

# 配置 Ingress
echo "配置 Ingress..."
kubectl apply -f k8s/ingress.yaml

# 验证部署
echo "验证部署状态..."
kubectl get pods -n $NAMESPACE
kubectl get services -n $NAMESPACE
kubectl get ingress -n $NAMESPACE

# 运行健康检查
echo "运行健康检查..."
./scripts/health-check.sh $NAMESPACE

echo "部署完成！"
echo "API 网关地址: $(kubectl get ingress banho-portal-ingress -n $NAMESPACE -o jsonpath='{.spec.rules[0].host}')"
```

### 6.2 健康检查脚本

```bash
#!/bin/bash
# scripts/health-check.sh

NAMESPACE=${1:-banho-portal}
SERVICES=("user-service" "product-service" "order-service" "api-gateway")

echo "开始健康检查..."

for service in "${SERVICES[@]}"; do
    echo "检查 $service..."
    
    # 检查 Pod 状态
    pod_status=$(kubectl get pods -n $NAMESPACE -l app=$service -o jsonpath='{.items[0].status.phase}')
    
    if [ "$pod_status" != "Running" ]; then
        echo "❌ $service Pod 状态异常: $pod_status"
        continue
    fi
    
    # 检查健康端点
    pod_name=$(kubectl get pods -n $NAMESPACE -l app=$service -o jsonpath='{.items[0].metadata.name}')
    
    if kubectl exec -n $NAMESPACE $pod_name -- curl -f http://localhost:8080/health > /dev/null 2>&1; then
        echo "✅ $service 健康检查通过"
    else
        echo "❌ $service 健康检查失败"
    fi
done

echo "健康检查完成"
```

### 6.3 回滚脚本

```bash
#!/bin/bash
# scripts/rollback.sh

NAMESPACE=${1:-banho-portal}
SERVICE=${2:-all}
REVISION=${3:-previous}

echo "开始回滚操作..."

if [ "$SERVICE" = "all" ]; then
    echo "回滚所有服务..."
    kubectl rollout undo deployment/user-service -n $NAMESPACE
    kubectl rollout undo deployment/product-service -n $NAMESPACE
    kubectl rollout undo deployment/order-service -n $NAMESPACE
    kubectl rollout undo deployment/api-gateway -n $NAMESPACE
else
    echo "回滚服务: $SERVICE"
    kubectl rollout undo deployment/$SERVICE -n $NAMESPACE
fi

# 等待回滚完成
echo "等待回滚完成..."
kubectl rollout status deployment/user-service -n $NAMESPACE --timeout=300s
kubectl rollout status deployment/product-service -n $NAMESPACE --timeout=300s
kubectl rollout status deployment/order-service -n $NAMESPACE --timeout=300s
kubectl rollout status deployment/api-gateway -n $NAMESPACE --timeout=300s

echo "回滚完成！"

# 验证回滚
./scripts/health-check.sh $NAMESPACE
```

---

**文档版本**: v1.0.0  
**创建日期**: 2025年12月4日  
**最后更新**: 2025年12月4日  
**维护团队**: 万方商事技术团队