# 微服务数据一致性方案设计

## 概述

本文档详细描述了万方商事 B2B 采购门户微服务架构中的数据一致性方案，包括分布式事务管理、事件溯源模式、CQRS 架构和数据同步机制。

## 1. 分布式事务管理

### 1.1 Saga 模式实现

```php
<?php
// app/Services/SagaOrchestrator.php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SagaOrchestrator
{
    private array $steps = [];
    private array $compensations = [];
    private string $sagaId;
    private array $context = [];

    public function __construct()
    {
        $this->sagaId = uniqid('saga_', true);
    }

    /**
     * 添加步骤
     */
    public function addStep(string $name, callable $action, callable $compensation): self
    {
        $this->steps[] = [
            'name' => $name,
            'action' => $action,
            'compensation' => $compensation,
            'status' => 'pending'
        ];

        return $this;
    }

    /**
     * 执行 Saga
     */
    public function execute(array $initialContext = []): bool
    {
        $this->context = $initialContext;
        $executedSteps = [];

        try {
            foreach ($this->steps as $index => $step) {
                Log::info("Executing saga step: {$step['name']}", [
                    'saga_id' => $this->sagaId,
                    'step' => $index + 1,
                    'total_steps' => count($this->steps)
                ]);

                // 执行步骤
                $result = ($step['action'])($this->context);
                
                if ($result === false) {
                    throw new \Exception("Step {$step['name']} failed");
                }

                // 更新上下文
                $this->context = array_merge($this->context, $result ?? []);
                $executedSteps[] = $step;
                $step['status'] = 'completed';

                Log::info("Saga step completed: {$step['name']}", [
                    'saga_id' => $this->sagaId,
                    'context' => $this->context
                ]);
            }

            Log::info("Saga completed successfully", [
                'saga_id' => $this->sagaId,
                'final_context' => $this->context
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Saga failed, starting compensation", [
                'saga_id' => $this->sagaId,
                'error' => $e->getMessage(),
                'executed_steps' => count($executedSteps)
            ]);

            // 执行补偿操作
            $this->compensate($executedSteps);
            return false;
        }
    }

    /**
     * 执行补偿操作
     */
    private function compensate(array $executedSteps): void
    {
        $reversedSteps = array_reverse($executedSteps);

        foreach ($reversedSteps as $step) {
            try {
                Log::info("Executing compensation for: {$step['name']}", [
                    'saga_id' => $this->sagaId
                ]);

                ($step['compensation'])($this->context);

                Log::info("Compensation completed for: {$step['name']}", [
                    'saga_id' => $this->sagaId
                ]);

            } catch (\Exception $e) {
                Log::error("Compensation failed for: {$step['name']}", [
                    'saga_id' => $this->sagaId,
                    'error' => $e->getMessage()
                ]);
                // 补偿失败需要人工干预
            }
        }
    }
}
```

### 1.2 订单创建 Saga 示例

```php
<?php
// app/Services/OrderSaga.php
namespace App\Services;

use App\Services\ServiceClient;
use Illuminate\Support\Facades\Log;

class OrderSaga
{
    private SagaOrchestrator $orchestrator;
    private ServiceClient $serviceClient;

    public function __construct(ServiceClient $serviceClient)
    {
        $this->serviceClient = $serviceClient;
        $this->orchestrator = new SagaOrchestrator();
    }

    /**
     * 创建订单
     */
    public function createOrder(array $orderData): array
    {
        $this->orchestrator
            // 步骤 1: 验证用户
            ->addStep(
                'validate_user',
                function ($context) use ($orderData) {
                    $response = $this->serviceClient->get(
                        'user-service',
                        "/api/users/{$orderData['user_id']}"
                    );

                    if ($response['status'] !== 200) {
                        throw new \Exception('User validation failed');
                    }

                    return ['user' => $response['data']];
                },
                function ($context) {
                    // 用户验证无需补偿
                    Log::info('User validation compensation (no-op)');
                }
            )
            // 步骤 2: 检查库存
            ->addStep(
                'check_inventory',
                function ($context) use ($orderData) {
                    $inventoryResults = [];
                    
                    foreach ($orderData['items'] as $item) {
                        $response = $this->serviceClient->get(
                            'product-service',
                            "/api/products/{$item['product_id']}/inventory"
                        );

                        if ($response['status'] !== 200 || $response['data']['available'] < $item['quantity']) {
                            throw new \Exception("Insufficient inventory for product {$item['product_id']}");
                        }

                        $inventoryResults[$item['product_id']] = $response['data'];
                    }

                    return ['inventory' => $inventoryResults];
                },
                function ($context) {
                    // 库存检查无需补偿
                    Log::info('Inventory check compensation (no-op)');
                }
            )
            // 步骤 3: 扣减库存
            ->addStep(
                'reserve_inventory',
                function ($context) use ($orderData) {
                    $reservationResults = [];
                    
                    foreach ($orderData['items'] as $item) {
                        $response = $this->serviceClient->post(
                            'product-service',
                            "/api/products/{$item['product_id']}/reserve",
                            [
                                'quantity' => $item['quantity'],
                                'order_id' => $context['order_id'] ?? null,
                                'user_id' => $orderData['user_id']
                            ]
                        );

                        if ($response['status'] !== 200) {
                            throw new \Exception("Failed to reserve inventory for product {$item['product_id']}");
                        }

                        $reservationResults[$item['product_id']] = $response['data'];
                    }

                    return ['reservations' => $reservationResults];
                },
                function ($context) {
                    // 释放库存预留
                    if (isset($context['reservations'])) {
                        foreach ($context['reservations'] as $productId => $reservation) {
                            $this->serviceClient->post(
                                'product-service',
                                "/api/products/{$productId}/release",
                                ['reservation_id' => $reservation['id']]
                            );
                        }
                    }
                }
            )
            // 步骤 4: 创建订单记录
            ->addStep(
                'create_order',
                function ($context) use ($orderData) {
                    $response = $this->serviceClient->post(
                        'order-service',
                        '/api/orders',
                        array_merge($orderData, [
                            'status' => 'confirmed',
                            'created_at' => now()->toISOString()
                        ])
                    );

                    if ($response['status'] !== 201) {
                        throw new \Exception('Failed to create order');
                    }

                    return ['order' => $response['data']];
                },
                function ($context) {
                    // 取消订单
                    if (isset($context['order'])) {
                        $this->serviceClient->put(
                            'order-service',
                            "/api/orders/{$context['order']['id']}/status",
                            ['status' => 'cancelled']
                        );
                    }
                }
            )
            // 步骤 5: 发送通知
            ->addStep(
                'send_notification',
                function ($context) use ($orderData) {
                    $response = $this->serviceClient->post(
                        'notification-service',
                        '/api/notifications/send',
                        [
                            'type' => 'order_created',
                            'user_id' => $orderData['user_id'],
                            'data' => [
                                'order_id' => $context['order']['id'],
                                'total_amount' => $orderData['total_amount']
                            ]
                        ]
                    );

                    // 通知失败不影响订单创建
                    return ['notification_sent' => $response['status'] === 200];
                },
                function ($context) {
                    // 通知补偿：发送订单取消通知
                    if (isset($context['order'])) {
                        $this->serviceClient->post(
                            'notification-service',
                            '/api/notifications/send',
                            [
                                'type' => 'order_cancelled',
                                'user_id' => $context['order']['user_id'],
                                'data' => ['order_id' => $context['order']['id']]
                            ]
                        );
                    }
                }
            );

        $success = $this->orchestrator->execute($orderData);
        
        if ($success) {
            return $this->orchestrator->getContext()['order'];
        } else {
            throw new \Exception('Order creation failed');
        }
    }
}
```

## 2. 事件溯源模式

### 2.1 事件存储实现

```php
<?php
// app/Models/EventStore.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EventStore extends Model
{
    protected $table = 'event_store';
    protected $fillable = [
        'aggregate_id',
        'aggregate_type',
        'event_type',
        'event_data',
        'event_version',
        'occurred_at',
        'metadata'
    ];

    protected $casts = [
        'event_data' => 'json',
        'metadata' => 'json',
        'occurred_at' => 'datetime'
    ];

    /**
     * 保存事件
     */
    public static function appendEvent(
        string $aggregateId,
        string $aggregateType,
        string $eventType,
        array $eventData,
        int $version = 1,
        array $metadata = []
    ): bool {
        try {
            DB::table('event_store')->insert([
                'aggregate_id' => $aggregateId,
                'aggregate_type' => $aggregateType,
                'event_type' => $eventType,
                'event_data' => json_encode($eventData),
                'event_version' => $version,
                'occurred_at' => now(),
                'metadata' => json_encode($metadata),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to store event: " . $e->getMessage(), [
                'aggregate_id' => $aggregateId,
                'event_type' => $eventType
            ]);
            return false;
        }
    }

    /**
     * 获取聚合的所有事件
     */
    public static function getEvents(string $aggregateId, string $aggregateType): array
    {
        return DB::table('event_store')
            ->where('aggregate_id', $aggregateId)
            ->where('aggregate_type', $aggregateType)
            ->orderBy('event_version')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'aggregate_id' => $event->aggregate_id,
                    'aggregate_type' => $event->aggregate_type,
                    'event_type' => $event->event_type,
                    'event_data' => json_decode($event->event_data, true),
                    'event_version' => $event->event_version,
                    'occurred_at' => $event->occurred_at,
                    'metadata' => json_decode($event->metadata, true)
                ];
            })
            ->toArray();
    }

    /**
     * 获取最新版本号
     */
    public static function getLatestVersion(string $aggregateId, string $aggregateType): int
    {
        return DB::table('event_store')
            ->where('aggregate_id', $aggregateId)
            ->where('aggregate_type', $aggregateType)
            ->max('event_version') ?? 0;
    }
}
```

### 2.2 聚合根基类

```php
<?php
// app/Domain/AggregateRoot.php
namespace App\Domain;

use App\Models\EventStore;
use Illuminate\Support\Collection;

abstract class AggregateRoot
{
    protected string $id;
    protected int $version = 0;
    protected Collection $pendingEvents;
    protected Collection $recordedEvents;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->pendingEvents = collect();
        $this->recordedEvents = collect();
    }

    /**
     * 从事件重建聚合
     */
    public static function fromHistory(string $id, array $events): static
    {
        $aggregate = new static($id);
        
        foreach ($events as $event) {
            $aggregate->apply($event['event_type'], $event['event_data']);
            $aggregate->version = $event['event_version'];
        }

        return $aggregate;
    }

    /**
     * 应用事件
     */
    protected function apply(string $eventType, array $eventData): void
    {
        $method = 'when' . str_replace('_', '', ucwords($eventType, '_'));
        
        if (method_exists($this, $method)) {
            $this->$method($eventData);
        } else {
            $this->whenGeneric($eventType, $eventData);
        }
    }

    /**
     * 通用事件处理器
     */
    protected function whenGeneric(string $eventType, array $eventData): void
    {
        // 默认实现，子类可以重写
    }

    /**
     * 记录事件
     */
    protected function recordThat(string $eventType, array $eventData, array $metadata = []): void
    {
        $this->version++;
        
        $event = [
            'aggregate_id' => $this->id,
            'aggregate_type' => static::class,
            'event_type' => $eventType,
            'event_data' => $eventData,
            'event_version' => $this->version,
            'occurred_at' => now()->toISOString(),
            'metadata' => $metadata
        ];

        $this->pendingEvents->push($event);
        $this->apply($eventType, $eventData);
    }

    /**
     * 获取待处理事件
     */
    public function getPendingEvents(): Collection
    {
        return $this->pendingEvents;
    }

    /**
     * 标记事件为已提交
     */
    public function markEventsAsCommitted(): void
    {
        $this->recordedEvents = $this->pendingEvents;
        $this->pendingEvents = collect();
    }

    /**
     * 获取已提交事件
     */
    public function getRecordedEvents(): Collection
    {
        return $this->recordedEvents;
    }

    /**
     * 保存聚合
     */
    public function save(): bool
    {
        foreach ($this->pendingEvents as $event) {
            $success = EventStore::appendEvent(
                $event['aggregate_id'],
                $event['aggregate_type'],
                $event['event_type'],
                $event['event_data'],
                $event['event_version'],
                $event['metadata']
            );

            if (!$success) {
                return false;
            }
        }

        $this->markEventsAsCommitted();
        return true;
    }

    /**
     * 重新加载聚合
     */
    public function reload(): static
    {
        $events = EventStore::getEvents($this->id, static::class);
        return static::fromHistory($this->id, $events);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
```

### 2.3 订单聚合实现

```php
<?php
// app/Domain/Order.php
namespace App\Domain;

class Order extends AggregateRoot
{
    private string $userId;
    private array $items = [];
    private string $status = 'pending';
    private float $totalAmount = 0.0;
    private string $createdAt;
    private ?string $confirmedAt = null;
    private ?string $cancelledAt = null;

    public function __construct(string $id)
    {
        parent::__construct($id);
    }

    /**
     * 创建订单
     */
    public static function create(string $id, string $userId, array $items, float $totalAmount): self
    {
        $order = new self($id);
        
        $order->recordThat('order_created', [
            'user_id' => $userId,
            'items' => $items,
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ]);

        return $order;
    }

    /**
     * 确认订单
     */
    public function confirm(): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Only pending orders can be confirmed');
        }

        $this->recordThat('order_confirmed', [
            'previous_status' => $this->status,
            'new_status' => 'confirmed'
        ]);
    }

    /**
     * 取消订单
     */
    public function cancel(string $reason = ''): void
    {
        if ($this->status === 'cancelled') {
            throw new \Exception('Order is already cancelled');
        }

        if ($this->status === 'completed') {
            throw new \Exception('Completed orders cannot be cancelled');
        }

        $this->recordThat('order_cancelled', [
            'previous_status' => $this->status,
            'reason' => $reason
        ]);
    }

    /**
     * 更新订单项
     */
    public function updateItems(array $newItems): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Only pending orders can be updated');
        }

        $this->recordThat('order_items_updated', [
            'previous_items' => $this->items,
            'new_items' => $newItems
        ]);
    }

    // 事件处理器
    private function whenOrderCreated(array $data): void
    {
        $this->userId = $data['user_id'];
        $this->items = $data['items'];
        $this->totalAmount = $data['total_amount'];
        $this->status = $data['status'];
        $this->createdAt = now()->toISOString();
    }

    private function whenOrderConfirmed(array $data): void
    {
        $this->status = $data['new_status'];
        $this->confirmedAt = now()->toISOString();
    }

    private function whenOrderCancelled(array $data): void
    {
        $this->status = 'cancelled';
        $this->cancelledAt = now()->toISOString();
    }

    private function whenOrderItemsUpdated(array $data): void
    {
        $this->items = $data['new_items'];
        $this->totalAmount = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $this->items));
    }

    // Getters
    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getConfirmedAt(): ?string
    {
        return $this->confirmedAt;
    }

    public function getCancelledAt(): ?string
    {
        return $this->cancelledAt;
    }
}
```

## 3. CQRS 架构实现

### 3.1 命令总线

```php
<?php
// app/Services/CommandBus.php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class CommandBus
{
    private array $handlers = [];

    /**
     * 注册命令处理器
     */
    public function register(string $commandClass, callable $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    /**
     * 执行命令
     */
    public function dispatch(object $command): mixed
    {
        $commandClass = get_class($command);

        if (!isset($this->handlers[$commandClass])) {
            throw new \Exception("No handler registered for command: {$commandClass}");
        }

        try {
            Log::info("Dispatching command", [
                'command' => $commandClass,
                'data' => json_decode(json_encode($command), true)
            ]);

            $handler = $this->handlers[$commandClass];
            $result = $handler($command);

            Log::info("Command executed successfully", [
                'command' => $commandClass
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error("Command execution failed", [
                'command' => $commandClass,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
```

### 3.2 查询总线

```php
<?php
// app/Services/QueryBus.php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class QueryBus
{
    private array $handlers = [];

    /**
     * 注册查询处理器
     */
    public function register(string $queryClass, callable $handler): void
    {
        $this->handlers[$queryClass] = $handler;
    }

    /**
     * 执行查询
     */
    public function ask(object $query): mixed
    {
        $queryClass = get_class($query);

        if (!isset($this->handlers[$queryClass])) {
            throw new \Exception("No handler registered for query: {$queryClass}");
        }

        try {
            Log::info("Executing query", [
                'query' => $queryClass,
                'data' => json_decode(json_encode($query), true)
            ]);

            $handler = $this->handlers[$queryClass];
            $result = $handler($query);

            Log::info("Query executed successfully", [
                'query' => $queryClass
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error("Query execution failed", [
                'query' => $queryClass,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
```

### 3.3 命令和查询定义

```php
<?php
// app/Commands/CreateOrderCommand.php
namespace App\Commands;

class CreateOrderCommand
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $userId,
        public readonly array $items,
        public readonly float $totalAmount
    ) {}
}

<?php
// app/Commands/ConfirmOrderCommand.php
namespace App\Commands;

class ConfirmOrderCommand
{
    public function __construct(
        public readonly string $orderId
    ) {}
}

<?php
// app/Queries/GetOrderQuery.php
namespace App\Queries;

class GetOrderQuery
{
    public function __construct(
        public readonly string $orderId
    ) {}
}

<?php
// app/Queries/GetUserOrdersQuery.php
namespace App\Queries;

class GetUserOrdersQuery
{
    public function __construct(
        public readonly string $userId,
        public readonly int $page = 1,
        public readonly int $limit = 10
    ) {}
}
```

### 3.4 事件投影

```php
<?php
// app/Projections/OrderProjection.php
namespace App\Projections;

use App\Models\EventStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderProjection
{
    /**
     * 更新订单投影
     */
    public function project(array $event): void
    {
        switch ($event['event_type']) {
            case 'order_created':
                $this->handleOrderCreated($event);
                break;
            case 'order_confirmed':
                $this->handleOrderConfirmed($event);
                break;
            case 'order_cancelled':
                $this->handleOrderCancelled($event);
                break;
            case 'order_items_updated':
                $this->handleOrderItemsUpdated($event);
                break;
        }
    }

    private function handleOrderCreated(array $event): void
    {
        DB::table('order_read_model')->insert([
            'order_id' => $event['aggregate_id'],
            'user_id' => $event['event_data']['user_id'],
            'items' => json_encode($event['event_data']['items']),
            'total_amount' => $event['event_data']['total_amount'],
            'status' => $event['event_data']['status'],
            'created_at' => $event['occurred_at'],
            'updated_at' => $event['occurred_at']
        ]);
    }

    private function handleOrderConfirmed(array $event): void
    {
        DB::table('order_read_model')
            ->where('order_id', $event['aggregate_id'])
            ->update([
                'status' => $event['event_data']['new_status'],
                'confirmed_at' => $event['occurred_at'],
                'updated_at' => $event['occurred_at']
            ]);
    }

    private function handleOrderCancelled(array $event): void
    {
        DB::table('order_read_model')
            ->where('order_id', $event['aggregate_id'])
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => $event['occurred_at'],
                'cancellation_reason' => $event['event_data']['reason'] ?? '',
                'updated_at' => $event['occurred_at']
            ]);
    }

    private function handleOrderItemsUpdated(array $event): void
    {
        DB::table('order_read_model')
            ->where('order_id', $event['aggregate_id'])
            ->update([
                'items' => json_encode($event['event_data']['new_items']),
                'updated_at' => $event['occurred_at']
            ]);
    }

    /**
     * 重建投影
     */
    public function rebuild(): void
    {
        Log::info('Rebuilding order projection...');

        // 清空投影表
        DB::table('order_read_model')->truncate();

        // 重新处理所有事件
        $events = DB::table('event_store')
            ->where('aggregate_type', 'App\\Domain\\Order')
            ->orderBy('event_version')
            ->get();

        foreach ($events as $event) {
            $this->project([
                'aggregate_id' => $event->aggregate_id,
                'event_type' => $event->event_type,
                'event_data' => json_decode($event->event_data, true),
                'occurred_at' => $event->occurred_at
            ]);
        }

        Log::info('Order projection rebuilt successfully');
    }
}
```

## 4. 数据同步机制

### 4.1 事件发布器

```php
<?php
// app/Services/EventPublisher.php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EventPublisher
{
    private AMQPStreamConnection $connection;
    private string $exchangeName;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.login'),
            config('queue.connections.rabbitmq.password')
        );
        $this->exchangeName = config('services.events.exchange', 'domain_events');
    }

    /**
     * 发布事件
     */
    public function publish(array $event): void
    {
        try {
            $channel = $this->connection->channel();
            
            // 声明交换机
            $channel->exchange_declare(
                $this->exchangeName,
                'topic',
                false,
                true,
                false
            );

            $routingKey = $this->getRoutingKey($event);
            
            $message = new AMQPMessage(
                json_encode($event),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'message_id' => uniqid('msg_', true),
                    'timestamp' => time()
                ]
            );

            $channel->basic_publish(
                $message,
                $this->exchangeName,
                $routingKey
            );

            $channel->close();

            Log::info("Event published successfully", [
                'event_type' => $event['event_type'],
                'aggregate_id' => $event['aggregate_id'],
                'routing_key' => $routingKey
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to publish event", [
                'event_type' => $event['event_type'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 获取路由键
     */
    private function getRoutingKey(array $event): string
    {
        return strtolower($event['aggregate_type']) . '.' . $event['event_type'];
    }

    public function __destruct()
    {
        if ($this->connection->isConnected()) {
            $this->connection->close();
        }
    }
}
```

### 4.2 事件处理器

```php
<?php
// app/Services/EventHandler.php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class EventHandler
{
    private AMQPStreamConnection $connection;
    private array $handlers = [];

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.login'),
            config('queue.connections.rabbitmq.password')
        );
    }

    /**
     * 注册事件处理器
     */
    public function subscribe(string $eventType, callable $handler): void
    {
        $this->handlers[$eventType] = $handler;
    }

    /**
     * 开始监听事件
     */
    public function listen(): void
    {
        $channel = $this->connection->channel();
        
        $exchangeName = config('services.events.exchange', 'domain_events');
        $queueName = config('app.name') . '_events_queue';

        // 声明交换机
        $channel->exchange_declare($exchangeName, 'topic', false, true, false);
        
        // 声明队列
        $channel->queue_declare($queueName, false, true, false, false);
        
        // 绑定队列到交换机
        foreach (array_keys($this->handlers) as $eventType) {
            $routingKey = strtolower($eventType);
            $channel->queue_bind($queueName, $exchangeName, $routingKey);
        }

        Log::info("Starting to listen for events", [
            'queue' => $queueName,
            'handlers' => array_keys($this->handlers)
        ]);

        $callback = function (AMQPMessage $message) {
            try {
                $event = json_decode($message->getBody(), true);
                $eventType = $event['event_type'];

                if (isset($this->handlers[$eventType])) {
                    Log::info("Processing event", [
                        'event_type' => $eventType,
                        'aggregate_id' => $event['aggregate_id']
                    ]);

                    $handler = $this->handlers[$eventType];
                    $handler($event);

                    $message->ack();

                    Log::info("Event processed successfully", [
                        'event_type' => $eventType
                    ]);
                } else {
                    Log::warning("No handler found for event", [
                        'event_type' => $eventType
                    ]);
                    $message->ack();
                }

            } catch (\Exception $e) {
                Log::error("Failed to process event", [
                    'error' => $e->getMessage(),
                    'event' => $message->getBody()
                ]);
                
                // 重新入队，稍后重试
                $message->nack(false, true);
            }
        };

        $channel->basic_consume($queueName, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
    }
}
```

### 4.3 数据同步服务

```php
<?php
// app/Services/DataSyncService.php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DataSyncService
{
    private EventPublisher $eventPublisher;
    private array $syncStrategies = [];

    public function __construct(EventPublisher $eventPublisher)
    {
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * 注册同步策略
     */
    public function registerSyncStrategy(string $entityType, callable $strategy): void
    {
        $this->syncStrategies[$entityType] = $strategy;
    }

    /**
     * 同步数据变更
     */
    public function syncDataChange(string $entityType, string $entityId, array $changes): void
    {
        try {
            $event = [
                'event_id' => uniqid('sync_', true),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'changes' => $changes,
                'timestamp' => now()->toISOString(),
                'source_service' => config('app.name')
            ];

            // 发布同步事件
            $this->eventPublisher->publish($event);

            // 执行本地同步策略
            if (isset($this->syncStrategies[$entityType])) {
                $strategy = $this->syncStrategies[$entityType];
                $strategy($entityId, $changes);
            }

            Log::info("Data sync completed", [
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ]);

        } catch (\Exception $e) {
            Log::error("Data sync failed", [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 批量同步
     */
    public function batchSync(array $syncItems): void
    {
        DB::transaction(function () use ($syncItems) {
            foreach ($syncItems as $item) {
                $this->syncDataChange(
                    $item['entity_type'],
                    $item['entity_id'],
                    $item['changes']
                );
            }
        });
    }

    /**
     * 重新同步数据
     */
    public function resync(string $entityType, string $entityId): void
    {
        Log::info("Starting data resync", [
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);

        // 获取源数据
        $sourceData = $this->getSourceData($entityType, $entityId);
        
        if ($sourceData) {
            $this->syncDataChange($entityType, $entityId, $sourceData);
        }

        Log::info("Data resync completed", [
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);
    }

    /**
     * 获取源数据
     */
    private function getSourceData(string $entityType, string $entityId): ?array
    {
        // 根据实体类型获取源数据
        switch ($entityType) {
            case 'user':
                return DB::table('users')->where('id', $entityId)->first()?->toArray();
            case 'product':
                return DB::table('products')->where('id', $entityId)->first()?->toArray();
            case 'order':
                return DB::table('orders')->where('id', $entityId)->first()?->toArray();
            default:
                return null;
        }
    }
}
```

## 5. 数据库迁移

### 5.1 事件存储迁移

```php
<?php
// database/migrations/2025_12_04_000001_create_event_store_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_store', function (Blueprint $table) {
            $table->id();
            $table->string('aggregate_id');
            $table->string('aggregate_type');
            $table->string('event_type');
            $table->json('event_data');
            $table->integer('event_version');
            $table->timestamp('occurred_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // 索引
            $table->index(['aggregate_id', 'aggregate_type']);
            $table->index('event_type');
            $table->index('occurred_at');
            $table->unique(['aggregate_id', 'aggregate_type', 'event_version'], 'unique_aggregate_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_store');
    }
};
```

### 5.2 读取模型迁移

```php
<?php
// database/migrations/2025_12_04_000002_create_order_read_model_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_read_model', function (Blueprint $table) {
            $table->string('order_id')->primary();
            $table->string('user_id');
            $table->json('items');
            $table->decimal('total_amount', 10, 2);
            $table->string('status');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            // 索引
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_read_model');
    }
};
```

---

**文档版本**: v1.0.0  
**创建日期**: 2025年12月4日  
**最后更新**: 2025年12月4日  
**维护团队**: 万方商事技术团队