# DDD架构实施指南

## 概述

本项目已成功实施领域驱动设计（DDD）架构，为万方商事B2B采购门户提供了更清晰、更可维护的代码结构。

## 架构层次

### 1. 领域层 (Domain Layer)

位置：`app/Domain/`

**职责**：包含核心业务逻辑和规则，不依赖任何外部框架。

```
Domain/
├── Contracts/           # 核心接口定义
├── Abstractions/        # 基础抽象类
├── User/               # 用户领域
│   ├── ValueObjects/   # 值对象
│   ├── Events/         # 领域事件
│   ├── Services/       # 领域服务
│   ├── Repositories/   # 仓储接口
│   └── User.php        # 聚合根
├── Product/            # 产品领域
├── Order/              # 订单领域
├── Inquiry/            # 询价领域
└── Purchase/           # 采购领域
```

### 2. 应用层 (Application Layer)

位置：`app/Application/`

**职责**：协调领域对象执行应用程序任务，包含用例逻辑。

```
Application/
├── Services/           # 应用服务
├── DTOs/              # 数据传输对象
└── Queries/           # 查询对象
```

### 3. 基础设施层 (Infrastructure Layer)

位置：`app/Infrastructure/`

**职责**：提供技术实现，如数据库访问、外部服务集成。

```
Infrastructure/
└── Repositories/       # 仓储实现
```

### 4. 表现层 (Presentation Layer)

位置：`app/Http/Controllers/`

**职责**：处理HTTP请求，调用应用服务，返回响应。

## 核心概念

### 值对象 (Value Objects)

值对象是不可变的对象，通过属性值来标识相等性。

```php
// 示例：创建用户邮箱值对象
$email = Email::fromString('user@example.com');

// 验证和业务规则
if ($email->getDomain() === 'manpou.jp') {
    // 特殊业务逻辑
}
```

### 聚合根 (Aggregate Roots)

聚合根是数据修改的唯一入口点，保证业务规则的一致性。

```php
// 示例：创建用户聚合根
$user = User::register(
    UserId::fromInt(1),
    Username::fromString('testuser'),
    Email::fromString('test@example.com'),
    $passwordHash,
    UserRole::customer()
);

// 业务操作
$user->changeStatus(UserStatus::active(), 'Account verified');
```

### 领域事件 (Domain Events)

领域事件记录聚合根中发生的重要业务事件。

```php
// 示例：用户注册事件
$user->recordDomainEvent(
    UserRegistered::create(
        $userId,
        $username->toString(),
        $email->toString(),
        $role->getValue()
    )
);
```

### 仓储模式 (Repository Pattern)

仓储模式封装了对象存储和检索逻辑。

```php
// 示例：使用仓储
$user = $userRepository->findById(UserId::fromInt(1));
$userRepository->save($user);
```

## 使用指南

### 创建新用户

```php
use App\Application\Services\UserApplicationService;
use App\Application\DTOs\CreateUserDTO;

// 创建DTO
$createUserDTO = CreateUserDTO::fromArray([
    'username' => 'newuser',
    'email' => 'newuser@example.com',
    'password_hash' => bcrypt('password'),
    'role' => 'customer'
]);

// 使用应用服务
$userService = app(UserApplicationService::class);
$user = $userService->createUser($createUserDTO);
```

### 创建新订单

```php
use App\Application\Services\OrderApplicationService;
use App\Application\DTOs\CreateOrderDTO;

// 创建DTO
$createOrderDTO = CreateOrderDTO::fromArray([
    'customer_id' => '123',
    'customer_email' => 'customer@example.com',
    'items' => [
        [
            'product_id' => 'ALIBABA_SKU_A123',
            'product_name' => '办公椅',
            'quantity' => 10,
            'unit_price' => 1250.50,
            'currency' => 'CNY'
        ]
    ],
    'currency' => 'CNY'
]);

// 使用应用服务
$orderService = app(OrderApplicationService::class);
$order = $orderService->createOrder($createOrderDTO);
```

### 处理订单状态变更

```php
// 确认订单
$order = $orderService->confirmOrder($orderId, 'admin');

// 发货
$order = $orderService->shipOrder($orderId, 'TRACK123456', 'shipping_dept');

// 送达
$order = $orderService->deliverOrder($orderId, 'delivery_system');
```

## 业务规则

### 用户业务规则

1. **用户名唯一性**：用户名必须在系统中唯一
2. **邮箱唯一性**：邮箱必须在系统中唯一
3. **角色权限**：不同角色有不同的操作权限
4. **状态转换**：用户状态只能按照特定流程转换

### 产品业务规则

1. **库存管理**：产品库存不能为负数
2. **价格验证**：产品价格不能为负数
3. **状态转换**：库存状态有特定的转换规则

### 订单业务规则

1. **订单状态流程**：待处理 → 已确认 → 处理中 → 已发货 → 已送达
2. **库存扣减**：订单确认时自动扣减库存
3. **批量折扣**：大批量订单享受折扣优惠
4. **取消规则**：特定状态下可以取消订单

## 事件处理

系统自动处理以下领域事件：

### 用户事件
- `user_registered`：用户注册时触发
- `user_status_changed`：用户状态变更时触发
- `user_role_changed`：用户角色变更时触发

### 订单事件
- `order_created`：订单创建时触发
- `order_status_changed`：订单状态变更时触发

### 产品事件
- `product_created`：产品创建时触发
- `inventory_changed`：库存变更时触发

## 依赖注入配置

在 `app/Providers/DomainServiceProvider.php` 中配置了所有DDD相关的服务绑定：

```php
// 仓储绑定
$this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

// 领域服务绑定
$this->app->singleton(UserDomainService::class, function ($app) {
    return new UserDomainService($app->make(UserRepositoryInterface::class));
});

// 应用服务绑定
$this->app->singleton(UserApplicationService::class, function ($app) {
    return new UserApplicationService(
        $app->make(UserRepositoryInterface::class),
        $app->make(UserDomainService::class),
        $app->make(EventDispatcherService::class)
    );
});
```

## 测试策略

### 单元测试

测试值对象和领域实体的业务逻辑：

```php
public function testEmailValidation()
{
    $this->expectException(InvalidArgumentException::class);
    Email::fromString('invalid-email');
}
```

### 集成测试

测试应用服务和仓储的集成：

```php
public function testCreateUser()
{
    $userService = app(UserApplicationService::class);
    $user = $userService->createUser($createUserDTO);
    
    $this->assertEquals('newuser', $user->username);
}
```

## 性能优化

### 仓储缓存

在仓储实现中添加适当的缓存策略：

```php
public function findById(UserId $id): ?User
{
    return Cache::remember("user_{$id->toInt()}", 3600, function () use ($id) {
        // 数据库查询逻辑
    });
}
```

### 事件异步处理

对于非关键事件，使用队列异步处理：

```php
$eventDispatcher->register('user_registered', function ($event) {
    dispatch(new SendWelcomeEmailJob($event));
});
```

## 扩展指南

### 添加新领域

1. 在 `app/Domain/` 下创建新领域目录
2. 定义值对象、聚合根、领域事件
3. 创建领域服务和仓储接口
4. 在基础设施层实现仓储
5. 创建应用服务和DTO
6. 在服务提供者中注册依赖

### 添加新业务规则

1. 在相应的值对象中添加验证逻辑
2. 在聚合根中添加业务方法
3. 在领域服务中添加复杂业务逻辑
4. 更新应用服务以支持新规则

## 最佳实践

1. **保持领域层纯净**：领域层不应依赖任何框架
2. **使用值对象**：用值对象替代基础类型，提高代码表达力
3. **小聚合**：保持聚合根的规模适中
4. **最终一致性**：通过领域事件实现最终一致性
5. **依赖倒置**：高层模块不依赖低层模块，都依赖抽象

## 迁移路径

现有代码可以逐步迁移到DDD架构：

1. **第一步**：引入值对象替换基础类型
2. **第二步**：创建领域服务和应用服务
3. **第三步**：重构控制器使用应用服务
4. **第四步**：引入领域事件和事件处理
5. **第五步**：优化性能和添加缓存

## 总结

DDD架构的实施为项目带来了以下好处：

1. **更好的代码组织**：清晰的分层和职责划分
2. **更高的可维护性**：业务逻辑集中在领域层
3. **更强的表达力**：通过值对象和领域模型表达业务概念
4. **更好的测试性**：各层可以独立测试
5. **更强的扩展性**：新功能可以轻松添加到相应领域

这个架构为万方商事B2B采购门户的长期发展奠定了坚实的基础。