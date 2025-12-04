<?php

namespace App\Events;

use App\Events\Contracts\EventInterface;
use App\Events\Contracts\ListenerInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class EventDispatcher
{
    protected Collection $listeners;
    protected Collection $eventHistory;
    protected bool $enableHistory = true;
    protected int $maxHistorySize = 1000;

    public function __construct()
    {
        $this->listeners = new Collection();
        $this->eventHistory = new Collection();
    }

    /**
     * 注册事件监听器
     */
    public function listen(string $eventName, ListenerInterface $listener): self
    {
        if (!$this->listeners->has($eventName)) {
            $this->listeners->put($eventName, new Collection());
        }

        $this->listeners->get($eventName)->push($listener);
        $this->listeners->get($eventName)->sortByDesc(fn($l) => $l->getPriority());

        Log::info('Event listener registered', [
            'event' => $eventName,
            'listener' => $listener->getName(),
            'priority' => $listener->getPriority()
        ]);

        return $this;
    }

    /**
     * 注册监听器到多个事件
     */
    public function listenTo(array $eventNames, ListenerInterface $listener): self
    {
        foreach ($eventNames as $eventName) {
            $this->listen($eventName, $listener);
        }

        return $this;
    }

    /**
     * 分发事件
     */
    public function dispatch(EventInterface $event): self
    {
        $this->recordEvent($event);

        Log::info('Event dispatched', [
            'event_id' => $event->getId(),
            'event_name' => $event->getName(),
            'async' => $event->shouldProcessAsync(),
            'priority' => $event->getPriority()
        ]);

        if ($event->shouldProcessAsync()) {
            $this->dispatchAsync($event);
        } else {
            $this->dispatchSync($event);
        }

        return $this;
    }

    /**
     * 同步分发事件
     */
    protected function dispatchSync(EventInterface $event): void
    {
        $listeners = $this->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            if ($listener->shouldHandle($event)) {
                try {
                    $listener->handle($event);

                    if ($listener->stopPropagation()) {
                        Log::info('Event propagation stopped', [
                            'event_id' => $event->getId(),
                            'listener' => $listener->getName()
                        ]);
                        break;
                    }
                } catch (\Throwable $exception) {
                    Log::error('Event listener failed', [
                        'event_id' => $event->getId(),
                        'listener' => $listener->getName(),
                        'error' => $exception->getMessage()
                    ]);
                    throw $exception;
                }
            }
        }
    }

    /**
     * 异步分发事件
     */
    protected function dispatchAsync(EventInterface $event): void
    {
        $job = new \App\Jobs\ProcessEventJob($event);
        Queue::push($job);
    }

    /**
     * 获取事件的监听器
     */
    protected function getListenersForEvent(EventInterface $event): Collection
    {
        $eventName = $event->getName();
        $listeners = new Collection();

        if ($this->listeners->has($eventName)) {
            $listeners = $listeners->merge($this->listeners->get($eventName));
        }

        // 添加全局监听器（支持所有事件的监听器）
        $this->listeners->each(function ($eventListeners, $key) use ($event, $listeners) {
            $eventListeners->each(function ($listener) use ($event, $listeners) {
                if (empty($listener->getSupportedEvents()) && $listener->shouldHandle($event)) {
                    $listeners->push($listener);
                }
            });
        });

        return $listeners->sortByDesc(fn($l) => $l->getPriority())->values();
    }

    /**
     * 记录事件历史
     */
    protected function recordEvent(EventInterface $event): void
    {
        if (!$this->enableHistory) {
            return;
        }

        $this->eventHistory->push([
            'id' => $event->getId(),
            'name' => $event->getName(),
            'timestamp' => $event->getTimestamp(),
            'data' => $event->getData(),
            'metadata' => $event->getMetadata(),
            'async' => $event->shouldProcessAsync(),
            'priority' => $event->getPriority()
        ]);

        // 限制历史记录大小
        if ($this->eventHistory->count() > $this->maxHistorySize) {
            $this->eventHistory = $this->eventHistory->slice(-$this->maxHistorySize);
        }
    }

    /**
     * 获取事件历史
     */
    public function getEventHistory(): Collection
    {
        return $this->eventHistory;
    }

    /**
     * 清除事件历史
     */
    public function clearEventHistory(): self
    {
        $this->eventHistory = new Collection();
        return $this;
    }

    /**
     * 启用/禁用事件历史记录
     */
    public function setEnableHistory(bool $enable): self
    {
        $this->enableHistory = $enable;
        return $this;
    }

    /**
     * 设置最大历史记录大小
     */
    public function setMaxHistorySize(int $size): self
    {
        $this->maxHistorySize = $size;
        return $this;
    }

    /**
     * 获取所有注册的监听器
     */
    public function getListeners(): Collection
    {
        return $this->listeners;
    }

    /**
     * 检查是否有监听器监听特定事件
     */
    public function hasListeners(string $eventName): bool
    {
        return $this->listeners->has($eventName) && $this->listeners->get($eventName)->isNotEmpty();
    }

    /**
     * 移除监听器
     */
    public function removeListener(string $eventName, string $listenerClass): self
    {
        if ($this->listeners->has($eventName)) {
            $this->listeners->put($eventName, 
                $this->listeners->get($eventName)->filter(
                    fn($listener) => !$listener instanceof $listenerClass
                )
            );
        }

        return $this;
    }

    /**
     * 清除所有监听器
     */
    public function clearListeners(): self
    {
        $this->listeners = new Collection();
        return $this;
    }
}