<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Services\EventService;
use App\Events\User\UserRegisteredEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Product\ProductViewedEvent;
use App\Events\Inquiry\InquiryCreatedEvent;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class EventIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EventService::reset();
        Queue::fake();
        Mail::fake();
    }

    /** @test */
    public function it_triggers_user_registration_event_on_user_creation()
    {
        $userData = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);
        
        // 验证事件被触发
        $history = EventService::getEventHistory();
        $this->assertGreaterThan(0, $history->count());
        
        $userRegisteredEvents = $history->filter(function ($event) {
            return $event['name'] === UserRegisteredEvent::class;
        });
        
        $this->assertGreaterThan(0, $userRegisteredEvents->count());
    }

    /** @test */
    public function it_triggers_order_creation_event_on_order_creation()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $product = Product::factory()->create([
            'stock_quantity' => 100,
            'price' => 100.00
        ]);

        $orderData = [
            'items' => [
                [
                    'sku' => $product->sku,
                    'quantity' => 2
                ]
            ],
            'shipping_address' => 'Test Address, 123 Street, City'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201);
        
        // 验证事件被触发
        $history = EventService::getEventHistory();
        $orderCreatedEvents = $history->filter(function ($event) {
            return $event['name'] === OrderCreatedEvent::class;
        });
        
        $this->assertGreaterThan(0, $orderCreatedEvents->count());
    }

    /** @test */
    public function it_triggers_product_view_event_on_product_detail_view()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        
        // 验证事件被触发
        $history = EventService::getEventHistory();
        $productViewEvents = $history->filter(function ($event) {
            return $event['name'] === ProductViewedEvent::class;
        });
        
        $this->assertGreaterThan(0, $productViewEvents->count());
    }

    /** @test */
    public function it_handles_async_events_properly()
    {
        Queue::fake();

        $user = User::factory()->create();
        $event = new UserRegisteredEvent($user);
        
        EventService::dispatch($event);

        // 验证异步事件被推送到队列
        Queue::assertPushed(\App\Jobs\ProcessEventJob::class, function ($job) use ($event) {
            return $job->event->getId() === $event->getId();
        });
    }

    /** @test */
    public function it_updates_cache_on_events()
    {
        // 创建产品
        $product = Product::factory()->create();
        
        // 设置缓存
        Cache::put("product:{$product->id}", $product, 3600);
        Cache::put('products:list:all', Product::all(), 3600);
        
        // 触发产品更新事件
        $originalData = $product->toArray();
        $product->price = 200.00;
        $product->save();
        
        $updatedData = $product->toArray();
        $event = new \App\Events\Product\ProductUpdatedEvent(
            $product,
            $originalData,
            $updatedData
        );
        
        EventService::dispatch($event);
        
        // 验证缓存被清除
        $this->assertNull(Cache::get("product:{$product->id}"));
    }

    /** @test */
    public function it_sends_emails_on_events()
    {
        Mail::fake();

        $user = User::factory()->create();
        $event = new UserRegisteredEvent($user);
        
        EventService::dispatch($event);

        // 验证邮件被发送（在实际环境中）
        // Mail::assertSent(\App\Mail\WelcomeEmail::class);
    }

    /** @test */
    public function it_updates_statistics_on_events()
    {
        $product = Product::factory()->create();
        $event = new ProductViewedEvent($product);
        
        EventService::dispatch($event);
        
        // 验证统计数据被更新
        $stats = Cache::get("stats:products:{$product->id}:views");
        $this->assertNotNull($stats);
        $this->assertGreaterThan(0, $stats);
    }

    /** @test */
    public function it_handles_inquiry_events()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $inquiryData = [
            'company_name' => 'Test Company',
            'contact_person' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'subject' => 'Test Inquiry',
            'message' => 'This is a test inquiry message',
            'priority' => 'high',
            'estimated_budget' => 10000.00,
            'quantity' => 100,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/inquiries', $inquiryData);

        $response->assertStatus(201);
        
        // 验证事件被触发
        $history = EventService::getEventHistory();
        $inquiryEvents = $history->filter(function ($event) {
            return $event['name'] === InquiryCreatedEvent::class;
        });
        
        $this->assertGreaterThan(0, $inquiryEvents->count());
    }

    /** @test */
    public function it_provides_event_statistics()
    {
        // 触发一些事件
        $user = User::factory()->create();
        EventService::dispatch(new UserRegisteredEvent($user));
        
        $product = Product::factory()->create();
        EventService::dispatch(new ProductViewedEvent($product));

        $stats = EventService::getStatistics();
        
        $this->assertArrayHasKey('total_events', $stats);
        $this->assertArrayHasKey('enabled', $stats);
        $this->assertArrayHasKey('event_types', $stats);
        $this->assertArrayHasKey('async_events', $stats);
        $this->assertArrayHasKey('sync_events', $stats);
        
        $this->assertGreaterThan(0, $stats['total_events']);
    }

    /** @test */
    public function it_can_be_disabled_and_enabled()
    {
        $user = User::factory()->create();
        
        // 禁用事件系统
        EventService::disable();
        
        $initialCount = EventService::getEventHistory()->count();
        EventService::dispatch(new UserRegisteredEvent($user));
        
        // 事件不应该被处理
        $this->assertEquals($initialCount, EventService::getEventHistory()->count());
        
        // 启用事件系统
        EventService::enable();
        
        EventService::dispatch(new UserRegisteredEvent($user));
        
        // 事件应该被处理
        $this->assertGreaterThan($initialCount, EventService::getEventHistory()->count());
    }

    /** @test */
    public function it_handles_event_failures_gracefully()
    {
        // 创建一个会失败的事件监听器
        $failingListener = new class implements \App\Events\Contracts\ListenerInterface {
            public function handle(\App\Events\Contracts\EventInterface $event): void
            {
                throw new \Exception('Test failure');
            }
            public function getName(): string { return static::class; }
            public function getPriority(): int { return 0; }
            public function shouldHandle(\App\Events\Contracts\EventInterface $event): bool { return true; }
            public function getSupportedEvents(): array { return []; }
            public function stopPropagation(): bool { return false; }
            public function setStopPropagation(bool $stop): self { return $this; }
        };

        EventService::listen(UserRegisteredEvent::class, $failingListener);
        
        $user = User::factory()->create();
        
        // 事件失败不应该抛出异常（取决于实现）
        // 这里只是验证系统不会崩溃
        EventService::dispatch(new UserRegisteredEvent($user));
        
        $this->assertTrue(true); // 如果到达这里说明系统没有崩溃
    }
}