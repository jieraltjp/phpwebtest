# 万方商事 B2B 采购门户 - 自动化测试文档

## 概述

本文档详细介绍了万方商事 B2B 采购门户项目的自动化测试体系，包括测试架构、覆盖范围、执行指南和最佳实践。

## 测试架构

### 测试类型

1. **单元测试 (Unit Tests)**
   - 服务层测试 (Services)
   - 业务逻辑验证
   - 边界条件测试
   - 错误处理测试

2. **功能测试 (Feature Tests)**
   - API 端点测试
   - 用户交互流程测试
   - 数据库集成测试
   - 认证授权测试

3. **性能和安全测试 (Performance & Security Tests)**
   - API 响应时间测试
   - 缓存性能测试
   - 加密性能测试
   - 安全漏洞测试

### 测试目录结构

```
tests/
├── CreatesApplication.php          # 测试应用创建 Trait
├── TestCase.php                    # 基础测试类
├── Unit/
│   ├── Services/
│   │   ├── CacheServiceTest.php    # 缓存服务测试
│   │   ├── EncryptionServiceTest.php # 加密服务测试
│   │   └── ValidationServiceTest.php # 验证服务测试
│   └── ExampleTest.php
├── Feature/
│   ├── Api/
│   │   ├── AuthControllerTest.php  # 认证 API 测试
│   │   ├── ProductControllerTest.php # 产品 API 测试
│   │   ├── OrderControllerTest.php # 订单 API 测试
│   │   ├── InquiryControllerTest.php # 询价 API 测试
│   │   └── BulkPurchaseControllerTest.php # 批量采购 API 测试
│   ├── DatabaseIntegrationTest.php # 数据库集成测试
│   ├── PerformanceSecurityTest.php # 性能安全测试
│   └── ExampleTest.php
└── TestCase.php                    # 测试基类
```

## 测试覆盖范围

### 1. API 功能测试

#### 认证系统 (AuthControllerTest)
- ✅ 用户登录/登出
- ✅ 用户注册
- ✅ 令牌刷新
- ✅ 用户信息获取
- ✅ 用户名/邮箱可用性检查
- ✅ 并发登录限制
- ✅ 令牌缓存和失效

#### 产品管理 (ProductControllerTest)
- ✅ 产品列表查询 (分页、筛选、搜索)
- ✅ 产品详情查看
- ✅ 产品缓存功能
- ✅ 产品统计信息
- ✅ 产品推荐
- ✅ 库存检查
- ✅ 批量获取产品
- ✅ 产品比较

#### 订单系统 (OrderControllerTest)
- ✅ 订单创建 (单个/多个商品)
- ✅ 订单列表和详情
- ✅ 订单状态更新
- ✅ 订单取消
- ✅ 物流追踪
- ✅ 订单统计
- ✅ 订单导出
- ✅ 批量操作

#### 询价系统 (InquiryControllerTest)
- ✅ 询价创建
- ✅ 询价状态管理
- ✅ 报价处理
- ✅ 询价搜索和筛选
- ✅ 询价统计
- ✅ 询价导出
- ✅ 批量更新
- ✅ 过期检查

#### 批量采购 (BulkPurchaseControllerTest)
- ✅ 批量采购报价
- ✅ 折扣计算引擎
- ✅ 批量订单创建
- ✅ 采购历史和统计
- ✅ 性能测试
- ✅ 并发处理

### 2. 服务层单元测试

#### 缓存服务 (CacheServiceTest)
- ✅ 基础缓存操作 (set, get, has, forget)
- ✅ 缓存清理和统计
- ✅ 产品/用户/搜索结果缓存
- ✅ 标签缓存功能
- ✅ 缓存预热和失效策略
- ✅ 性能测试

#### 加密服务 (EncryptionServiceTest)
- ✅ 字符串/数组/JSON 加密解密
- ✅ 密码哈希和验证
- ✅ JWT 令牌处理
- ✅ API 密钥生成
- ✅ 数据完整性验证
- ✅ 敏感数据掩码
- ✅ 性能测试

#### 验证服务 (ValidationServiceTest)
- ✅ 邮箱/手机/密码强度验证
- ✅ URL/IP/信用卡验证
- ✅ 用户注册/产品/订单数据验证
- ✅ 询价/批量采购数据验证
- ✅ 输入清理和过滤
- ✅ 自定义验证规则
- ✅ 批量验证

### 3. 数据库集成测试

#### 数据库集成 (DatabaseIntegrationTest)
- ✅ 数据库迁移验证
- ✅ 模型关系测试
- ✅ 数据完整性约束
- ✅ 外键约束测试
- ✅ 数据工厂测试
- ✅ 批量数据操作
- ✅ 查询性能测试
- ✅ 事务处理测试
- ✅ 并发操作测试

### 4. 性能和安全测试

#### 性能安全 (PerformanceSecurityTest)
- ✅ API 响应时间测试
- ✅ 缓存性能测试
- ✅ 加密性能测试
- ✅ 数据库查询性能
- ✅ API 限流测试
- ✅ 输入验证安全测试
- ✅ 密码安全性测试
- ✅ JWT 令牌安全测试
- ✅ CORS 配置测试
- ✅ 敏感信息泄露防护
- ✅ 内存使用测试
- ✅ 并发请求处理
- ✅ 缓存穿透/雪崩防护

## 测试数据工厂

### 工厂类列表

1. **UserFactory** - 用户数据工厂
   - 基础用户创建
   - 管理员/测试用户
   - 日本/中国用户
   - 企业用户
   - 活跃/非活跃用户

2. **ProductFactory** - 产品数据工厂
   - 基础产品创建
   - 分类产品 (电子/家具/服装)
   - 库存状态产品
   - 特色/高评分产品
   - 日/中市场产品

3. **OrderFactory** - 订单数据工厂
   - 基础订单创建
   - 不同状态订单
   - 高/低价值订单
   - 紧急订单
   - 日/中订单

4. **OrderItemFactory** - 订单项数据工厂
   - 基础订单项创建
   - 特定产品/订单关联
   - 定制化订单项
   - 礼品包装订单项

5. **InquiryFactory** - 询价数据工厂
   - 基础询价创建
   - 不同状态询价
   - 高/低价值询价
   - OEM/政府询价
   - 日/中询价

## 运行测试

### 基础测试命令

```bash
# 运行所有测试
./vendor/bin/phpunit

# 运行特定测试套件
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Feature
./vendor/bin/phpunit --testsuite Api
./vendor/bin/phpunit --testsuite Services

# 运行特定测试文件
./vendor/bin/phpunit tests/Unit/Services/CacheServiceTest.php
./vendor/bin/phpunit tests/Feature/Api/AuthControllerTest.php

# 运行特定测试方法
./vendor/bin/phpunit --filter test_user_login_success
./vendor/bin/phpunit --filter "test_user_login_success|test_user_registration_success"

# 生成测试覆盖率报告
./vendor/bin/phpunit --coverage-html build/coverage
./vendor/bin/phpunit --coverage-text build/coverage.txt
./vendor/bin/phpunit --coverage-clover build/logs/clover.xml
```

### Laravel 测试命令

```bash
# 使用 Laravel artisan 运行测试
php artisan test

# 运行特定测试
php artisan test --filter AuthControllerTest
php artisan test --filter "test_user_login_success"

# 生成覆盖率报告
php artisan test --coverage

# 并行运行测试 (需要安装 parallel 包)
php artisan test --parallel
```

### 测试配置

#### PHPUnit 配置 (phpunit.xml)
- 使用 SQLite 内存数据库
- 测试环境变量配置
- 覆盖率报告配置
- 测试套件分组

#### 测试环境配置
```bash
# 测试数据库
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# 缓存配置
CACHE_STORE=array

# 队列配置
QUEUE_CONNECTION=sync

# 日志配置
LOG_CHANNEL=single
```

## 测试最佳实践

### 1. 测试编写原则

- **独立性**: 每个测试应该独立运行，不依赖其他测试
- **可重复性**: 测试结果应该一致，不受环境影响
- **快速执行**: 单元测试应该在秒级完成
- **清晰命名**: 测试方法名应该清楚描述测试内容
- **单一职责**: 每个测试只验证一个功能点

### 2. 测试结构模式

```php
public function test_feature_scenario(): void
{
    // Arrange - 准备测试数据
    $testData = $this->createTestData();
    
    // Act - 执行测试操作
    $response = $this->performAction($testData);
    
    // Assert - 验证结果
    $response->assertStatus(200);
    $this->assertDatabaseHas('table', ['field' => 'value']);
}
```

### 3. 数据库测试策略

- 使用 `RefreshDatabase` trait 确保数据库干净
- 使用工厂模式创建测试数据
- 使用事务回滚避免数据污染
- 测试数据库关系和约束

### 4. API 测试策略

- 测试正常流程和异常情况
- 验证响应格式和状态码
- 测试认证和授权
- 验证输入验证和错误处理

### 5. 性能测试策略

- 设置合理的性能基准
- 测试不同数据量下的性能
- 监控内存使用情况
- 测试并发场景

## 持续集成配置

### GitHub Actions 配置示例

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: sqlite, pdo_sqlite
        
    - name: Copy Environment File
      run: cp .env.example .env
      
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
      
    - name: Generate Application Key
      run: php artisan key:generate
      
    - name: Run Tests
      run: php artisan test --coverage-clover=coverage.xml
      
    - name: Upload Coverage
      uses: codecov/codecov-action@v2
      with:
        file: ./coverage.xml
```

## 测试覆盖率目标

### 当前覆盖率指标

- **总体目标**: 80% 以上
- **服务层**: 90% 以上
- **API 控制器**: 85% 以上
- **模型层**: 80% 以上

### 覆盖率分析

```bash
# 生成详细覆盖率报告
./vendor/bin/phpunit --coverage-html build/coverage

# 查看覆盖率摘要
./vendor/bin/phpunit --coverage-text

# 生成 Clover XML 格式报告
./vendor/bin/phpunit --coverage-clover build/logs/clover.xml
```

## 故障排除

### 常见问题

1. **数据库连接错误**
   ```bash
   # 清理并重新迁移
   php artisan migrate:fresh
   php artisan db:seed
   ```

2. **缓存问题**
   ```bash
   # 清理所有缓存
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

3. **权限问题**
   ```bash
   # 设置存储目录权限
   chmod -R 755 storage
   chmod -R 755 bootstrap/cache
   ```

4. **依赖问题**
   ```bash
   # 重新安装依赖
   composer install --no-dev --optimize-autoloader
   ```

### 调试技巧

1. **使用 `--debug` 选项**
   ```bash
   php artisan test --debug
   ```

2. **运行单个测试**
   ```bash
   php artisan test --filter test_method_name
   ```

3. **查看详细输出**
   ```bash
   php artisan test --verbose
   ```

4. **停止在第一个失败**
   ```bash
   php artisan test --stop-on-failure
   ```

## 测试报告

### 报告类型

1. **HTML 覆盖率报告**: `build/coverage/index.html`
2. **文本覆盖率报告**: `build/coverage.txt`
3. **XML 覆盖率报告**: `build/logs/clover.xml`
4. **JUnit 测试报告**: `build/report.junit.xml`

### 报告分析

- 查看未覆盖的代码行
- 识别测试盲点
- 优化测试策略
- 提高代码质量

## 总结

万方商事 B2B 采购门户的自动化测试体系提供了：

1. **全面的测试覆盖**: 涵盖所有核心功能和业务逻辑
2. **高性能测试**: 确保系统在各种负载下的稳定性
3. **安全保障**: 验证系统的安全性和数据保护
4. **易于维护**: 清晰的测试结构和文档
5. **持续集成**: 支持 CI/CD 流程自动化

通过这套测试体系，我们可以确保代码质量、减少生产环境问题、提高开发效率，并为系统的持续改进提供可靠的基础。

---

**更新日期**: 2025年12月4日  
**版本**: v1.0  
**维护者**: 开发团队