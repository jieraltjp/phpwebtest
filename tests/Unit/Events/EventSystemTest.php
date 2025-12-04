<?php

namespace Tests\Unit\Events;

use Tests\TestCase;
use App\Services\EventService;
use App\Events\EventDispatcher;
use App\Events\Contracts\EventInterface;
use App\Events\Contracts\ListenerInterface;
use App\Events\User\UserRegisteredEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Listeners\LoggingListener;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EventSystemTest extends TestCase
{
    protected EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = new EventDispatcher();
        EventService::reset();
    }

    /** @test */
    public function it_can_register_and_dispatch_events()
    {
        // 创建测试监听器
        $listener = new TestEventListener();
        $this->dispatcher->listen(TestEvent::class, $listener);

        // 创建并分发事件
        $event = new TestEvent(['test' => 'data']);
        $this->dispatcher->dispatch($event);

        // 验证监听器被调用
        $this->assertTrue($listener->wasCalled);
        $this->assertEquals($event, $listener->receivedEvent);
    }

    /** @test */
    public function it_can_handle_multiple_listeners()
    {
        $listener1 = new TestEventListener();
        $listener2 = new TestEventListener();

        $this->dispatcher->listen(TestEvent::class, $listener1);
        $this->dispatcher->listen(TestEvent::class, $listener2);

        $event = new TestEvent(['test' => 'data']);
        $this->dispatcher->dispatch($event);

        $this->assertTrue($listener1->wasCalled);
        $this->assertTrue($listener2->wasCalled);
    }

    /** @test */
    public function it_respects_listener_priority()
    {
        $lowPriorityListener = new TestEventListener(1);
        $highPriorityListener = new TestEventListener(10);

        $this->dispatcher->listen(TestEvent::class, $lowPriorityListener);
        $this->dispatcher->listen(TestEvent::class, $highPriorityListener);

        $event = new TestEvent(['test' => 'data']);
        $this->dispatcher->dispatch($event);

        // 高优先级监听器应该先被调用
        $this->assertLessThan($lowPriorityListener->callOrder, $highPriorityListener->callOrder);
    }

    /** @test */
    public function it_can_stop_propagation()
    {
        $listener1 = new TestEventListener(10, true); // 停止传播
        $listener2 = new TestEventListener(1);

        $this->dispatcher->listen(TestEvent::class, $listener1);
        $this->dispatcher->listen(TestEvent::class, $listener2);

        $event = new TestEvent(['test' => 'data']);
        $this->dispatcher->dispatch($event);

        $this->assertTrue($listener1->wasCalled);
        $this->assertFalse($listener2->wasCalled); // 不应该被调用
    }

    /** @test */
    public function it_records_event_history()
    {
        $this->dispatcher->setEnableHistory(true);

        $event = new TestEvent(['test' => 'data']);
        $this->dispatcher->dispatch($event);

        $history = $this->dispatcher->getEventHistory();
        $this->assertCount(1, $history);
        $this->assertEquals($event->getId(), $history->first()['id']);
    }

    /** @test */
    public function it_can_dispatch_async_events()
    {
        $this->expectsJobs(\App\Jobs\ProcessEventJob::class);

        $event = new TestEvent(['test' => 'data'], [], true); // 异步事件
        $this->dispatcher->dispatch($event);
    }

    /** @test */
    public function it_handles_user_registered_event()
    {
        $user = User::factory()->create();
        $listener = new TestEventListener();

        $this->dispatcher->listen(UserRegisteredEvent::class, $listener);

        $event = new UserRegisteredEvent($user);
        $this->dispatcher->dispatch($event);

        $this->assertTrue($listener->wasCalled);
        $this->assertEquals($user->id, $event->getUserId());
        $this->assertEquals($user->username, $event->getUsername());
    }

    /** @test */
    public function it_can_enable_and_disable_event_system()
    {
        $this->assertTrue(EventService::isEnabled());

        EventService::disable();
        $this->assertFalse(EventService::isEnabled());

        EventService::enable();
        $this->assertTrue(EventService::isEnabled());
    }

    /** @test */
    public function it_provides_debug_information()
    {
        $debug = EventService::debug();

        $this->assertIsArray($debug);
        $this->assertArrayHasKey('enabled', $debug);
        $this->assertArrayHasKey('dispatcher', $debug);
        $this->assertArrayHasKey('statistics', $debug);
    }

    /** @test */
    public function it_handles_listener_exceptions_gracefully()
    {
        Log::shouldReceive('error')->once();

        $failingListener = new FailingTestEventListener();
        $this->dispatcher->listen(TestEvent::class, $failingListener);

        $event = new TestEvent(['test' => 'data']);

        $this->expectException(\Exception::class);
        $this->dispatcher->dispatch($event);
    }
}

// 测试用的事件类
class TestEvent extends \App\Events\AbstractEvent
{
    public function __construct(array $data = [], array $metadata = [], bool $async = false, int $priority = 0)
    {
        parent::__construct($data, $metadata, $async, $priority);
    }
}

// 测试用的监听器
class TestEventListener implements ListenerInterface
{
    public bool $wasCalled = false;
    public EventInterface $receivedEvent;
    public int $callOrder = 0;
    public static int $globalCallOrder = 0;
    private bool $stopPropagation;
    private int $priority;

    public function __construct(int $priority = 0, bool $stopPropagation = false)
    {
        $this->priority = $priority;
        $this->stopPropagation = $stopPropagation;
    }

    public function handle(EventInterface $event): void
    {
        $this->wasCalled = true;
        $this->receivedEvent = $event;
        $this->callOrder = ++self::$globalCallOrder;
    }

    public function getName(): string
    {
        return self::class;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function shouldHandle(EventInterface $event): bool
    {
        return $event instanceof TestEvent;
    }

    public function getSupportedEvents(): array
    {
        return [TestEvent::class];
    }

    public function stopPropagation(): bool
    {
        return $this->stopPropagation;
    }

    public function setStopPropagation(bool $stop): self
    {
        $this->stopPropagation = $stop;
        return $this;
    }
}

// 测试用的失败监听器
class FailingTestEventListener implements ListenerInterface
{
    public function handle(EventInterface $event): void
    {
        throw new \Exception('Test listener failure');
    }

    public function getName(): string
    {
        return self::class;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function shouldHandle(EventInterface $event): bool
    {
        return true;
    }

    public function getSupportedEvents(): array
    {
        return [];
    }

    public function stopPropagation(): bool
    {
        return false;
    }

    public function setStopPropagation(bool $stop): self
    {
        return $this;
    }
}